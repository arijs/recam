<?php

namespace App\Model;

class Usuario
{
    public $usuario_id;
    public $irmao_id;
    public $usuario_nome;
    public $usuario_email;
    public $usuario_senha;
    public $usuario_registro;
    public $usuario_autorizado;

    public function exchangeArray(array $data)
    {
        $this->usuario_id = !empty($data['usuario_id']) ? $data['usuario_id'] : null;
        $this->irmao_id   = !empty($data['irmao_id']) ? $data['irmao_id'] : null;
        $this->usuario_nome  = !empty($data['usuario_nome']) ? $data['usuario_nome'] : null;
        $this->usuario_email = !empty($data['usuario_email']) ? $data['usuario_email'] : null;
        $this->usuario_senha = !empty($data['usuario_senha']) ? $data['usuario_senha'] : null;

        $registro = !empty($data['usuario_registro']) ? $data['usuario_registro'] : null;
        $this->usuario_registro = $this->checkDate($registro);

        $autorizado = !empty($data['usuario_autorizado']) ? $data['usuario_autorizado'] : null;
        $this->usuario_autorizado = $this->checkDate($autorizado);
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

    public function toArray() {
      $registro = $this->usuario_registro;
      $autorizado = $this->usuario_autorizado;
      $registro   = !empty($registro  ['original']) ? $registro  ['original'] : 0;
      $autorizado = !empty($autorizado['original']) ? $autorizado['original'] : 0;
      $data = [
          'usuario_id' => $this->usuario_id,
          'irmao_id' => $this->irmao_id,
          'usuario_nome'  => $this->usuario_nome,
          'usuario_email' => $this->usuario_email,
          'usuario_senha' => $this->usuario_senha,
          'usuario_registro' => $registro,
          'usuario_autorizado' => $autorizado,
      ];
      return $data;
    }
}
