<?php

namespace App\Model;

use RuntimeException;
use Zend\Db\TableGateway\TableGatewayInterface;
use Zend\Db\Sql\Select;

class UsuarioTwitterTable
{
    private $tableGateway;

    public static $model = UsuarioTwitter::class;
    public static $tableName = 'usuarios_twitter';

    public static function create() {
        return new self::$model();
    }

    public function __construct(TableGatewayInterface $tableGateway)
    {
        $this->tableGateway = $tableGateway;
    }

    public function listToMap($list)
    {
        $map = [];
        foreach ($list as $usuario) {
            $map[$usuario->id] = $usuario;
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
        $rowset = $this->tableGateway->select(['id' => $id]);
        $row = $rowset->current();
        if (! $row) {
            throw new RuntimeException(sprintf(
                'Could not find row with identifier %d',
                $id
            ));
        }

        return $row;
    }

    public function getUsuariosByEmail($email)
    {
        return $this->tableGateway->select(['email' => $email]);
    }

    public function getUsuarioByIdTwitter($id_twitter)
    {
        $rowset = $this->tableGateway->select(['id_twitter' => $id_twitter]);
        return $rowset->current();
    }

    public function saveUsuarioByIdTwitter(UsuarioTwitter $usuario)
    {
        $row = $this->getUsuarioByIdTwitter($usuario->id_twitter);
        if (!empty($row)) $usuario->id = $row->id;
        $this->saveUsuario($usuario);
    }

    public function saveUsuario(UsuarioTwitter $usuario)
    {
        $id = (int) $usuario->id;

        if ($id === 0) {
            return $this->insertUsuario($usuario);
        }

        return $this->updateUsuario($usuario);
    }

    public function insertUsuario(UsuarioTwitter $usuario)
    {
        $this->tableGateway->insert($usuario->toArrayInsert());
        $usuario->id = $this->tableGateway->getLastInsertValue();
    }

    public function updateUsuario(UsuarioTwitter $usuario)
    {
        $this->tableGateway->update(
            $usuario->toArrayUpdate(),
            ['id' => (int) $usuario->id]
        );
    }

    public function deleteUsuario($id)
    {
        $this->tableGateway->delete(['id' => (int) $id]);
    }
}
