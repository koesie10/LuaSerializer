<?php
/**
 * LuaTokenStreamTest.php
 *
 * @author Koen Vlaswinkel <koen@vlaswinkel.info>
 * @since  21/12/2015 17:19
 */

namespace Vlaswinkel\Lua\Tests;

use Vlaswinkel\Lua\Lua;
use Vlaswinkel\Lua\LuaInputStream;
use Vlaswinkel\Lua\LuaToken;
use Vlaswinkel\Lua\LuaTokenStream;

class LuaTokenStreamTest extends \PHPUnit_Framework_TestCase {
    public function testDoubleQuotedString() {
        $obj = new LuaTokenStream(new LuaInputStream('"foo"'));

        $token = $obj->next();
        $this->assertEquals(LuaToken::TYPE_STRING, $token->getType());
        $this->assertEquals("foo", $token->getValue());
    }

    public function testSingleQuotedString() {
        $obj = new LuaTokenStream(new LuaInputStream("'foo'"));

        $token = $obj->next();
        $this->assertEquals(LuaToken::TYPE_STRING, $token->getType());
        $this->assertEquals("foo", $token->getValue());
    }

    public function testNumberInt() {
        $obj = new LuaTokenStream(new LuaInputStream("1337"));

        $token = $obj->next();
        $this->assertEquals(LuaToken::TYPE_NUMBER, $token->getType());
        $this->assertEquals(1337, $token->getValue());
    }

    public function testNumberFloat() {
        $obj = new LuaTokenStream(new LuaInputStream("13.37"));

        $token = $obj->next();
        $this->assertEquals(LuaToken::TYPE_NUMBER, $token->getType());
        $this->assertEquals(13.37, $token->getValue());
    }

    public function testPunctuation() {
        foreach ([',', '{', '}', '=', '[', ']'] as $punc) {
            $obj = new LuaTokenStream(new LuaInputStream($punc));

            $token = $obj->next();
            $this->assertEquals(LuaToken::TYPE_PUNCTUATION, $token->getType());
            $this->assertEquals($punc, $token->getValue());
        }
    }

    public function testIdentifier() {
        $obj = new LuaTokenStream(new LuaInputStream("foo"));

        $token = $obj->next();
        $this->assertEquals(LuaToken::TYPE_IDENTIFIER, $token->getType());
        $this->assertEquals('foo', $token->getValue());
    }

    public function testKeyword() {
        foreach (Lua::$luaKeywords as $keyword) {
            $obj = new LuaTokenStream(new LuaInputStream($keyword));

            $token = $obj->next();
            $this->assertEquals(LuaToken::TYPE_KEYWORD, $token->getType());
            $this->assertEquals($keyword, $token->getValue());
        }
    }

    /**
     * @expectedException \Vlaswinkel\Lua\LuaParseException
     * @expectedExceptionMessage Cannot handle character: * (ord: 42)
     */
    public function testInvalidCharacter() {
        $obj = new LuaTokenStream(new LuaInputStream("*"));

        $obj->next();
    }
}