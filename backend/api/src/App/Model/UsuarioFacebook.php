<?php

namespace App\Model;

class UsuarioFacebook
{
    public $id;
    public $id_facebook;
    public $nome;
    public $email;
    public $url_foto;
    public $json;
    public $inserido;
    public $atualizado;

    public function exchangeArray(array $data)
    {
        $this->id = !empty($data['id']) ? $data['id'] : null;
        $this->id_facebook = !empty($data['id_facebook']) ? $data['id_facebook'] : null;
        $this->nome     = !empty($data['nome']) ? $data['nome'] : null;
        $this->email    = !empty($data['email']) ? $data['email'] : null;
        $this->url_foto = !empty($data['url_foto']) ? $data['url_foto'] : null;
        $this->json     = !empty($data['json']) ? $data['json'] : null;

        $inserido = !empty($data['inserido']) ? $data['inserido'] : null;
        $this->inserido = $this->checkDate($inserido);

        $atualizado = !empty($data['atualizado']) ? $data['atualizado'] : null;
        $this->atualizado = $this->checkDate($atualizado);
    }

    public function readFacebookJson(array $data)
    {
        $this->id_facebook = !empty($data['id']) ? $data['id'] : null;
        $this->nome = !empty($data['name']) ? $data['name'] : null;
        $this->email = !empty($data['email']) ? $data['email'] : null;
        $this->url_foto = !empty($data['picture_url']) ? $data['picture_url'] : null;
        $this->json = !empty($data) ? json_encode($data, JSON_UNESCAPED_UNICODE) : null;
    }

    public function readDatabaseRow($row)
    {
        if (!empty($row)) {
            $this->id = $row['id'];
        }
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
          'id_facebook' => $this->id_facebook,
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
