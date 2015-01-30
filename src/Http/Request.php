<?php

namespace Kinglozzer\TinyPng\Http;

use Kinglozzer\TinyPng\Http\Response;

class Request
{
    /**
     * @var string
     */
    protected $url = 'https://api.tinypng.com/shrink';

    /**
     * @var resource Curl handle
     */
    protected $curl;

    public function __construct()
    {
        $this->curl = curl_init();
        curl_setopt_array(
            $this->curl,
            array(
                CURLOPT_RETURNTRANSFER => 1,
                CURLOPT_BINARYTRANSFER => 1
            )
        );
    }

    /**
     * @param string $url
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param string $key TingPNG API key
     */
    public function authenticate($key)
    {
        curl_setopt($this->curl, CURLOPT_USERPWD, 'api:'.$key);
    }

    /**
     * Clear authentication credentials
     */
    public function clearAuthentication()
    {
        curl_setopt($this->curl, CURLOPT_USERPWD, null);
    }

    /**
     * @param string|null $fileData The raw file data to post
     * @return \Kinglozzer\TinyPng\Http\Response
     */
    public function send($fileData = null)
    {
        curl_setopt_array(
            $this->curl,
            array(
                CURLOPT_URL => $this->url,
                CURLOPT_POSTFIELDS => ($fileData) ? $fileData : '',
                CURLOPT_POST => ($fileData) ? 1 : 0
            )
        );

        $responseBody = curl_exec($this->curl);
        $responseCode = curl_getinfo($this->curl, CURLINFO_HTTP_CODE);
        
        return new Response($responseBody, $responseCode);
    }
}
