<?php

namespace Kinglozzer\TinyPng\Tests;

use Kinglozzer\TinyPng\Exception\LogicException;

class LogicExceptionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException Kinglozzer\TinyPng\Exception\LogicException
     */
    public function testExistence()
    {
        throw new LogicException();
    }
}
