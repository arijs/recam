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
class WeeklyMeetings implements RequestHandlerInterface
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

    /**
     * {@inheritDoc}
     */
    public function handle(ServerRequestInterface $request) : ResponseInterface
    {
        $this->freshDate = new DateTime('now', new DateTimeZone('America/Sao_Paulo'));
        $this->freshDate->sub(new DateInterval('P1D'));
        // Create a curl handle to a non-existing location
        $query = $request->getServerParams()['QUERY_STRING'];
        $ch = curl_init('https://apps.jw.org/api/public/meeting-search/weekly-meetings?'.$query);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        $typeJson = 'application/json; charset=utf-8';

        if($result !== false) {
            $array = json_decode($result, true);
            if(!empty($array) && !empty($array['geoLocationList'])) {
                $this->processList($array['geoLocationList']);
            }
        }

        if($result === false)
        {
            $response = new TextResponse(
                '{"error":"Transfer error","errorInfo":'.json_encode(curl_error($ch)).'}',
                500,
                ['Content-Type' => [$typeJson]]
            );
        }
        else
        {
            $resultType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
            $resultStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $response = new TextResponse(
                $result,
                $resultStatus ? $resultStatus : 200,
                ['Content-Type' => [$typeJson]]
                // 'X-Original-Content-Type' => [$resultType],
            );
        }

        // Close handle
        curl_close($ch);
        return $response;
    }

    public function processList(array $list) : void {
        foreach ($list as $item) $this->processItem($item);
    }

    public function processItem(array $item) : void {
        $prop = !empty($item['properties']) ? $item['properties'] : [];
        $location = !empty($item['location']) ? $item['location'] : [];
        $schedule = !empty($prop['schedule']) ? $prop['schedule'] : [];
        $scurrent = !empty($schedule['current']) ? $schedule['current'] : [];
        $weekend = !empty($scurrent['weekend']) ? $scurrent['weekend'] : [];
        $midweek = !empty($scurrent['midweek']) ? $scurrent['midweek'] : [];
        $localReuniao = $this->lrtable->getReuniaoByGeoId($item['geoId']);
        if (empty($localReuniao)) {
          $localReuniao = $this->lrtable->getReuniaoByOrgId($prop['orgGuid']);
        }
        if (empty($localReuniao)) {
          $localReuniao = new LocalReuniao();
        }
        $lrAtualizado = $localReuniao->atualizado;
        if (!empty($lrAtualizado['dt'])) {
            if ($lrAtualizado['dt'] > $this->freshDate) return;
        }
        $localReuniao->geo_id = $item['geoId'];
        $localReuniao->org_id = $prop['orgGuid'];
        $localReuniao->latitude = $location['latitude'];
        $localReuniao->longitude = $location['longitude'];
        $localReuniao->address = $prop['address'];
        $localReuniao->language = $prop['languageCode'];
        $localReuniao->name = $prop['orgName'];
        $localReuniao->org_type = $prop['orgType'];
        $localReuniao->meio_semana_dia = $midweek['weekday'];
        $localReuniao->meio_semana_horario = $midweek['time'];
        $localReuniao->fim_semana_dia = $weekend['weekday'];
        $localReuniao->fim_semana_horario = $weekend['time'];
        $localReuniao->type = $item['type'];
        $localReuniao->json = json_encode($item);
        $this->lrtable->saveLocalReuniao($localReuniao);
        // {"geoId":"13B6C036-CC4B-4D94-8689-126372827D22"
        // ,"type":"weekly"
        // ,"isPrimary":true
        // ,"location":
        //   {"latitude":-23.6837
        //   ,"longitude":-46.701599}
        // ,"staticMapUrl":""
        // ,"properties":
        //   {"orgGuid":"13B6C036-CC4B-4D94-8689-126372827D22"
        //   ,"orgName":"Socorro - S\u00e3o Paulo SP"
        //   ,"orgType":"CONG"
        //   ,"orgTransliteratedName":""
        //   ,"address":"R. Olympio Carr Ribeiro, 126\r\nVila Calif\u00f3rnia\r\nS\u00e3o Paulo, SP\r\n04775-120\r\n\r\n"
        //   ,"transliteratedAddress":""
        //   ,"languageCode":"T"
        //   ,"schedule":
        //     {"current":
        //       {"weekend":
        //         {"weekday":6
        //         ,"time":"18:30"}
        //       ,"midweek":
        //         {"weekday":4
        //         ,"time":"19:45"}
        //       }
        //     ,"futureDate":null
        //     ,"changeStamp":null}
        //   ,"relatedLanguageCodes":[]
        //   ,"phones":[]
        //   ,"isPrivateMtgPlace":false
        //   ,"memorialAddress":""
        //   ,"memorialTime":""}
        // }
    }
}
