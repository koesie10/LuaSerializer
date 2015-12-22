<?php

namespace Vlaswinkel\Lua\Tests;

use Vlaswinkel\Lua\InputStream;

/**
 * Class LuaInputStreamTest
 *
 * @author  Koen Vlaswinkel <koen@vlaswinkel.info>
 * @package Vlaswinkel\Lua\Tests
 */
class LuaInputStreamTest extends \PHPUnit_Framework_TestCase {
    public function testSimpleNext() {
        $obj = new InputStream("a");
        $this->assertEquals("a", $obj->next());
    }

    public function testMultipleLines() {
        $obj = new InputStream("a\nb\n");

        $this->assertEquals("a", $obj->next());
        $this->assertEquals("\n", $obj->next());
        $this->assertEquals("b", $obj->next());
        $this->assertEquals("\n", $obj->next());
        $this->assertTrue($obj->eof());
    }

    /**
     * @expectedException \Vlaswinkel\Lua\ParseException
     * @expectedExceptionMessage Simple error (1:1)
     */
    public function testSimpleError() {
        $obj = new InputStream("a");
        $this->assertEquals("a", $obj->next());

        $obj->error("Simple error");
    }

    /**
     * @expectedException \Vlaswinkel\Lua\ParseException
     * @expectedExceptionMessage Other error (2:1)
     */
    public function testMultipleLineError() {
        $obj = new InputStream("a\nb");
        $this->assertEquals("a", $obj->next());
        $this->assertEquals("\n", $obj->next());
        $this->assertEquals("b", $obj->next());

        $obj->error("Other error");
    }

    /**
     * @expectedException \Vlaswinkel\Lua\ParseException
     * @expectedExceptionMessage This error (1:2)
     */
    public function testMultipleColumnError() {
        $obj = new InputStream("ab");
        $this->assertEquals("a", $obj->next());
        $this->assertEquals("b", $obj->next());

        $obj->error("This error");
    }

    /**
     * @expectedException \Vlaswinkel\Lua\ParseException
     * @expectedExceptionMessage Complex error (2:2)
     */
    public function testMultipleLineAndColumnError() {
        $obj = new InputStream("ab\nab\n");
        $this->assertEquals("a", $obj->next());
        $this->assertEquals("b", $obj->next());
        $this->assertEquals("\n", $obj->next());

        $this->assertEquals("a", $obj->next());
        $this->assertEquals("b", $obj->next());

        $obj->error("Complex error");
    }
}