<?php

declare(strict_types=1);

namespace App\Handler;

use Fig\Http\Message\StatusCodeInterface as StatusCode;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Diactoros\Response\RedirectResponse;
use Zend\Diactoros\Response\JsonResponse;
use Zend\Diactoros\Response\EmptyResponse;
use Zend\Authentication\AuthenticationService;
use Zend\Db\Adapter\AdapterInterface;
use Zend\Db\Adapter\Exception\RuntimeException;
use Zend\Session\Container;
// use \App\MyAuthAdapter;

class DneHandler implements RequestHandlerInterface
{
    private $auth;
    private $dbAdapter;
    // private $template;

    public function __construct(
        AuthenticationService $auth,
        AdapterInterface $dbAdapter
        // MyAuthAdapter $authAdapter
    ) {
        $this->auth        = $auth;
        $this->dbAdapter   = $dbAdapter;
        // $this->authAdapter = $authAdapter;
    }

    public function handle(ServerRequestInterface $request) : ResponseInterface
    {

        switch ($request->getAttribute('action', null)) {
            case 'locations':
                return $this->locationsAction($request);
            case 'neighborhoods':
                return $this->neighborhoodsAction($request);
            case 'streets':
                return $this->streetsAction($request);
            default:
                // Invalid; thus, a 404!
                return new EmptyResponse(StatusCode::STATUS_NOT_FOUND);
        }
    }

    public function locationsAction(ServerRequestInterface $request) : ResponseInterface
    {
        if ($request->getMethod() === 'POST') {
            return $this->locationsSave($request);
        }

        return new JsonResponse([
            'baseUrl' => $request->getAttribute(\App\Middleware\InjectBaseUrlMiddleware::class),
            'error' => 'Method not allowed: '.$request->getMethod(),
        ], 405);
    }

    public function locationsSave(ServerRequestInterface $request) : ResponseInterface
    {
        $body = $request->getParsedBody();
        $db = $this->dbAdapter;

        $qi = function ($name) use ($db) {
            return $db->platform->quoteIdentifier($name);
        };
        $fp = function ($name) use ($db) {
            return $db->driver->formatParameterName($name);
        };
        $columns = [
            'id' => 'localidade_id',
            'uf' => 'uf',
            'nm' => 'nome',
            'nn' => 'nome_norm',
            'ce' => 'cep',
            'st' => 'situacao_nivel',
            'tp' => 'tipo',
            'su' => 'subordinado_id',
            'ab' => 'abreviatura',
            'ib' => 'ibge'
        ];
        $pkey = [
            'id' => true,
        ];
        $col_names = [];
        $col_params = [];
        $col_update = [];
        foreach ($columns as $cprop => $cname) {
            $qiname = $qi($cname);
            $fpprop = $fp($cprop);
            $col_names[] = $qiname;
            $col_params[] = $fpprop;
            if (empty($pkey[$cprop])) {
                $col_update[] = $qiname . ' = ' . $fpprop;
            }
        }

        // $sql = 'INSERT INTO localidades (localidade_id, uf, nome, nome_norm, cep, situacao_nivel, tipo, subordinado_id, abreviatura, ibge) VALUES() ON DUPLICATE KEY UPDATE name="A", age=19'
        $sql = 'INSERT INTO '.$qi('localidades').' ('.
            implode(', ', $col_names).') VALUES ('.
            implode(', ', $col_params).') ON DUPLICATE KEY UPDATE '.
            implode(', ', $col_update);

        try {
            $stm = $this->dbAdapter->createStatement($sql);//, $optionalParameters);
        } catch (RuntimeException $e) {
            $p = $e->getPrevious();
            throw empty($p) ? $e : $p;
        }

        foreach ($body as $row) {
            if ($db->driver->getPrepareType() === $db->driver::PARAMETERIZATION_POSITIONAL) {
                $row_insert = [];
                $row_update = [];
                foreach ($columns as $cprop => $cname) {
                    $row_insert[] = $row[$cprop];
                    if (empty($pkey[$cprop])) {
                        $row_update[] = $row[$cprop];
                    }
                }
                $row = array_merge($row_insert, $row_update);
            }
            $stm->execute($row);
        }

        return new JsonResponse([
            'sql' => $sql,
            'rows' => $body
        ]);
    }

    public function neighborhoodsAction(ServerRequestInterface $request) : ResponseInterface
    {
        if ($request->getMethod() === 'POST') {
            return $this->neighborhoodsSave($request);
        }

        return new JsonResponse([
            'baseUrl' => $request->getAttribute(\App\Middleware\InjectBaseUrlMiddleware::class),
            'error' => 'Method not allowed: '.$request->getMethod(),
        ], 405);
    }

    public function neighborhoodsSave(ServerRequestInterface $request) : ResponseInterface
    {
        $body = $request->getParsedBody();
        $db = $this->dbAdapter;

        $qi = function ($name) use ($db) {
            return $db->platform->quoteIdentifier($name);
        };
        $fp = function ($name) use ($db) {
            return $db->driver->formatParameterName($name);
        };
        $columns = [
            'id' => 'bairro_id',
            'uf' => 'uf',
            'lc' => 'localidade_id',
            'nm' => 'nome',
            'nn' => 'nome_norm',
            'ab' => 'abreviatura'
        ];
        $pkey = [
            'id' => true,
        ];
        $col_names = [];
        $col_params = [];
        $col_update = [];
        foreach ($columns as $cprop => $cname) {
            $qiname = $qi($cname);
            $fpprop = $fp($cprop);
            $col_names[] = $qiname;
            $col_params[] = $fpprop;
            if (empty($pkey[$cprop])) {
                $col_update[] = $qiname . ' = ' . $fpprop;
            }
        }

        // $sql = 'INSERT INTO localidades (localidade_id, uf, nome, nome_norm, cep, situacao_nivel, tipo, subordinado_id, abreviatura, ibge) VALUES() ON DUPLICATE KEY UPDATE name="A", age=19'
        $sql = 'INSERT INTO '.$qi('bairros').' ('.
            implode(', ', $col_names).') VALUES ('.
            implode(', ', $col_params).') ON DUPLICATE KEY UPDATE '.
            implode(', ', $col_update);

        try {
            $stm = $this->dbAdapter->createStatement($sql);//, $optionalParameters);
        } catch (RuntimeException $e) {
            $p = $e->getPrevious();
            throw empty($p) ? $e : $p;
        }

        foreach ($body as $row) {
            if ($db->driver->getPrepareType() === $db->driver::PARAMETERIZATION_POSITIONAL) {
                $row_insert = [];
                $row_update = [];
                foreach ($columns as $cprop => $cname) {
                    $row_insert[] = $row[$cprop];
                    if (empty($pkey[$cprop])) {
                        $row_update[] = $row[$cprop];
                    }
                }
                $row = array_merge($row_insert, $row_update);
            }
            $stm->execute($row);
        }

        return new JsonResponse([
            'sql' => $sql,
            'rows' => $body
        ]);
    }

    public function streetsAction(ServerRequestInterface $request) : ResponseInterface
    {
        if ($request->getMethod() === 'POST') {
            return $this->streetsSave($request);
        } else if ($request->getMethod() === 'GET') {
            return $this->streetsSelect($request);
        }

        return new JsonResponse([
            'baseUrl' => $request->getAttribute(\App\Middleware\InjectBaseUrlMiddleware::class),
            'error' => 'Method not allowed: '.$request->getMethod(),
        ], 405);
    }

    public function streetsSelect(ServerRequestInterface $request) : ResponseInterface
    {
        $query = $request->getQueryParams();
        $uf = $query['uf'];
        $page = intval($query['page']);
        $rowsPage = intval($query['rows']);
        $offset = ($page-1)*$rowsPage;
        $db = $this->dbAdapter;

        $qi = function ($name) use ($db) {
            return $db->platform->quoteIdentifier($name);
        };
        $fp = function ($name) use ($db) {
            return $db->driver->formatParameterName($name);
        };
        $sql = 'SELECT * FROM '.$qi('logradouros').' WHERE '.
            $qi('uf').' = '.$fp('uf').
            ' LIMIT '.$offset.', '.$rowsPage;
        try {
            $stm = $this->dbAdapter->createStatement($sql);//, $optionalParameters);
        } catch (RuntimeException $e) {
            $p = $e->getPrevious();
            throw empty($p) ? $e : $p;
        }
        $result = $stm->execute([$uf]);
        $columns = [
            'id' => 'logradouro_id',
            'uf' => 'uf',
            'lc' => 'localidade_id',
            'ba' => 'bairro_id',
            'nm' => 'nome',
            'nn' => 'nome_norm',
            'cp' => 'complemento',
            'ce' => 'cep',
            'tp' => 'tipo',
            'ut' => 'tipo_utiliza',
            'ab' => 'abreviatura'
        ];
        $mapped = [];
        foreach ($result as $row) {
            $maprow = [];
            foreach ($columns as $cprop => $cname) {
                $maprow[$cprop] = $row[$cname];
            }
            $mapped[] = $maprow;
            $maprow = null;
        }
        return new JsonResponse([
            'sql' => $sql,
            'rows' => $mapped
        ]);
    }

    public function streetsSave(ServerRequestInterface $request) : ResponseInterface
    {
        $body = $request->getParsedBody();
        $db = $this->dbAdapter;

        $qi = function ($name) use ($db) {
            return $db->platform->quoteIdentifier($name);
        };
        $fp = function ($name) use ($db) {
            return $db->driver->formatParameterName($name);
        };
        $columns = [
            'id' => 'logradouro_id',
            'uf' => 'uf',
            'lc' => 'localidade_id',
            'ba' => 'bairro_id',
            'nm' => 'nome',
            'nn' => 'nome_norm',
            'cp' => 'complemento',
            'ce' => 'cep',
            'tp' => 'tipo',
            'ut' => 'tipo_utiliza',
            'ab' => 'abreviatura'
        ];
        $pkey = [
            'id' => true,
        ];
        $col_names = [];
        $col_params = [];
        $col_update = [];
        foreach ($columns as $cprop => $cname) {
            $qiname = $qi($cname);
            $fpprop = $fp($cprop);
            $col_names[] = $qiname;
            $col_params[] = $fpprop;
            if (empty($pkey[$cprop])) {
                $col_update[] = $qiname . ' = ' . $fpprop;
            }
        }

        // $sql = 'INSERT INTO localidades (localidade_id, uf, nome, nome_norm, cep, situacao_nivel, tipo, subordinado_id, abreviatura, ibge) VALUES() ON DUPLICATE KEY UPDATE name="A", age=19'
        $sql = 'INSERT INTO '.$qi('logradouros').' ('.
            implode(', ', $col_names).') VALUES ('.
            implode(', ', $col_params).') ON DUPLICATE KEY UPDATE '.
            implode(', ', $col_update);

        try {
            $stm = $this->dbAdapter->createStatement($sql);//, $optionalParameters);
        } catch (RuntimeException $e) {
            $p = $e->getPrevious();
            throw empty($p) ? $e : $p;
        }

        foreach ($body as $row) {
            if ($db->driver->getPrepareType() === $db->driver::PARAMETERIZATION_POSITIONAL) {
                $row_insert = [];
                $row_update = [];
                foreach ($columns as $cprop => $cname) {
                    $row_insert[] = $row[$cprop];
                    if (empty($pkey[$cprop])) {
                        $row_update[] = $row[$cprop];
                    }
                }
                $row = array_merge($row_insert, $row_update);
            }
            $stm->execute($row);
        }

        return new JsonResponse([
            'sql' => $sql,
            'rows' => $body
        ]);
    }
}
