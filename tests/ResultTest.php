<?php

namespace Kinglozzer\TinyPng\Tests;

use Kinglozzer\TinyPng\Result;
use Kinglozzer\TinyPng\Http\Request;
use Kinglozzer\TinyPng\Http\Response;

class ResultTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Remove any lingering generated test files/directories
     */
    public function tearDown()
    {
        $testPath = __DIR__.'/Fixtures/ResultTest';

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
     * @covers Kinglozzer\TinyPng\Result::__construct
     * @covers Kinglozzer\TinyPng\Result::setRequest
     * @covers Kinglozzer\TinyPng\Result::getRequest
     * @covers Kinglozzer\TinyPng\Result::setResponse
     * @covers Kinglozzer\TinyPng\Result::getResponse
     */
    public function testConstructAndGetSet()
    {
        $result = new Result(new Response('{}', 200));
        $this->assertInstanceOf('\Kinglozzer\TinyPng\Result', $result);
        $this->assertInstanceOf('\Kinglozzer\TinyPng\Http\Request', $result->getRequest());
        $this->assertInstanceOf('\Kinglozzer\TinyPng\Http\Response', $result->getResponse());

        $request = new Request();
        $result->setRequest($request);
        $this->assertSame($request, $result->getRequest());

        $response = new Response('{}', 200);
        $result->setResponse($response);
        $this->assertSame($response, $result->getResponse());
    }

    /**
     * @covers Kinglozzer\TinyPng\Result::writeTo
     */
    public function testStoreFile()
    {
        $fileData = file_get_contents(__DIR__.'/Fixtures/ResultTest_CompressFile.jpg');

        $response = new Response('{"output": {"url": "http://foo.bar/"}}', 200);

        // Set up a mock request that will return the image data
        $mockRequest = $this->getMock('\Kinglozzer\TinyPng\Http\Request');
        $mockRequest->expects($this->once())
            ->method('send')
            ->will($this->returnValue(new Response($fileData, 200)));

        $result = new Result($response);
        $result->setRequest($mockRequest);

        $result->writeTo(__DIR__.'/Fixtures/ResultTest/CompressedFile.jpg');
        $this->assertFileExists(__DIR__.'/Fixtures/ResultTest/CompressedFile.jpg');

        $result->setResponse(new Response('{"output": {"nourlset": "bar"}}', 200));
        $this->setExpectedException('Exception');
        $result->writeTo('test');
    }

    /**
     * @covers Kinglozzer\TinyPng\Result::makeDir
     */
    public function testMakeDir()
    {
        $mockResponse = new Response('{}', 200);
        $method = new \ReflectionMethod('Kinglozzer\TinyPng\Result', 'makeDir');
        $method->setAccessible(true);

        $result = new Result($mockResponse);
        $method->invoke($result, __DIR__.'/Fixtures/ResultTest/MakeDir');
        $this->assertFileExists(__DIR__.'/Fixtures/ResultTest/MakeDir');


        $method->invoke($result, __DIR__.'/Fixtures/ResultTest/MakeDir/super/deep/hierarchy');
        $this->assertFileExists(__DIR__.'/Fixtures/ResultTest/MakeDir/super/deep/hierarchy');
    }

    /**
     * @covers Kinglozzer\TinyPng\Result::getCompressedFileSize
     */
    public function testGetCompressedFileSize()
    {
        $result = new Result(new Response('{"output": {"size": 999}}', 200));

        $this->assertEquals(999, $result->getCompressedFileSize());
        $this->assertEquals('999 bytes', $result->getCompressedFileSize(true));

        $result->setResponse(new Response('{"output": {"size": 9999}}', 200));
        $this->assertEquals('9.76 KB', $result->getCompressedFileSize(true));

        $result->setResponse(new Response('{"output": {"size": 9999999}}', 200));
        $this->assertEquals('9.54 MB', $result->getCompressedFileSize(true));

        $result->setResponse(new Response('{"output": {"sizenotset": "bar"}}', 200));
        $this->setExpectedException('Exception');
        $result->getCompressedFileSize();
    }

    /**
     * @covers Kinglozzer\TinyPng\Result::getResponseData
     */
    public function testGetResponseData()
    {
        $result = new Result(new Response('{"foo": "bar"}', 200));
        $this->assertEquals(array('foo' => 'bar'), $result->getResponseData());
    }
}
