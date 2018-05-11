<?php

namespace App\Model;

use RuntimeException;
use Zend\Db\TableGateway\TableGatewayInterface;
use Zend\Db\Sql\Select;

class UsuarioTable
{
    private $tableGateway;

    public function __construct(TableGatewayInterface $tableGateway)
    {
        $this->tableGateway = $tableGateway;
    }

    public function listToMap($list)
    {
        $map = [];
        foreach ($list as $usuario) {
            $map[$usuario->usuario_id] = $usuario;
        }
        return $map;
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

    public function getUsuario($id)
    {
        $id = (int) $id;
        $rowset = $this->tableGateway->select(['usuario_id' => $id]);
        $row = $rowset->current();
        if (! $row) {
            throw new RuntimeException(sprintf(
                'Could not find row with identifier %d',
                $id
            ));
        }

        return $row;
    }

    public function getUsuarioByEmail($email)
    {
        $rowset = $this->tableGateway->select(['usuario_email' => $email]);
        return $rowset->current();
    }

    public function saveUsuario(Usuario $usuario)
    {
        $id = (int) $usuario->usuario_id;

        if ($id === 0) {
            return $this->insertUsuario($usuario);
        }

        return $this->updateUsuario($usuario);
    }

    public function insertUsuario(Usuario $usuario)
    {
        $usuario->usuario_registro = ['original' => date('Y-m-d H:i:s')];
        $usuario->usuario_autorizado = ['original' => 0];
        $this->tableGateway->insert($usuario->toArray());
        $usuario->usuario_id = $this->tableGateway->getLastInsertValue();
    }

    public function updateUsuario(Usuario $usuario)
    {
        $this->tableGateway->update(
            $usuario->toArray(),
            ['usuario_id' => (int) $usuario->usuario_id]
        );
    }

    public function deleteUsuario($id)
    {
        $this->tableGateway->delete(['usuario_id' => (int) $id]);
    }
}
