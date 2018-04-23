<?php

namespace Nfq\WeatherBundle;

class YahooWeatherProvider implements WeatherProviderInterface
{

    /**
     * {@inheritdoc}
     */
    public function fetch(Location $location): Weather
    {
        $lat = $location->getLat();
        $lon = $location->getLon();

        $baseUrl = 'http://query.yahooapis.com/v1/public/yql';
        $yql = "select * from weather.forecast where woeid in (select woeid from geo.places(1) where text=\"($lat, $lon)\") and u=\"c\"";
        $url = $baseUrl . "?q=" . urlencode($yql) . "&format=json";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, $url);
        $result = curl_exec($ch);
        curl_close($ch);
        $obj = json_decode($result);
        if(!isset($obj->query->results->channel->item->condition->temp)){
            throw new WeatherProviderException('Failed to get YahooWeatherProvider weather');
        }

        $temp = $obj->query->results->channel->item->condition->temp;

        return new Weather($temp);
    }
}
