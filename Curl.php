<?php

namespace Neoan3\Apps;

/**
 * Class Curl
 *
 * @package Neoan3\Apps
 */
class Curl
{

    /**
     * @var string
     */
    private static $responseFormat = 'plain';

    /**
     * set output to verbose
     */
    static function setResponseFormatVerbose(){
        self::$responseFormat = 'verbose';
    }

    /**
     * set output to payload only
     */
    static function setResponseFormatPlain(){
        self::$responseFormat = 'plain';
    }

    /**
     * @param        $url
     * @param        $array
     * @param bool   $auth
     * @param string $authType
     * @param bool   $headerOverride
     *
     * @return array|mixed
     * @throws CurlException
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
     * @throws CurlException
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
     * @throws CurlException
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
     * @throws CurlException
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
     * @throws CurlException
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

        // cookies
        curl_setopt($curl, CURLOPT_COOKIESESSION, true);
        curl_setopt($curl, CURLOPT_COOKIEJAR, __DIR__ . '/_log/cookie.log');
        curl_setopt($curl, CURLOPT_COOKIEFILE, __DIR__ . '/_log/cookie.log');

        $responseHeaders = [];
        self::retrieveResponseHeaders($curl,$responseHeaders);

        $fp = fopen(__DIR__ . '/_log/lastCall.log', 'w+');
        curl_setopt($curl, CURLOPT_VERBOSE, 1);
        curl_setopt($curl, CURLOPT_STDERR, $fp);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $response = curl_exec($curl);
        // retrieve status code
        $status = self::retrieveStatus($curl);
        curl_close($curl);
        // evaluate results
        return self::evaluateResults($status, $response, $responseHeaders);
    }

    /**
     * @param $status
     * @param $response
     * @param $headers
     *
     * @return array|mixed
     * @throws CurlException
     */
    private static function evaluateResults($status, $response, $headers)
    {
        $body = $response;
        if($status > 499){
            throw new CurlException('Unable to retrieve response. Check ' . __DIR__ . '/_log/lastCall.log');
        }
        // json?
        if(mb_strlen($response)>0 && isset($headers['CONTENT-TYPE']) && strpos($headers['CONTENT-TYPE'][0],'application/json') !== false){
            $body = json_decode($response, true);
        }
        if(self::$responseFormat == 'plain'){
            return $body;
        }
        return [
            'headers' => $headers,
            'body' => $body,
            'status' => $status
        ];
    }

    /**
     * @param $curl
     * @param $responseHeaders
     */
    private static function retrieveResponseHeaders($curl, &$responseHeaders){
        curl_setopt($curl, CURLOPT_HEADERFUNCTION, function ($curl, $header) use (&$responseHeaders) {
            $len = strlen($header);
            $header = explode(':', $header, 2);
            if (count($header) < 2) {
                return $len;
            }
            $responseHeaders[strtoupper(trim($header[0]))][] = trim($header[1]);
            return $len;
        });
    }

    /**
     * @param $ch
     *
     * @return int|mixed
     */
    private static function retrieveStatus($ch)
    {
        if (!curl_errno($ch)) {
            return curl_getinfo($ch, CURLINFO_HTTP_CODE);
        }
        return 500;
    }

    /**
     * @return array
     */
    private static function standardHeader()
    {
        return [
            'User-Agent: neoan3',
            'Content-Type: application/json'
        ];
    }
}
