<?php

namespace Kinglozzer\TinyPng;

use Kinglozzer\TinyPng\Http\Request;
use Kinglozzer\TinyPng\Exception\AuthorizationException;
use Kinglozzer\TinyPng\Exception\InputException;
use Kinglozzer\TinyPng\Exception\LogicException;

class Client
{
    /**
     * @var string
     */
    protected $key;

    /**
     * @var Kinglozzer\TinyPng\Http\Request
     */
    protected $request;

    /**
     * @var Kinglozzer\TinyPng\Http\Response
     */
    protected $response;

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
     * @return Kinglozzer\TinyPng\Http\Request
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @throws Kinglozzer\TinyPng\Exception\LogicException
     * @return Kinglozzer\TinyPng\Http\Response
     */
    public function getResponse()
    {
        if (! $this->response) {
            throw new LogicException(
                'No response data available. Perhaps the image hasn\'t been compressed yet?'
            );
        }

        return $this->response;
    }

    /**
     * @param string $file Path to the file or raw file data
     * @param boolean $isRawData Set true if passing raw file data as first parameter
     * @return self
     */
    public function compress($file, $isRawData = false)
    {
        $contents = $isRawData ? $file : file_get_contents($file);
        $this->request->authenticate($this->key);
        $this->response = $this->request->send($contents);

        $body = json_decode($this->response->getBody(), true);
        $statusCode = $this->response->getStatusCode();

        if ($statusCode < 200 || $statusCode > 399) {
            $error = isset($body['error']) ? $body['error'] : '';
            $message = isset($body['message']) ? $body['message'] : '';

            $this->handleError($error, $message);
        }

        return $this;
    }

    /**
     * @throws Kinglozzer\TinyPng\Exception\AuthorizationException
     * @throws Kinglozzer\TinyPng\Exception\InputException
     * @throws Exception
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

    /**
     * @throws Exception
     * @param string $path
     */
    public function storeFile($path)
    {
        $body = json_decode($this->getResponse()->getBody(), true);
        $this->makeDir(dirname($path));

        if (! isset($body['output']['url'])) {
            throw new \Exception('Compressed image URL missing from response body');
        }

        $this->request->clearAuthentication();
        $this->request->setUrl($body['output']['url']);
        $response = $this->request->send();
        $image = $response->getBody();
        file_put_contents($path, $image);
    }

    /**
     * Creates a directory, recursively iterating through, and creating, missing
     * parent directories
     * @param string $path
     */
    protected function makeDir($path)
    {
        if (! file_exists(dirname($path))) {
            $this->makeDir(dirname($path));
        }

        if (! file_exists($path)) {
            mkdir($path);
        }
    }

    /**
     * @throws Exception
     * @param boolean $humanReadable
     * @return string|int
     */
    public function getCompressedFileSize($humanReadable = false)
    {
        $body = json_decode($this->getResponse()->getBody(), true);

        if (! isset($body['output']['size'])) {
            throw new \Exception('Compressed image size missing from response body');
        }

        $size = $body['output']['size'];

        if ($humanReadable) {
            $units = array('bytes', 'KB', 'MB');

            $size = max($size, 0);
            $pow = floor(($size ? log($size) : 0) / log(1024));
            $pow = min($pow, count($units) - 1);
            $size /= pow(1024, $pow);

            $size = round($size, 2) . ' ' . $units[$pow];
        }
    
        return $size;
    }

    /**
     * @return array
     */
    public function getResponseData()
    {
        return json_decode($this->getResponse()->getBody(), true);
    }
}
