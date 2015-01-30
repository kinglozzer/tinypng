<?php

namespace Kinglozzer\TinyPng\Tests;

use Kinglozzer\TinyPng\Compressor;
use Kinglozzer\TinyPng\Http\Request;
use Kinglozzer\TinyPng\Http\Response;

class CompressorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers Kinglozzer\TinyPng\Compressor::__construct
     * @covers Kinglozzer\TinyPng\Compressor::setKey
     * @covers Kinglozzer\TinyPng\Compressor::getKey
     * @covers Kinglozzer\TinyPng\Compressor::setRequest
     * @covers Kinglozzer\TinyPng\Compressor::getRequest
     */
    public function testConstructAndGetSet()
    {
        $compressor = new Compressor('some-test-key');
        $this->assertInstanceOf('\Kinglozzer\TinyPng\Compressor', $compressor);
        $this->assertEquals('some-test-key', $compressor->getKey());
        $this->assertInstanceOf('\Kinglozzer\TinyPng\Http\Request', $compressor->getRequest());

        $compressor->setKey('another-test-key');
        $this->assertEquals('another-test-key', $compressor->getKey());

        $request = new Request();
        $compressor->setRequest($request);
        $this->assertSame($request, $compressor->getRequest());

        $compressor = new Compressor('some-test-key', $request);
        $this->assertSame($request, $compressor->getRequest());
    }

    /**
     * @covers Kinglozzer\TinyPng\Compressor::compress
     */
    public function testCompressAuthenticates()
    {
        $mockRequest = $this->getMock('\Kinglozzer\TinyPng\Http\Request');
        $mockRequest->expects($this->once())
            ->method('authenticate')
            ->with('test-authenticate-key');
        $mockRequest->expects($this->once())
            ->method('send')
            ->will($this->returnValue(new Response('{}', 200)));

        $compressor = new Compressor('test-authenticate-key');
        $compressor->setRequest($mockRequest);
        $result = $compressor->compress('rawfiledata', true);

        $this->assertInstanceOf('\Kinglozzer\TinyPng\Result', $result);
    }

    /**
     * @covers Kinglozzer\TinyPng\Compressor::compress
     */
    public function testCompressRawFileData()
    {
        $mockRequest = $this->getMock('\Kinglozzer\TinyPng\Http\Request');
        $mockRequest->expects($this->once())
            ->method('send')
            ->with('rawfiledata')
            ->will($this->returnValue(new Response('{}', 200)));

        $compressor = new Compressor('test-authenticate-key');
        $compressor->setRequest($mockRequest);
        $compressor->compress('rawfiledata', true);
    }

    /**
     * @covers Kinglozzer\TinyPng\Compressor::compress
     */
    public function testCompressFile()
    {
        $fileData = file_get_contents(__DIR__.'/Fixtures/CompressorTest_CompressFile.jpg');
        $mockRequest = $this->getMock('\Kinglozzer\TinyPng\Http\Request');
        $mockRequest->expects($this->once())
            ->method('send')
            ->with($fileData)
            ->will($this->returnValue(new Response('{}', 200)));

        $compressor = new Compressor('test-authenticate-key');
        $compressor->setRequest($mockRequest);
        $compressor->compress(__DIR__.'/Fixtures/CompressorTest_CompressFile.jpg');
    }

    /**
     * @covers Kinglozzer\TinyPng\Compressor::compress
     */
    public function testCompressErrorResponseTriggersErrorHandler()
    {
        $mockRequest = $this->getMock('\Kinglozzer\TinyPng\Http\Request');
        $mockRequest->expects($this->once())
            ->method('send')
            ->will($this->returnValue(new Response('{"error": "TestError", "message": "Oh no!"}', 500)));

        $mockCompressor = $this->getMock('\Kinglozzer\TinyPng\Compressor', array('handleError'), array('test-key'));
        $mockCompressor->expects($this->once())
            ->method('handleError')
            ->with('TestError', 'Oh no!');

        $mockCompressor->setRequest($mockRequest);
        $mockCompressor->compress('something', true);
    }

    /**
     * @return ReflectionMethod
     */
    protected function getCompressorWithAccessibleErrorHandler()
    {
        $method = new \ReflectionMethod('Kinglozzer\TinyPng\Compressor', 'handleError');
        $method->setAccessible(true);
        return $method;
    }

    /**
     * @covers Kinglozzer\TinyPng\Compressor::handleError
     */
    public function testHandleErrorUnauthorized()
    {
        $this->setExpectedException('Kinglozzer\TinyPng\Exception\AuthorizationException');
        $method = $this->getCompressorWithAccessibleErrorHandler();
        $method->invoke(new Compressor('some-test-key'), 'Unauthorized', 401);
    }

    /**
     * @covers Kinglozzer\TinyPng\Compressor::handleError
     */
    public function testHandleErrorTooManyRequests()
    {
        $this->setExpectedException('Kinglozzer\TinyPng\Exception\AuthorizationException');
        $method = $this->getCompressorWithAccessibleErrorHandler();
        $method->invoke(new Compressor('some-test-key'), 'TooManyRequests', 429);
    }

    /**
     * @covers Kinglozzer\TinyPng\Compressor::handleError
     */
    public function testHandleErrorInputMissing()
    {
        $this->setExpectedException('Kinglozzer\TinyPng\Exception\InputException');
        $method = $this->getCompressorWithAccessibleErrorHandler();
        $method->invoke(new Compressor('some-test-key'), 'InputMissing', 412);
    }

    /**
     * @covers Kinglozzer\TinyPng\Compressor::handleError
     */
    public function testHandleErrorBadSignature()
    {
        $this->setExpectedException('Kinglozzer\TinyPng\Exception\InputException');
        $method = $this->getCompressorWithAccessibleErrorHandler();
        $method->invoke(new Compressor('some-test-key'), 'BadSignature', 415);
    }

    /**
     * @covers Kinglozzer\TinyPng\Compressor::handleError
     */
    public function testHandleErrorUnsupportedFile()
    {
        $this->setExpectedException('Kinglozzer\TinyPng\Exception\InputException');
        $method = $this->getCompressorWithAccessibleErrorHandler();
        $method->invoke(new Compressor('some-test-key'), 'UnsupportedFile', 415);
    }

    /**
     * @covers Kinglozzer\TinyPng\Compressor::handleError
     */
    public function testHandleErrorDecodeError()
    {
        $this->setExpectedException('Kinglozzer\TinyPng\Exception\InputException');
        $method = $this->getCompressorWithAccessibleErrorHandler();
        $method->invoke(new Compressor('some-test-key'), 'DecodeError', 415);
    }

    /**
     * @covers Kinglozzer\TinyPng\Compressor::handleError
     */
    public function testHandleErrorDefaultException()
    {
        $this->setExpectedException('Exception');
        $method = $this->getCompressorWithAccessibleErrorHandler();
        $method->invoke(new Compressor('some-test-key'), '', 500);
    }
}
