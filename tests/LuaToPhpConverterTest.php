<?php
/**
 * LuaToPhpConverterTest.php
 *
 * @author Koen Vlaswinkel <koen@vlaswinkel.info>
 * @since  21/12/2015 17:50
 */

namespace Vlaswinkel\Lua\Tests;

use Vlaswinkel\Lua\LuaInputStream;
use Vlaswinkel\Lua\LuaParser;
use Vlaswinkel\Lua\LuaTokenStream;
use Vlaswinkel\Lua\LuaToPhpConverter;

class LuaToPhpConverterTest extends \PHPUnit_Framework_TestCase {
    public function testString() {
        $parser = new LuaParser(new LuaTokenStream(new LuaInputStream('"foo"')));

        $node = $parser->parse();

        $this->assertEquals("foo", LuaToPhpConverter::convertToPhpValue($node));
    }

    public function testNumber() {
        $parser = new LuaParser(new LuaTokenStream(new LuaInputStream('1337')));

        $node = $parser->parse();

        $this->assertEquals(1337, LuaToPhpConverter::convertToPhpValue($node));
    }

    public function testNil() {
        $parser = new LuaParser(new LuaTokenStream(new LuaInputStream('nil')));

        $node = $parser->parse();

        $this->assertEquals(null, LuaToPhpConverter::convertToPhpValue($node));
    }

    public function testSimpleTable() {
        $parser = new LuaParser(new LuaTokenStream(new LuaInputStream('{ foo = "bar" }')));

        $node = $parser->parse();

        $result = LuaToPhpConverter::convertToPhpValue($node);

        $this->assertEquals(
            [
                'foo' => 'bar',
            ],
            $result
        );
    }

    public function testNestedTable() {
        $parser = new LuaParser(new LuaTokenStream(new LuaInputStream('{ foo = { "bar" = { 1337 } } }')));

        $node = $parser->parse();

        $result = LuaToPhpConverter::convertToPhpValue($node);

        $this->assertEquals(
            [
                'foo' => [
                    'bar' => [
                        1337,
                    ],
                ],
            ],
            $result
        );
    }

    public function testEmptyTable() {
        $parser = new LuaParser(new LuaTokenStream(new LuaInputStream('{}')));

        $node = $parser->parse();

        $result = LuaToPhpConverter::convertToPhpValue($node);

        $this->assertEquals([], $result);
    }
}