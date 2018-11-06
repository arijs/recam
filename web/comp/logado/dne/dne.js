(function() {

var Utils = RECAM.Utils;
var reTrim = /^\s*|\s*$/g;
var reSymbols = [
	{re:/\xa0/g,rp:' '},
	{re:/\s+[¿]\s+/g,rp:' '},
	{re:/\S[¿]\S/g,rp:'\''},
	{re:/[`´]/g,rp:'\''},
	{re:/[°]/g,rp:'º'},
	{re:/^([^()]*)\(([^()]*)(\)\s*\))([^()]*)$/,rp:'$1($2)$4'}
];
var colLoc = [
	{re:/^\d{1,8}$/,key:'id',desc:'chave da localidade'},
	{re:/^[A-Z]{2}$/i,key:'uf',desc:'sigla da uf'},
	{re:/^[A-ZÁÂÃÇÉÊÍÓÔÕÚÜáâãçéêíóôõúü' 0-9-]{2,72}$/i,key:'nm',desc:'nome da localidade'},
	{re:/^\d{8}$|^$/,key:'ce',desc:'CEP da localidade (não codificada)'},
	{re:/^[012]$/,key:'st',desc:'situação da localidade (codificada ou não)'},
	{re:/^[DMP]$/i,key:'tp',desc:'tipo de localidade (d/m/p)'},
	{re:/^\d{0,8}$/,key:'su',desc:'chave da localidade de subordinação'},
	{re:/^[A-ZÁÂÃÇÉÊÍÓÔÕÚÜáâãçéêíóôõúü' 0-9ªº-]{2,36}$/i,key:'ab',desc:'abreviatura da localidade'},
	{re:/^\d{7}$|^$/,key:'ib',desc:'código do município IBGE'}
];
var colNei = [
	{re:/^\d{1,8}$/,key:'id',desc:'chave do bairro'},
	{re:/^[A-Z]{2}$/i,key:'uf',desc:'sigla da uf'},
	{re:/^\d{1,8}$/,key:'lc',desc:'chave da localidade'},
	{re:/^[A-ZÁÀÂÃÇÉÈÊÍÓÔÕÚÜáàâãçéèêíóôõúü'" 0-9ªº().,/+-]{1,72}$/i,key:'nm',desc:'nome do bairro',rp:reSymbols},
	{re:/^[A-ZÁÀÂÃÇÉÈÊÍÓÔÕÚÜáàâãçéèêíóôõúü'" 0-9ªº().,/+-]{1,36}$/i,key:'ab',desc:'abreviatura do bairro',rp:reSymbols},
];
var colStr = [
	{re:/^\d{1,8}$/,key:'id',desc:'chave do logradouro'},
	{re:/^[A-Z]{2}$/i,key:'uf',desc:'sigla da uf'},
	{re:/^\d{1,8}$/,key:'lc',desc:'chave da localidade'},
	{re:/^\d{1,8}$/,key:'ba',desc:'chave do bairro'},
	{re:/^\d{0,8}$/,key:'bf',desc:'chave do bairro final (deprecado)',opt:true},
	{re:/^[A-ZÁÀÂÃÄÇÉÈÊÍÌÑÓÒÔÕÚÜáàâãäçéèêíìñóòôõúü'" 0-9ªº().,/+-]{1,96}$/i,key:'nm',desc:'nome do logradouro',rp:reSymbols},
	{re:/^[A-ZÁÀÂÃÇÉÈÊÍÑÓÔÕÚÜáàâãçéèêíñóôõúü'" 0-9ªº().,/+-]{0,96}$/i,key:'cp',desc:'complemento do logradouro',rp:reSymbols},
	{re:/^\d{8}$|^$/,key:'ce',desc:'CEP do logradouro'},
	{re:/^[A-ZÁÂÃÇÉÊÍÓÔÕÚÜáâãçéêíóôõúü 0-9ªº-]{2,36}$/i,key:'tp',desc:'tipo de logradouro'},
	{re:/^[SN]$/i,key:'ut',desc:'utilização do tipo de logradouro'},
	{re:/^[A-ZÁÀÂÃÄÇÉÈÊÍÌÑÓÒÔÕÚÜáàâãäçéèêíìñóòôõúü'" 0-9ªº().,/+-]{1,36}$/i,key:'ab',desc:'abreviatura do logradouro',rp:reSymbols},
];

RECAM.comp['logado/dne'] = {
	data: function() {
		return {
			runLocations: false,
			queueLocations: null,
			runNeighborhoods: false,
			queueNeighborhoods: null,
			runStreets: false,
			queueStreets: null,
			checkStreets: false,
			queueCheckStreets: null
		}
	},
	methods: {
		normalizeString: function(s) {
			return Utils.deaccentize(String(s || ''))
				.replace(/\W/g,' ')
				.replace(/\s{2,}/g,' ')
				.replace(reTrim,'')
				.toLowerCase();
		},
		changeFileLocation: function() {
			var vm = this;
			var file = this.$refs.fileLocation.files[0];
			// console.log(file);
			var reader = new FileReader();
			reader.onload = function(e) {
				var data = e.target.result;
				// console.log(data.length);
				var rows = [];
				var lfpos;
				do {
					lfpos = data.indexOf('\n');
					lfpos = (lfpos == -1) ? data.length : lfpos;
					rows.push(data.substr(0,lfpos).replace(reTrim,''));
					data = data.substr(lfpos+1);
				} while (data.length);
				// console.log(rows);
				// vm.saveLocations(rows);
				vm.queueLocations = {
					rows: rows,
					success: 0,
					invalid: 0,
					errors: 0,
					total: rows.length
				};
			};
			reader.readAsText(file, 'iso-8859-1');
		},
		parseRowLocation: function(row) {
			row = row.split('@');
			var nc = colLoc.length;
			var erros = [];
			var parsed;
			if (row.length !== nc) {
				parsed = row;
				erros.push('número de colunas diferente (tem '+row.length+', esperado '+nc+')');
			} else {
				parsed = {};
				for (var i = 0; i < nc; i++) {
					var value = row[i];
					var colLocItem = colLoc[i];
					var rp = colLocItem.rp;
					var rpc = rp && rp.length;
					if (rpc > 0) {
						for (var j = 0; j < rpc; j++) {
							value = value.replace(rp[j].re, rp[j].rp);
						}
					}
					value = value.replace(reTrim,'');
					parsed[colLocItem.key] = value;
					if (!colLocItem.re.test(value)) {
						erros.push('erro: '+colLocItem.desc);
					}
				}
				parsed.nn = this.normalizeString(parsed.nm);
			}
			return {
				erros: erros.length ? erros : null,
				parsed: parsed
			};
		},
		clickRunLocations: function() {
			this.$nextTick(this.saveLocations);
		},
		saveLocations: function() {
			if (!this.runLocations) return;
			var ql = this.queueLocations;
			var rows = ql.rows;
			if (!rows.length) return;
			var vm = this;
			var batch = 10;
			var parsed = [];
			var invalid = 0;
			var pc = 0;
			while (pc < batch && rows.length) {
				var row = this.parseRowLocation(rows.shift());
				if (row.erros) {
					console.log('invalid', row);
					invalid += 1;
				} else {
					parsed.push(row.parsed);
					pc += 1;
				}
			}
			ql.invalid += invalid;
			Utils.loadAjax({
				method: 'POST',
				url: '/api/dne/locations',
				headers: [
					{name:'Content-Type',value:'application/json'}
				],
				body:JSON.stringify(parsed),
				cb: function(err,data) {
					var rows = data && data.rows;
					if (err || !rows) {
						console.log('error', err, parsed);
						ql.errors += pc;
					} else {
						for (var i = 0; i < pc; i++) {
							if (rows[i] && parsed[i].id === rows[i].id) {
								ql.success += 1;
							} else {
								ql.errors += 1;
							}
						}
					}
					vm.saveLocations();
				}
			});
		},
		changeFileNeighborhood: function() {
			var vm = this;
			var file = this.$refs.fileNeighborhood.files[0];
			// console.log(file);
			var reader = new FileReader();
			reader.onload = function(e) {
				var data = e.target.result;
				// console.log(data.length);
				var rows = [];
				var lfpos;
				do {
					lfpos = data.indexOf('\n');
					lfpos = (lfpos == -1) ? data.length : lfpos;
					rows.push(data.substr(0,lfpos).replace(reTrim,''));
					data = data.substr(lfpos+1);
				} while (data.length);
				// console.log(rows);
				// vm.saveLocations(rows);
				vm.queueNeighborhoods = {
					rows: rows,
					success: 0,
					invalid: 0,
					errors: 0,
					total: rows.length
				};
			};
			reader.readAsText(file, 'iso-8859-1');
		},
		parseRowNeighborhood: function(row) {
			row = row.split('@');
			var nc = colNei.length;
			var erros = [];
			var parsed;
			if (row.length !== nc) {
				parsed = row;
				erros.push('número de colunas diferente (tem '+row.length+', esperado '+nc+')');
			} else {
				parsed = {};
				for (var i = 0; i < nc; i++) {
					var value = row[i];
					var colNeiItem = colNei[i];
					var rp = colNeiItem.rp;
					var rpc = rp && rp.length;
					if (rpc > 0) {
						for (var j = 0; j < rpc; j++) {
							value = value.replace(rp[j].re, rp[j].rp);
						}
					}
					value = value.replace(reTrim,'');
					parsed[colNeiItem.key] = value;
					if (!colNeiItem.re.test(value)) {
						erros.push('erro: '+colNeiItem.desc);
					}
				}
				parsed.nn = this.normalizeString(parsed.nm);
			}
			return {
				erros: erros.length ? erros : null,
				parsed: parsed
			};
		},
		clickRunNeighborhoods: function() {
			this.$nextTick(this.saveNeighborhoods);
		},
		saveNeighborhoods: function() {
			if (!this.runNeighborhoods) return;
			var qn = this.queueNeighborhoods;
			var rows = qn.rows;
			if (!rows.length) return;
			var vm = this;
			var batch = 50;
			var parsed = [];
			var invalid = 0;
			var pc = 0;
			while (pc < batch && rows.length) {
				var row = this.parseRowNeighborhood(rows.shift());
				if (row.erros) {
					console.log('invalid', row);
					invalid += 1;
				} else {
					parsed.push(row.parsed);
					pc += 1;
				}
			}
			qn.invalid += invalid;
			Utils.loadAjax({
				method: 'POST',
				url: '/api/dne/neighborhoods',
				headers: [
					{name:'Content-Type',value:'application/json'}
				],
				body:JSON.stringify(parsed),
				cb: function(err,data) {
					var rows = data && data.rows;
					if (err || !rows) {
						console.log('error', err, parsed);
						qn.errors += pc;
					} else {
						for (var i = 0; i < pc; i++) {
							if (rows[i] && parsed[i].id === rows[i].id) {
								qn.success += 1;
							} else {
								qn.errors += 1;
							}
						}
					}
					vm.saveNeighborhoods();
				}
			});
		},
		changeFileStreet: function() {
			var vm = this;
			var file = this.$refs.fileStreet.files[0];
			// console.log(file);
			var reader = new FileReader();
			reader.onload = function(e) {
				var data = e.target.result;
				// console.log(data.length);
				var rows = [];
				var lfpos;
				do {
					lfpos = data.indexOf('\n');
					lfpos = (lfpos == -1) ? data.length : lfpos;
					rows.push(data.substr(0,lfpos).replace(reTrim,''));
					data = data.substr(lfpos+1);
				} while (data.length);
				vm.queueStreets = {
					rows: rows,
					total: rows.length,
					parsed: [],
					invalid: [],
					parsing: false,
					check: {
						page: 0,
						pageLoading: 0,
						success: 0,
						wrong: [],
						retry: [],
						notfound: 0,
						skipSource: 0,
						skipTarget: 0,
						errors: 0
					},
					save: {
						success: 0,
						errors: []
					}
				};
				vm.triggerParseStreet();
			};
			reader.readAsText(file, 'iso-8859-1');
		},
		triggerParseStreet: function() {
			var vm = this;
			var queue = this.queueStreets;
			var count = 100;
			if (queue.rows.length) {
				queue.parsing = true;
				setTimeout(function() {
					var batch = queue.rows.slice(0, count);
					queue.rows = queue.rows.slice(count);
					batch = vm.parseBatchStreet(batch);
					queue.parsed = queue.parsed.concat(batch.parsed);
					queue.invalid = queue.invalid.concat(batch.invalid);
					vm.triggerParseStreet();
				}, 0);
			} else {
				queue.parsing = false;
			}
		},
		parseBatchStreet: function(rows) {
			var parsed = [];
			var invalid = [];
			var r;
			while (r = rows.shift()) {
				r = this.parseRowStreet(r);
				if (r.erros) {
					invalid.push(r);
				} else {
					parsed.push(r.parsed);
				}
			}
			return { parsed: parsed, invalid: invalid };
		},
		parseRowStreet: function(row) {
			row = row.split('@');
			var nc = colStr.length;
			var erros = [];
			var parsed;
			if (row.length !== nc) {
				parsed = row;
				erros.push('número de colunas diferente (tem '+row.length+', esperado '+nc+')');
			} else {
				parsed = {};
				for (var i = 0; i < nc; i++) {
					var value = row[i];
					var colStrItem = colStr[i];
					var rp = colStrItem.rp;
					var rpc = rp && rp.length;
					if (rpc > 0) {
						for (var j = 0; j < rpc; j++) {
							value = value.replace(rp[j].re, rp[j].rp);
						}
					}
					value = value.replace(reTrim,'');
					parsed[colStrItem.key] = value;
					if (!colStrItem.re.test(value)) {
						erros.push('erro: '+colStrItem.desc);
					}
				}
				parsed.nn = this.normalizeString(parsed.nm);
			}
			parsed = {
				erros: erros.length ? erros : null,
				parsed: parsed
			};
			if (erros.length) {
				console.log('invalid', parsed);
			}
			return parsed;
		},
		clickRunStreets: function() {
			this.$nextTick(this.saveStreets);
		},
		clickCheckStreets: function() {
			this.$nextTick(this.loadStreets);
		},
		loadStreets: function() {
			if (!this.checkStreets) return;
			var qs = this.queueStreets;
			var parsed = qs.parsed;
			var qsc = qs.check;
			if (!parsed.length) {
				var retry = qsc.retry;
				if (!retry.length) {
					return;
				} else {
					qs.parsed = parsed = retry;
					qsc.retry = retry = [];
				}
			}
			var page = qsc.page + 1;
			if (qsc.pageLoading) {
				console.log('Page already loading: '+qsc.pageLoading+', would try '+page);
				return;
			}
			qsc.pageLoading = page;
			var vm = this;
			var batch = 50;
			Utils.loadAjax({
				url: '/api/dne/streets?uf=SP&page='+page+'&rows='+batch,
				timeout: 120000,
				cb: function(err, data) {
					qsc.pageLoading = 0;
					var drows = data && data.rows;
					if (err || !drows) {
						console.log('error', err, batch);
						qsc.errors += 1;
						return void vm.loadStreets();
					} else {
						var count = drows.length;
						var rowQ = null;
						var rowDb;
						while (rowDb = drows.shift()) {
							var first = true;
							var idMatch = null;
							var pc = parsed.length;
							for (var j = 0; j < pc; j++) {
								rowQ = parsed[j];
								if (rowQ.id === String(rowDb.id)) {
									idMatch = rowQ;
									parsed.splice(j, 1);
									break;
								}
							}
							rowQ = null;
							if (idMatch) {
								var hop = Object.prototype.hasOwnProperty;
								var propErrors = [];
								var colStrCount = colStr.length;
								for (var csi = 0; csi < colStrCount; csi++) {
									var cs = colStr[csi];
									var k = cs.key;
									if (!cs.opt && !hop.call(rowDb, k)) {
										propErrors.push('falta: '+cs.desc);
									} else if (rowDb[k] ? rowDb[k] != idMatch[k] : idMatch[k] && !cs.opt) {
										propErrors.push(
											'erro: '+cs.desc+' (esperado '+
											JSON.stringify(idMatch[k])+', obteve '+
											JSON.stringify(rowDb[k])+')'
										);
									}
								}
								if (propErrors.length) {
									console.log('prop error', propErrors, idMatch, rowDb);
									// vm.queueStreets.rows.push(idMatch);
									// vm.queueStreets.total += 1;
									qsc.wrong.push(idMatch);
								} else {
									qsc.success += 1;
								}
							} else {
								if (qs.parsing) {
									qsc.retry.push(rowDb);
								} else {
									console.log('not found', rowDb);
									qsc.notfound += 1;
								}
							}
						}
					}
					if (count == batch) {
						qsc.page = page;
						vm.loadStreets();
					} else {
						console.log('finished', qsc);
						qs.parsed = qs.parsed.concat(qsc.wrong);
					}
				}
			});
		},
		saveStreets: function() {
			if (!this.runStreets) return;
			var qs = this.queueStreets;
			var qss = qs.save;
			var parsed = qs.parsed;
			if (!parsed.length) return;
			var vm = this;
			var count = 50;
			var batch = parsed.slice(0, count);
			count = batch.length;
			qs.parsed = parsed = parsed.slice(count);
			Utils.loadAjax({
				method: 'POST',
				url: '/api/dne/streets',
				headers: [
					{name:'Content-Type',value:'application/json'}
				],
				body:JSON.stringify(batch),
				cb: function(err, data) {
					var rows = data && data.rows;
					if (err || !rows) {
						console.log('error', err, parsed);
						qss.errors = qss.errors.concat(batch);
					} else {
						for (var i = 0; i < count; i++) {
							if (rows[i] && batch[i].id === rows[i].id) {
								qss.success += 1;
							} else {
								qss.errors.push(batch[i]);
							}
						}
					}
					vm.saveStreets();
				}
			});
		}
	}
};

})();
