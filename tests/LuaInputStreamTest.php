<?php
/**
 * LuaInputStreamTest.php
 *
 * @author Koen Vlaswinkel <koen@vlaswinkel.info>
 * @since  21/12/2015 16:57
 */

namespace Vlaswinkel\Lua\Tests;

use Vlaswinkel\Lua\LuaInputStream;

class LuaInputStreamTest extends \PHPUnit_Framework_TestCase {
    public function testSimpleNext() {
        $obj = new LuaInputStream("a");
        $this->assertEquals("a", $obj->next());
    }

    public function testMultipleLines() {
        $obj = new LuaInputStream("a\nb\n");

        $this->assertEquals("a", $obj->next());
        $this->assertEquals("\n", $obj->next());
        $this->assertEquals("b", $obj->next());
        $this->assertEquals("\n", $obj->next());
        $this->assertTrue($obj->eof());
    }

    /**
     * @expectedException \Vlaswinkel\Lua\LuaParseException
     * @expectedExceptionMessage Simple error (1:1)
     */
    public function testSimpleError() {
        $obj = new LuaInputStream("a");
        $this->assertEquals("a", $obj->next());

        $obj->error("Simple error");
    }

    /**
     * @expectedException \Vlaswinkel\Lua\LuaParseException
     * @expectedExceptionMessage Other error (2:1)
     */
    public function testMultipleLineError() {
        $obj = new LuaInputStream("a\nb");
        $this->assertEquals("a", $obj->next());
        $this->assertEquals("\n", $obj->next());
        $this->assertEquals("b", $obj->next());

        $obj->error("Other error");
    }

    /**
     * @expectedException \Vlaswinkel\Lua\LuaParseException
     * @expectedExceptionMessage This error (1:2)
     */
    public function testMultipleColumnError() {
        $obj = new LuaInputStream("ab");
        $this->assertEquals("a", $obj->next());
        $this->assertEquals("b", $obj->next());

        $obj->error("This error");
    }

    /**
     * @expectedException \Vlaswinkel\Lua\LuaParseException
     * @expectedExceptionMessage Complex error (2:2)
     */
    public function testMultipleLineAndColumnError() {
        $obj = new LuaInputStream("ab\nab\n");
        $this->assertEquals("a", $obj->next());
        $this->assertEquals("b", $obj->next());
        $this->assertEquals("\n", $obj->next());

        $this->assertEquals("a", $obj->next());
        $this->assertEquals("b", $obj->next());

        $obj->error("Complex error");
    }
}