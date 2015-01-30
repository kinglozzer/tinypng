<?php

namespace Kinglozzer\TinyPng;

use Kinglozzer\TinyPng\Http\Request;
use Kinglozzer\TinyPng\Http\Response;

class Result
{
    /**
     * @var \Kinglozzer\TinyPng\Http\Request
     */
    protected $request;

    /**
     * @var \Kinglozzer\TinyPng\Http\Response
     */
    protected $response;

    /**
     * @param \Kinglozzer\TinyPng\Http\Response $response
     */
    public function __construct(\Kinglozzer\TinyPng\Http\Response $response)
    {
        $this->setRequest(new Request());
        $this->setResponse($response);
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
     * @param \Kinglozzer\TinyPng\Http\Response $response
     */
    public function setResponse(\Kinglozzer\TinyPng\Http\Response $response)
    {
        $this->response = $response;
    }

    /**
     * @return \Kinglozzer\TinyPng\Http\Response
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * @throws \Exception
     * @param string $path
     */
    public function writeTo($path)
    {
        $body = json_decode($this->getResponse()->getBody(), true);
        $this->makeDir(dirname($path));

        if (! isset($body['output']['url'])) {
            throw new \Exception('Compressed image URL missing from response body');
        }

        $this->getRequest()->setUrl($body['output']['url']);
        $response = $this->getRequest()->send();
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
     * @throws \Exception
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
