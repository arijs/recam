<?php

namespace App\Model;

use RuntimeException;
use Zend\Db\TableGateway\TableGatewayInterface;
use Zend\Db\Sql\Select;

class UsuarioAcessoTable
{
    private $tableGateway;

    public function __construct(TableGatewayInterface $tableGateway)
    {
        $this->tableGateway = $tableGateway;
    }

    public function fetchAll()
    {
        return $this->tableGateway->select();
    }

    public function fetchAllArray()
    {
        $rowset = $this->fetchAll();
        $list = [];
        foreach ($rowset as $row) {
            $list[] = $row;
        }
        return $list;
    }

    public function fetchOffsetLimit($offset, $limit)
    {
        return $this->tableGateway->select(function (Select $select) use ($offset, $limit) {
            $select->offset($offset)->limit($limit);
        });
    }

    public function countAll()
    {
        $sql = $this->tableGateway->getSql();
        $select = $sql->select();
        $select->columns(array('count' => new \Zend\Db\Sql\Expression('COUNT(*)')));
        $statement = $sql->prepareStatementForSqlObject($select);
        $rowset = $statement->execute();
        $row = $rowset->current();
        if (! $row) {
            throw new RuntimeException(
                'Could not count table rows'
            );
        }

        return $row;
    }

    public function getAcessoDeHoje($id)
    {
        $id = (int) $id;
        $rowset = $this->tableGateway->select([
            'usuario_id' => $id,
            'acesso_data' => new \Zend\Db\Sql\Expression('CURRENT_DATE()'),
        ]);
        return $rowset->current();
    }

    public function criarAcessoDeHoje($id)
    {
        $a = [
            'usuario_id' => (int) $id,
            'acesso_contagem' => 1,
            'acesso_data' => new \Zend\Db\Sql\Expression('CURRENT_DATE()'),
            'acesso_hora_inicial' => new \Zend\Db\Sql\Expression('CURRENT_TIME()'),
            'acesso_hora_final' => new \Zend\Db\Sql\Expression('CURRENT_TIME()'),
        ];
        $this->tableGateway->insert($a);
        return $this->getAcessoDeHoje($id);
    }
    // criarAcessoDeHoje

    public function updateAcessoDeHoje(UsuarioAcesso $usuarioAcesso)
    {
        $id = (int) $usuarioAcesso->usuario_id;
        $this->tableGateway->update(
            [
                'acesso_contagem' => new \Zend\Db\Sql\Expression('acesso_contagem + 1'),
                'acesso_hora_final' => new \Zend\Db\Sql\Expression('CURRENT_TIME()'),
            ],
            [
                'usuario_id' => $id,
                'acesso_data' => new \Zend\Db\Sql\Expression('CURRENT_DATE()'),
            ]
        );
        return $this->getAcessoDeHoje($id);
    }

    public function deleteUsuario($id)
    {
        $this->tableGateway->delete(['usuario_id' => (int) $id]);
    }
}
