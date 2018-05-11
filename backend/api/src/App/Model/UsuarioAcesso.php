<?php

namespace App\Model;

class UsuarioAcesso
{
    public $usuario_id;
    public $acesso_data;
    public $acesso_contagem;
    public $acesso_hora_inicial;
    public $acesso_hora_final;

    public function exchangeArray(array $data)
    {
        $this->usuario_id      = !empty($data['usuario_id'      ]) ? $data['usuario_id'      ] : null;
        $this->acesso_contagem = !empty($data['acesso_contagem']) ? $data['acesso_contagem'] : null;
        $acesso_data  = !empty($data['acesso_data'        ]) ? $data['acesso_data'        ] : null;
        $hora_inicial = !empty($data['acesso_hora_inicial']) ? $data['acesso_hora_inicial'] : null;
        $hora_final   = !empty($data['acesso_hora_final'  ]) ? $data['acesso_hora_final'  ] : null;
        $this->acesso_data         = $this->checkDate($acesso_data);
        $this->acesso_hora_inicial = $this->checkTime($hora_inicial);
        $this->acesso_hora_final   = $this->checkTime($hora_final);
    }

    public function checkDate($dt) {
        $date = !empty($dt) ? date_parse($dt) : null;
        if (empty($date) || empty($date['year'])) {
            $date = null;
        } else {
            $date['original'] = $dt;
        }
        return $date;
    }

    public function checkTime($dt) {
        $date = !empty($dt) ? date_parse_from_format('H:i:s', $dt) : null;
        if (empty($date)) {
            $date = null;
        } else {
            $date['original'] = $dt;
        }
        return $date;
    }

    public function toArray() {
      $data         = $this->acesso_data;
      $hora_inicial = $this->acesso_hora_inicial;
      $hora_final   = $this->acesso_hora_final;
      $data         = !empty($data        ['original']) ? $data        ['original'] : null;
      $hora_inicial = !empty($hora_inicial['original']) ? $hora_inicial['original'] : null;
      $hora_final   = !empty($hora_final  ['original']) ? $hora_final  ['original'] : null;
      $data = [
          'usuario_id'          => $this->usuario_id,
          'acesso_data'         => $this->acesso_data,
          'acesso_contagem'     => $this->acesso_contagem,
          'acesso_hora_inicial' => $this->acesso_hora_inicial,
          'acesso_hora_final'   => $this->acesso_hora_final,
      ];
      return $data;
    }
}
