<?php

namespace App\Model;

class UsuarioLinkedin
{
    public $id;
    public $id_linkedin;
    public $nome;
    public $email;
    public $url_foto;
    public $json;
    public $inserido;
    public $atualizado;

    public function exchangeArray(array $data)
    {
        $this->id = !empty($data['id']) ? $data['id'] : null;
        $this->id_linkedin = !empty($data['id_linkedin']) ? $data['id_linkedin'] : null;
        $this->nome     = !empty($data['nome']) ? $data['nome'] : null;
        $this->email    = !empty($data['email']) ? $data['email'] : null;
        $this->url_foto = !empty($data['url_foto']) ? $data['url_foto'] : null;
        $this->json     = !empty($data['json']) ? $data['json'] : null;

        $inserido = !empty($data['inserido']) ? $data['inserido'] : null;
        $this->inserido = $this->checkDate($inserido);

        $atualizado = !empty($data['atualizado']) ? $data['atualizado'] : null;
        $this->atualizado = $this->checkDate($atualizado);
    }

    public function readLinkedinJson(array $data)
    {
        $firstName = !empty($data['firstName']) ? $data['firstName'] : null;
        $lastName = !empty($data['lastName']) ? $data['lastName'] : null;
        $name = empty($lastName) ? $firstName :
          empty($firstName) ? $lastName :
          "{$firstName} {$lastName}";
        $this->id_linkedin = !empty($data['id']) ? $data['id'] : null;
        $this->nome = $name;
        $this->email = !empty($data['emailAddress']) ? $data['emailAddress'] : null;
        $this->url_foto = !empty($data['pictureUrl']) ? $data['pictureUrl'] : null;
        $this->json = !empty($data) ? json_encode($data, JSON_UNESCAPED_UNICODE) : null;
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
      $inserido = $this->inserido;
      $atualizado = $this->atualizado;
      $inserido   = !empty($inserido  ['original']) ? $inserido  ['original'] : 0;
      $atualizado = !empty($atualizado['original']) ? $atualizado['original'] : 0;
      $data = [
          'id' => $this->id,
          'id_linkedin' => $this->id_linkedin,
          'nome'  => $this->nome,
          'email' => $this->email,
          'url_foto' => $this->url_foto,
          'json'  => $this->json,
          'inserido' => $inserido,
          'atualizado' => $atualizado,
      ];
      return $data;
    }
}
