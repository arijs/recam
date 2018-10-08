<?php

namespace App\Model;

class UsuarioTwitter
{
    public $id;
    public $id_twitter;
    public $nome;
    public $email;
    public $url_foto;
    public $json;
    public $inserido;
    public $atualizado;

    public function exchangeArray(array $data)
    {
        $this->id = !empty($data['id']) ? $data['id'] : null;
        $this->id_twitter = !empty($data['id_twitter']) ? $data['id_twitter'] : null;
        $this->nome     = !empty($data['nome']) ? $data['nome'] : null;
        $this->email    = !empty($data['email']) ? $data['email'] : null;
        $this->url_foto = !empty($data['url_foto']) ? $data['url_foto'] : null;
        $this->json     = !empty($data['json']) ? $data['json'] : null;

        $inserido = !empty($data['inserido']) ? $data['inserido'] : null;
        $this->inserido = $this->checkDate($inserido);

        $atualizado = !empty($data['atualizado']) ? $data['atualizado'] : null;
        $this->atualizado = $this->checkDate($atualizado);
    }

    public function readTwitterJson(array $data)
    {
        $this->id_twitter = !empty($data['id_str']) ? $data['id_str'] : null;
        $this->nome = !empty($data['name']) ? $data['name'] : null;
        $this->email = !empty($data['email']) ? $data['email'] : null;
        $this->url_foto = !empty($data['profile_image_url_https']) ? $data['profile_image_url_https'] : null;
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
            'id_twitter' => $this->id_twitter,
            'nome'  => $this->nome,
            'email' => $this->email,
            'url_foto' => $this->url_foto,
            'json'  => $this->json,
            'inserido' => $inserido,
            'atualizado' => $atualizado,
        ];
        return $data;
    }

    public function toArrayInsert() {
        $this->inserido = $this->checkDate(date('Y-m-d H:i:s'));
        $this->atualizado = $this->checkDate('0000-00-00 00:00:00');
        $data = $this->toArray();
        $data['atualizado'] = 0;
        return $data;
    }

    public function toArrayUpdate() {
        $this->atualizado = $this->checkDate(date('Y-m-d H:i:s'));
        $data = $this->toArray();
        unset($data['inserido']);
        return $data;
    }
}
