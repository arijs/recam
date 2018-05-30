<?php

namespace App\Model;

use \DateTime;
use \DateTimeZone;

class LocalReuniao
{
    public $reuniao_id;
    public $geo_id;
    public $org_id;
    public $latitude;
    public $longitude;
    public $address;
    public $language;
    public $name;
    public $org_type;
    public $meio_semana_dia;
    public $meio_semana_horario;
    public $fim_semana_dia;
    public $fim_semana_horario;
    public $type;
    public $json;
    public $inserido;
    public $atualizado;

    public function exchangeArray(array $data)
    {
        $this->reuniao_id = !empty($data['reuniao_id']) ? $data['reuniao_id'] : null;
        $this->geo_id   = !empty($data['geo_id']) ? $data['geo_id'] : null;
        $this->org_id   = !empty($data['org_id']) ? $data['org_id'] : null;
        $this->latitude  = !empty($data['latitude']) ? $data['latitude'] : null;
        $this->longitude  = !empty($data['longitude']) ? $data['longitude'] : null;
        $this->address = !empty($data['address']) ? $data['address'] : null;
        $this->language = !empty($data['language']) ? $data['language'] : null;
        $this->name = !empty($data['name']) ? $data['name'] : null;
        $this->org_type = !empty($data['org_type']) ? $data['org_type'] : null;
        $this->meio_semana_dia = !empty($data['meio_semana_dia']) ? $data['meio_semana_dia'] : null;
        $this->meio_semana_horario = !empty($data['meio_semana_horario']) ? $data['meio_semana_horario'] : null;
        $this->fim_semana_dia = !empty($data['fim_semana_dia']) ? $data['fim_semana_dia'] : null;
        $this->fim_semana_horario = !empty($data['fim_semana_horario']) ? $data['fim_semana_horario'] : null;
        $this->type = !empty($data['type']) ? $data['type'] : null;
        $this->json = !empty($data['json']) ? $data['json'] : null;

        $inserido = !empty($data['inserido']) ? $data['inserido'] : null;
        $this->inserido = $this->checkDate($inserido);

        $atualizado = !empty($data['atualizado']) ? $data['atualizado'] : null;
        $this->atualizado = $this->checkDate($atualizado);
    }

    public function checkDate($dt) {
        $date = !empty($dt) ? date_parse($dt) : null;
        if (empty($date) || empty($date['year'])) {
            $date = null;
        } else {
            $date['original'] = $dt;
            $date['dt'] = new DateTime($dt, new DateTimeZone('America/Sao_Paulo'));
        }
        return $date;
    }

    public function toArray() {
      $inserido = $this->inserido;
      $atualizado = $this->atualizado;
      $inserido   = !empty($inserido  ['original']) ? $inserido  ['original'] : 0;
      $atualizado = !empty($atualizado['original']) ? $atualizado['original'] : 0;
      $data = [
          'reuniao_id' => $this->reuniao_id,
          'geo_id' => $this->geo_id,
          'org_id' => $this->org_id,
          'latitude'  => $this->latitude,
          'longitude' => $this->longitude,
          'address' => $this->address,
          'language' => $this->language,
          'name' => $this->name,
          'org_type' => $this->org_type,
          'meio_semana_dia' => $this->meio_semana_dia,
          'meio_semana_horario' => $this->meio_semana_horario,
          'fim_semana_dia' => $this->fim_semana_dia,
          'fim_semana_horario' => $this->fim_semana_horario,
          'type' => $this->type,
          'json' => $this->json,
          'inserido' => $inserido,
          'atualizado' => $atualizado,
      ];
      return $data;
    }
}
