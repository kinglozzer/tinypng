<?php

namespace Kinglozzer\TinyPng;

use Kinglozzer\TinyPng\Http\Request;
use Kinglozzer\TinyPng\Exception\AuthorizationException;
use Kinglozzer\TinyPng\Exception\InputException;
use Kinglozzer\TinyPng\Exception\LogicException;

class Compressor
{
    /**
     * @var string
     */
    protected $key;

    /**
     * @var \Kinglozzer\TinyPng\Http\Request
     */
    protected $request;

    /**
     * @param string $key TinyPNG API key
     * @param \Kinglozzer\TinyPng\Http\Request|null $request
     */
    public function __construct($key, \Kinglozzer\TinyPng\Http\Request $request = null)
    {
        $this->setKey($key);

        if (! $request) {
            $request = new Request();
        }

        $this->setRequest($request);
    }

    /**
     * @param string $key
     */
    public function setKey($key)
    {
        $this->key = $key;
    }

    /**
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @param \Kinglozzer\TinyPng\Http\Request $request
     */
    public function setRequest(\Kinglozzer\TinyPng\Http\Request $request)
    {
        $this->request = $request;
    }

    /**
     * @return \Kinglozzer\TinyPng\Http\Request
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @param string $file Path to the file or raw file data
     * @param boolean $isRawData Set true if passing raw file data as first parameter
     * @return \Kinglozzer\TinyPng\Result
     */
    public function compress($file, $isRawData = false)
    {
        $contents = $isRawData ? $file : file_get_contents($file);
        $this->request->authenticate($this->key);
        $response = $this->request->send($contents);

        $body = json_decode($response->getBody(), true);
        $statusCode = $response->getStatusCode();

        if ($statusCode < 200 || $statusCode > 399) {
            $error = isset($body['error']) ? $body['error'] : '';
            $message = isset($body['message']) ? $body['message'] : '';

            $this->handleError($error, $message);
        }

        return new Result($response);
    }

    /**
     * @throws \Kinglozzer\TinyPng\Exception\AuthorizationException
     * @throws \Kinglozzer\TinyPng\Exception\InputException
     * @throws \Exception
     * @param string $error
     * @param string $message
     */
    protected function handleError($error, $message)
    {
        switch ($error) {
            case "Unauthorized":
            case "TooManyRequests":
                throw new AuthorizationException($message);
                break;
            case "InputMissing":
            case "BadSignature":
            case "UnsupportedFile":
            case "DecodeError":
                throw new InputException($message);
                break;
            default:
                throw new \Exception($message);
        }
    }
}
