<?php

declare(strict_types=1);

namespace App\Handler;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\Diactoros\Response\TextResponse;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Expressive\Template\TemplateRendererInterface;
use \DateTime;
use \DateTimeZone;
use \DateInterval;
use \App\Model\LocalReuniao;
use \App\Model\LocalReuniaoTable;
// https://apps.jw.org/api/public/meeting-search/weekly-meetings?lowerLatitude=-23.71636&lowerLongitude=-46.74324&searchLanguageCode=T&upperLatitude=-23.64754&upperLongitude=-46.66874
class WeeklyMeetingsDb implements RequestHandlerInterface
{
    /**
     * @var TemplateRendererInterface
     */
    private $renderer;
    private $lrtable;
    private $freshDate;

    public function __construct(
        TemplateRendererInterface $renderer,
        LocalReuniaoTable $lrtable
    ) {
        $this->renderer = $renderer;
        $this->lrtable = $lrtable;
    }

    public function validateLimit($query, $name, $min, $max)
    {
        if ( !isset($query[$name]) ) {
            return ['parâmetro '.$name.' não informado', $name, null];
        }
        $val = $query[$name];
        if ( !is_numeric($val) ) {
            return ['parâmetro '.$name.' não é um número', $name, null];
        }
        $val = floatval($val);
        if ( $val < $min ) {
            return ['parâmetro '.$name.' menor que o mínimo '.$min, $name, $val];
        }
        if ( $val > $max ) {
            return ['parâmetro '.$name.' maior que o máximo '.$max, $name, $val];
        }
        return [null, $name, $val];
    }

    public function validateBounds($query)
    {
        $errors = [];
        $values = [];
        $validations = [
            $this->validateLimit($query, 'lowerLatitude', -90, +90),
            $this->validateLimit($query, 'upperLatitude', -90, +90),
            $this->validateLimit($query, 'lowerLongitude', -180, +180),
            $this->validateLimit($query, 'upperLongitude', -180, +180),
        ];
        foreach ($validations as $val) {
            $values[$val[1]] = $val[2];
            if (!empty($val[0])) {
                $errors[] = $val[0];
            }
        }
        return [
            'values' => $values,
            'errors' => $errors
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function handle(ServerRequestInterface $request) : ResponseInterface
    {
        $typeJson = 'application/json; charset=utf-8';
        $query = $request->getQueryParams();
        $vb = $this->validateBounds($query);
        if (count($vb['errors'])) {
            $response = new TextResponse(
                '{"error":"Invalid bounds","message":'.json_encode(implode(', ', $vb['errors'])).'}',
                400,
                ['Content-Type' => [$typeJson]]
            );
            return $response;
        }
        $limit = isset($query['nolimit']) ? 0 : 250;
        $locais = $this->lrtable->searchBounds($vb['values'], $limit);
        $locaisJson = [];
        foreach ($locais as $lr) {
            $item = json_decode($lr['json'], true);
            $lrInserido = LocalReuniao::checkDate($lr['inserido']);
            $lrAtualizado = LocalReuniao::checkDate($lr['atualizado']);
            $item['-rdc-meta'] = [
                'action' => null,
                'id' => $lr['reuniao_id'],
                'inserido' => empty($lrInserido['original']) ? null : $lrInserido['original'],
                'atualizado' => empty($lrAtualizado['original']) ? null : $lrAtualizado['original'],
            ];
            $locaisJson[] = json_encode($item);
        }
        $response = new TextResponse(
            '{"geoLocationList":['.implode(',',$locaisJson).']}',
            200,
            ['Content-Type' => [$typeJson]]
        );
        return $response;
    }

}
