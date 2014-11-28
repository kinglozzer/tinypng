<?php

namespace Kinglozzer\TinyPng\Tests;

use Kinglozzer\TinyPng\Exception\InputException;

class InputExceptionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException Kinglozzer\TinyPng\Exception\InputException
     */
    public function testExistence()
    {
        throw new InputException();
    }
}
