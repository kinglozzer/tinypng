<?php

namespace Kinglozzer\TinyPng\Tests;

use Kinglozzer\TinyPng\Http\Response;

class ResponseTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers Kinglozzer\TinyPng\Http\Response::__construct
     */
    public function testConstructAndGetSet()
    {
        $response = new Response('{}', 200);
        $this->assertInstanceOf('\Kinglozzer\TinyPng\Http\Response', $response);

        $this->assertEquals('{}', $response->getBody());
        $this->assertEquals(200, $response->getStatusCode());

        $response->setBody('test');
        $response->setStatusCode(500);

        $this->assertEquals('test', $response->getBody());
        $this->assertEquals(500, $response->getStatusCode());
    }
}
