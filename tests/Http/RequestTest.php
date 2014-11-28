<?php

namespace Kinglozzer\TinyPng\Tests;

use Kinglozzer\TinyPng\Http\Request;

class RequestTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers Kinglozzer\TinyPng\Http\Request::__construct
     * @covers Kinglozzer\TinyPng\Http\Request::setUrl
     * @covers Kinglozzer\TinyPng\Http\Request::getUrl
     */
    public function testConstructAndGetSet()
    {
        $request = new Request();
        $this->assertInstanceOf('\Kinglozzer\TinyPng\Http\Request', $request);

        $request->setUrl('http://foo.bar');
        $this->assertEquals('http://foo.bar', $request->getUrl());
    }
}
