<?php

namespace Vlaswinkel\Lua\Tests;

use Vlaswinkel\Lua\InputStream;
use Vlaswinkel\Lua\LuaToPhpConverter;
use Vlaswinkel\Lua\Parser;
use Vlaswinkel\Lua\TokenStream;

/**
 * Class LuaToPhpConverterTest
 *
 * @author  Koen Vlaswinkel <koen@vlaswinkel.info>
 * @package Vlaswinkel\Lua\Tests
 */
class LuaToPhpConverterTest extends \PHPUnit_Framework_TestCase {
    public function testString() {
        $parser = new Parser(new TokenStream(new InputStream('"foo"')));

        $node = $parser->parse();

        $this->assertEquals("foo", LuaToPhpConverter::convertToPhpValue($node));
    }

    public function testNumber() {
        $parser = new Parser(new TokenStream(new InputStream('1337')));

        $node = $parser->parse();

        $this->assertEquals(1337, LuaToPhpConverter::convertToPhpValue($node));
    }

    public function testNil() {
        $parser = new Parser(new TokenStream(new InputStream('nil')));

        $node = $parser->parse();

        $this->assertEquals(null, LuaToPhpConverter::convertToPhpValue($node));
    }

    public function testSimpleTable() {
        $parser = new Parser(new TokenStream(new InputStream('{ foo = "bar" }')));

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
        $parser = new Parser(new TokenStream(new InputStream('{ foo = { "bar" = { 1337 } } }')));

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
        $parser = new Parser(new TokenStream(new InputStream('{}')));

        $node = $parser->parse();

        $result = LuaToPhpConverter::convertToPhpValue($node);

        $this->assertEquals([], $result);
    }

    public function testSimpleTableWithComments() {
        $parser = new Parser(new TokenStream(new InputStream('{ 
        foo = "bar" -- comment
        }')));

        $node = $parser->parse();

        $result = LuaToPhpConverter::convertToPhpValue($node);

        $this->assertEquals(
            [
                'foo' => 'bar',
            ],
            $result
        );
    }

    public function testAdvancedTable() {
        $parser = new Parser(new TokenStream(new InputStream(file_get_contents(__DIR__ . '/advanced-test.lua'))));

        $node = $parser->parse();

        $result = LuaToPhpConverter::convertToPhpValue($node);

        $this->assertEquals(
            1,
            count($result)
        );
    }

    // https://github.com/koesie10/LuaSerializer/issues/6
    public function testNegativeNumberTable() {
        $parser = new Parser(new TokenStream(new InputStream('{1.75,0.6,-1.78}')));

        $node = $parser->parse();

        $result = LuaToPhpConverter::convertToPhpValue($node);

        $this->assertEquals(
            [1.75, 0.6, -1.78,],
            $result
        );
    }

    // https://github.com/koesie10/LuaSerializer/issues/7
    public function testHexNumberTable() {
        $parser = new Parser(new TokenStream(new InputStream('{0x0ef15a66,0xf10e0e66,0x3e4c5266}')));

        $node = $parser->parse();

        $result = LuaToPhpConverter::convertToPhpValue($node);

        $this->assertEquals(
            [0x0ef15a66, 0xf10e0e66, 0x3e4c5266],
            $result
        );
    }

    // https://github.com/koesie10/LuaSerializer/issues/8
    public function testBooleanTable() {
        $parser = new Parser(new TokenStream(new InputStream('{boolTrue = true, boolFalse = false}')));

        $node = $parser->parse();

        $result = LuaToPhpConverter::convertToPhpValue($node);

        $this->assertEquals(
            [
                'boolTrue'  => true,
                'boolFalse' => false,
            ],
            $result
        );
    }
}