<?php

namespace Kinglozzer\TinyPng\Tests;

use Kinglozzer\TinyPng\Client;
use Kinglozzer\TinyPng\Http\Request;
use Kinglozzer\TinyPng\Http\Response;

class ClientTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Remove any lingering generated test files/directories
     */
    public function tearDown()
    {
        $testPath = __DIR__.'/Fixtures/ClientTest';

        if (file_exists($testPath)) {
            $directoryIterator = new \RecursiveDirectoryIterator($testPath);
            $files = new \RecursiveIteratorIterator($directoryIterator, \RecursiveIteratorIterator::CHILD_FIRST);
            foreach ($files as $file) {
                if (in_array($file->getBasename(), array('.', '..')) !== true) {
                    if ($file->isDir() === true) {
                        rmdir($file->getPathName());
                    } elseif (($file->isFile() === true) || ($file->isLink() === true)) {
                        unlink($file->getPathname());
                    }
                }
            }

            rmdir($testPath);
        }
    }

    /**
     * @covers Kinglozzer\TinyPng\Client::__construct
     * @covers Kinglozzer\TinyPng\Client::setKey
     * @covers Kinglozzer\TinyPng\Client::getKey
     * @covers Kinglozzer\TinyPng\Client::setRequest
     * @covers Kinglozzer\TinyPng\Client::getRequest
     */
    public function testConstructAndGetSet()
    {
        $client = new Client('some-test-key');
        $this->assertInstanceOf('\Kinglozzer\TinyPng\Client', $client);
        $this->assertEquals('some-test-key', $client->getKey());
        $this->assertInstanceOf('\Kinglozzer\TinyPng\Http\Request', $client->getRequest());

        $client->setKey('another-test-key');
        $this->assertEquals('another-test-key', $client->getKey());

        $request = new Request();
        $client->setRequest($request);
        $this->assertSame($request, $client->getRequest());

        $client = new Client('some-test-key', $request);
        $this->assertSame($request, $client->getRequest());
    }

    /**
     * @covers Kinglozzer\TinyPng\Client::getResponse
     */
    public function testGetResponse()
    {
        $this->setExpectedException('Kinglozzer\TinyPng\Exception\LogicException');
        $client = new Client('test');
        $client->storeFile('somefile.jpg');

        $reflectionProp = new \ReflectionProperty('Kinglozzer\TinyPng\Client', 'response');
        $reflectionProp->setAccessible(true);
        $reflectionProp->setValue($client, new Response('{}', 200));
        $this->assertInstanceOf('Kinglozzer\TinyPng\Http\Response', $client->getResponse());
    }

    /**
     * @covers Kinglozzer\TinyPng\Client::compress
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

        $client = new Client('test-authenticate-key');
        $client->setRequest($mockRequest);
        $client->compress('rawfiledata');
    }

    /**
     * @covers Kinglozzer\TinyPng\Client::compress
     */
    public function testCompressRawFileData()
    {
        $mockRequest = $this->getMock('\Kinglozzer\TinyPng\Http\Request');
        $mockRequest->expects($this->once())
            ->method('send')
            ->with('rawfiledata')
            ->will($this->returnValue(new Response('{}', 200)));

        $client = new Client('test-authenticate-key');
        $client->setRequest($mockRequest);
        $client->compress('rawfiledata');
    }

    /**
     * @covers Kinglozzer\TinyPng\Client::compress
     */
    public function testCompressFile()
    {
        $fileData = file_get_contents(__DIR__.'/Fixtures/ClientTest_CompressFile.jpg');
        $mockRequest = $this->getMock('\Kinglozzer\TinyPng\Http\Request');
        $mockRequest->expects($this->once())
            ->method('send')
            ->with($fileData)
            ->will($this->returnValue(new Response('{}', 200)));

        $client = new Client('test-authenticate-key');
        $client->setRequest($mockRequest);
        $client->compress(__DIR__.'/Fixtures/ClientTest_CompressFile.jpg');
    }

    /**
     * @covers Kinglozzer\TinyPng\Client::compress
     */
    public function testCompressErrorResponseTriggersErrorHandler()
    {
        $mockRequest = $this->getMock('\Kinglozzer\TinyPng\Http\Request');
        $mockRequest->expects($this->once())
            ->method('send')
            ->will($this->returnValue(new Response('{"error": "TestError", "message": "Oh no!"}', 500)));

        $mockClient = $this->getMock('\Kinglozzer\TinyPng\Client', array('handleError'), array('test-key'));
        $mockClient->expects($this->once())
            ->method('handleError')
            ->with('TestError', 'Oh no!');

        $mockClient->setRequest($mockRequest);
        $mockClient->compress('something');
    }

    /**
     * @return ReflectionMethod
     */
    protected function getClientWithAccessibleErrorHandler()
    {
        $method = new \ReflectionMethod('Kinglozzer\TinyPng\Client', 'handleError');
        $method->setAccessible(true);
        return $method;
    }

    /**
     * @covers Kinglozzer\TinyPng\Client::handleError
     */
    public function testHandleErrorUnauthorized()
    {
        $this->setExpectedException('Kinglozzer\TinyPng\Exception\AuthorizationException');
        $method = $this->getClientWithAccessibleErrorHandler();
        $method->invoke(new Client('some-test-key'), 'Unauthorized', 401);
    }

    /**
     * @covers Kinglozzer\TinyPng\Client::handleError
     */
    public function testHandleErrorTooManyRequests()
    {
        $this->setExpectedException('Kinglozzer\TinyPng\Exception\AuthorizationException');
        $method = $this->getClientWithAccessibleErrorHandler();
        $method->invoke(new Client('some-test-key'), 'TooManyRequests', 429);
    }

    /**
     * @covers Kinglozzer\TinyPng\Client::handleError
     */
    public function testHandleErrorInputMissing()
    {
        $this->setExpectedException('Kinglozzer\TinyPng\Exception\InputException');
        $method = $this->getClientWithAccessibleErrorHandler();
        $method->invoke(new Client('some-test-key'), 'InputMissing', 412);
    }

    /**
     * @covers Kinglozzer\TinyPng\Client::handleError
     */
    public function testHandleErrorBadSignature()
    {
        $this->setExpectedException('Kinglozzer\TinyPng\Exception\InputException');
        $method = $this->getClientWithAccessibleErrorHandler();
        $method->invoke(new Client('some-test-key'), 'BadSignature', 415);
    }

    /**
     * @covers Kinglozzer\TinyPng\Client::handleError
     */
    public function testHandleErrorUnsupportedFile()
    {
        $this->setExpectedException('Kinglozzer\TinyPng\Exception\InputException');
        $method = $this->getClientWithAccessibleErrorHandler();
        $method->invoke(new Client('some-test-key'), 'UnsupportedFile', 415);
    }

    /**
     * @covers Kinglozzer\TinyPng\Client::handleError
     */
    public function testHandleErrorDecodeError()
    {
        $this->setExpectedException('Kinglozzer\TinyPng\Exception\InputException');
        $method = $this->getClientWithAccessibleErrorHandler();
        $method->invoke(new Client('some-test-key'), 'DecodeError', 415);
    }

    /**
     * @covers Kinglozzer\TinyPng\Client::handleError
     */
    public function testHandleErrorDefaultException()
    {
        $this->setExpectedException('Exception');
        $method = $this->getClientWithAccessibleErrorHandler();
        $method->invoke(new Client('some-test-key'), '', 500);
    }

    /**
     * @covers Kinglozzer\TinyPng\Client::storeFile
     */
    public function testStoreFile()
    {
        $fileData = file_get_contents(__DIR__.'/Fixtures/ClientTest_CompressFile.jpg');

        // Set up a mock request that will return the image data
        $mockRequest = $this->getMock('\Kinglozzer\TinyPng\Http\Request');
        $mockRequest->expects($this->once())
            ->method('send')
            ->will($this->returnValue(new Response($fileData, 200)));

        $client = new Client('test', $mockRequest);

        // Setup a fake existing response to work with - any URL will do
        $reflectionProp = new \ReflectionProperty('Kinglozzer\TinyPng\Client', 'response');
        $reflectionProp->setAccessible(true);
        $reflectionProp->setValue($client, new Response('{"output": {"url": "http://foo.bar/"}}', 200));

        $client->storeFile(__DIR__.'/Fixtures/ClientTest/CompressedFile.jpg');
        $this->assertFileExists(__DIR__.'/Fixtures/ClientTest/CompressedFile.jpg');

        // Test that an exception is thrown if URL is missing
        $reflectionProp->setValue($client, new Response('{"output": {"nourlset": "bar"}}', 200));
        $this->setExpectedException('Exception');
        $client->storeFile('test');
    }

    /**
     * @covers Kinglozzer\TinyPng\Client::makeDir
     */
    public function testMakeDir()
    {
        $method = new \ReflectionMethod('Kinglozzer\TinyPng\Client', 'makeDir');
        $method->setAccessible(true);

        $client = new Client('test');
        $method->invoke($client, __DIR__.'/Fixtures/ClientTest/MakeDir');
        $this->assertFileExists(__DIR__.'/Fixtures/ClientTest/MakeDir');


        $method->invoke($client, __DIR__.'/Fixtures/ClientTest/MakeDir/super/deep/hierarchy');
        $this->assertFileExists(__DIR__.'/Fixtures/ClientTest/MakeDir/super/deep/hierarchy');
    }

    /**
     * @covers Kinglozzer\TinyPng\Client::getCompressedFileSize
     */
    public function testGetCompressedFileSize()
    {
        $client = new Client('test');
        $reflectionProp = new \ReflectionProperty('Kinglozzer\TinyPng\Client', 'response');
        $reflectionProp->setAccessible(true);

        $reflectionProp->setValue($client, new Response('{"output": {"size": 999}}', 200));
        $this->assertEquals(999, $client->getCompressedFileSize());
        $this->assertEquals('999 bytes', $client->getCompressedFileSize(true));

        $reflectionProp->setValue($client, new Response('{"output": {"size": 9999}}', 200));
        $this->assertEquals('9.76 KB', $client->getCompressedFileSize(true));

        $reflectionProp->setValue($client, new Response('{"output": {"size": 9999999}}', 200));
        $this->assertEquals('9.54 MB', $client->getCompressedFileSize(true));

        $reflectionProp->setValue($client, new Response('{"output": {"sizenotset": "bar"}}', 200));
        $this->setExpectedException('Exception');
        $client->getCompressedFileSize();
    }

    /**
     * @covers Kinglozzer\TinyPng\Client::getResponseData
     */
    public function testGetResponseData()
    {
        $client = new Client('test');
        $reflectionProp = new \ReflectionProperty('Kinglozzer\TinyPng\Client', 'response');
        $reflectionProp->setAccessible(true);

        $reflectionProp->setValue($client, new Response('{"foo": "bar"}', 200));
        $this->assertEquals(array('foo' => 'bar'), $client->getResponseData());
    }
}
