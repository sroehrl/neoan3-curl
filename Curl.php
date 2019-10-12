<?php

namespace Neoan3\Apps;

/**
 * Class Curl
 * @package Neoan3\Apps
 */
class Curl
{
    /**
     * @param        $url
     * @param        $array
     * @param bool   $auth
     * @param string $authType
     * @param bool   $headerOverride
     *
     * @return array|mixed
     */
    static function call($url, $array, $auth = false, $authType = 'Bearer', $headerOverride = false)
    {
        $call = '';
        foreach ($array as $key => $value) {
            if (!is_array($value)) {
                $call .= $key . '=' . urlencode($value) . '&';
            }

        }
        $header = self::standardHeader();
        if ($auth && !$headerOverride) {
            $header[] = 'Authorization: ' . $authType . ' ' . $auth;

            $call = json_encode($array);
        }
        if ($headerOverride) {
            $header = $headerOverride;
        }
        return self::curling($url, $call, $header);

    }

    /**
     * @param        $url
     * @param        $array
     * @param bool   $auth
     * @param string $authType
     *
     * @return array|mixed
     */
    static function put($url, $array, $auth = false, $authType = 'Bearer')
    {
        $call = json_encode($array);
        $header = self::standardHeader();
        if ($auth) {
            $header[] = 'Authorization: ' . $authType . ' ' . $auth;
        }
        return self::curling($url, $call, $header, 'PUT');
    }

    /**
     * @param        $url
     * @param array  $array
     * @param bool   $auth
     * @param string $authType
     *
     * @return array|mixed
     */
    static function post($url, $array = [], $auth = false, $authType = 'Bearer')
    {
        $post = json_encode($array);
        $header = self::standardHeader();
        if ($auth) {
            $header[] = 'Authorization: ' . $authType . ' ' . $auth;
        }
        return self::curling($url, $post, $header);
    }

    /**
     * @param        $url
     * @param array  $array
     * @param bool   $auth
     * @param string $authType
     *
     * @return array|mixed
     */
    static function get($url, $array = [], $auth = false, $authType = 'Bearer')
    {
        if (!empty($array)) {
            $url .= '?';
            foreach ($array as $key => $value) {
                $url .= $key . '=' . urlencode($value) . '&';
            }
        }
        $header = self::standardHeader();
        if ($auth) {
            $header[] = 'Authorization: ' . $authType . ' ' . $auth;
        }
        return self::curling($url, [], $header, 'GET');

    }

    /**
     * @param        $url
     * @param        $call
     * @param        $header
     * @param string $type
     *
     * @return array|mixed
     */
    static function curling($url, $call, $header, $type = 'POST')
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);

        curl_setopt($curl, CURLOPT_URL, $url);

        if ($type == 'POST' || $type == 'PUT') {
            curl_setopt($curl, CURLOPT_POSTFIELDS, $call);
        }

        if ($type == 'POST') {
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
        } else {
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $type);
        }

        //curl_setopt( $curl, CURLOPT_HEADER, 0);
        // cookies
        curl_setopt($curl, CURLOPT_COOKIESESSION, true);
        curl_setopt($curl, CURLOPT_COOKIEJAR, neoan_path . '/apps/plugins/neoanCurl/cookie.txt');
        curl_setopt($curl, CURLOPT_COOKIEFILE, neoan_path . '/apps/plugins/neoanCurl/cookie.txt');

        $fp = fopen(path . '/asset/Curl-errorlog.txt', 'w+');
        curl_setopt($curl, CURLOPT_VERBOSE, 1);
        curl_setopt($curl, CURLOPT_STDERR, $fp);
        curl_setopt($curl, CURLOPT_HTTP200ALIASES, [400]);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $return = curl_exec($curl);
        curl_close($curl);

        $answer = json_decode($return, true);
        switch (json_last_error()) {
            case JSON_ERROR_NONE:
                return $answer;
                break;
            default:
                return ['error' => 'API-error', 'info' => $return];
                break;
        }
    }

    private static function standardHeader()
    {
        return [
            'User-Agent: neoan3',
            'Content-Type: application/json'
        ];
    }
}
