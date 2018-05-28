<?php

declare(strict_types=1);

namespace App\Handler;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\Diactoros\Response\TextResponse;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Expressive\Template\TemplateRendererInterface;
// https://apps.jw.org/api/public/meeting-search/weekly-meetings?lowerLatitude=-23.71636&lowerLongitude=-46.74324&searchLanguageCode=T&upperLatitude=-23.64754&upperLongitude=-46.66874
class WeeklyMeetings implements RequestHandlerInterface
{
    /**
     * @var TemplateRendererInterface
     */
    private $renderer;

    public function __construct(TemplateRendererInterface $renderer)
    {
        $this->renderer = $renderer;
    }

    /**
     * {@inheritDoc}
     */
    public function handle(ServerRequestInterface $request) : ResponseInterface
    {
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

    }
}
