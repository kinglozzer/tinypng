<?php

namespace Kinglozzer\TinyPng\Tests;

use Kinglozzer\TinyPng\Exception\AuthorizationException;

class AuthorizationExceptionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException Kinglozzer\TinyPng\Exception\AuthorizationException
     */
    public function testExistence()
    {
        throw new AuthorizationException();
    }
}
