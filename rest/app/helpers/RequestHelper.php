<?php

class RequestHelper
{
    public static function get($method = 'GET', $api_url)
    {
        $client = new \GuzzleHttp\Client();
        $res = $client->request($method, $api_url);
        if ($res->getStatusCode() == 200)
            return json_decode($res->getBody(), true);
        return null;
    }
}