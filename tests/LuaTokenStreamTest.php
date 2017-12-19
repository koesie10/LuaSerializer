<?php

namespace Vlaswinkel\Lua\Tests;

use Vlaswinkel\Lua\InputStream;
use Vlaswinkel\Lua\Lua;
use Vlaswinkel\Lua\ParseException;
use Vlaswinkel\Lua\Token;
use Vlaswinkel\Lua\TokenStream;

/**
 * Class LuaTokenStreamTest
 *
 * @author  Koen Vlaswinkel <koen@vlaswinkel.info>
 * @package Vlaswinkel\Lua\Tests
 */
class LuaTokenStreamTest extends \PHPUnit_Framework_TestCase {
    public function testDoubleQuotedString() {
        $obj = new TokenStream(new InputStream('"foo"'));

        $token = $obj->next();
        $this->assertEquals(Token::TYPE_STRING, $token->getType());
        $this->assertEquals("foo", $token->getValue());
    }

    public function testSingleQuotedString() {
        $obj = new TokenStream(new InputStream("'foo'"));

        $token = $obj->next();
        $this->assertEquals(Token::TYPE_STRING, $token->getType());
        $this->assertEquals("foo", $token->getValue());
    }

    public function testNestedString() {
        $obj = new TokenStream(new InputStream("[=[ Like this ]=]"));

        $token = $obj->next();
        $this->assertEquals(Token::TYPE_STRING, $token->getType());
        $this->assertEquals(' Like this ', $token->getValue());
    }

    public function testEscapedString() {
        $obj = new TokenStream(new InputStream('" test \n\r\t\v\\\\\""'));

        $token = $obj->next();
        $this->assertEquals(Token::TYPE_STRING, $token->getType());
        $this->assertEquals(" test \n\r\t\v\\\"", $token->getValue());
    }

    public function testOtherNestedString() {
        $obj = new TokenStream(new InputStream('[=[one [[two]] one]=]'));

        $token = $obj->next();
        $this->assertEquals(Token::TYPE_STRING, $token->getType());
        $this->assertEquals('one [[two]] one', $token->getValue());
    }

    public function testNestedNestedString() {
        $obj = new TokenStream(new InputStream('[=[one [==[two]==] one]=]'));

        $token = $obj->next();
        $this->assertEquals(Token::TYPE_STRING, $token->getType());
        $this->assertEquals('one [==[two]==] one', $token->getValue());
    }

    public function testComplexNestedString() {
        $obj = new TokenStream(new InputStream('[===[one [ [==[ one]===]'));

        $token = $obj->next();
        $this->assertEquals(Token::TYPE_STRING, $token->getType());
        $this->assertEquals('one [ [==[ one', $token->getValue());
    }

    public function testNumberInt() {
        $obj = new TokenStream(new InputStream("1337"));

        $token = $obj->next();
        $this->assertEquals(Token::TYPE_NUMBER, $token->getType());
        $this->assertEquals(1337, $token->getValue());
    }

    public function testNumberFloat() {
        $obj = new TokenStream(new InputStream("13.37"));

        $token = $obj->next();
        $this->assertEquals(Token::TYPE_NUMBER, $token->getType());
        $this->assertEquals(13.37, $token->getValue());
    }

    public function testNumberNegative() {
        $obj = new TokenStream(new InputStream("-13.37"));

        $token = $obj->next();
        $this->assertEquals(Token::TYPE_NUMBER, $token->getType());
        $this->assertEquals(-13.37, $token->getValue());

        $this->assertNull($obj->next());
    }

    public function testNumberHex() {
        $obj = new TokenStream(new InputStream('0x0ef15a66'));

        $token = $obj->next();
        $this->assertEquals(Token::TYPE_NUMBER, $token->getType());
        $this->assertEquals(0x0ef15a66, $token->getValue());

        $this->assertNull($obj->next());
    }

    public function testPunctuation() {
        foreach ([',', '{', '}', '=', '[', ']'] as $punc) {
            $obj = new TokenStream(new InputStream($punc));

            $token = $obj->next();
            $this->assertEquals(Token::TYPE_PUNCTUATION, $token->getType());
            $this->assertEquals($punc, $token->getValue());
        }
    }

    public function testIdentifier() {
        $obj = new TokenStream(new InputStream("foo"));

        $token = $obj->next();
        $this->assertEquals(Token::TYPE_IDENTIFIER, $token->getType());
        $this->assertEquals('foo', $token->getValue());
    }

    public function testKeyword() {
        foreach (Lua::$luaKeywords as $keyword) {
            $obj = new TokenStream(new InputStream($keyword));

            $token = $obj->next();
            $this->assertEquals(Token::TYPE_KEYWORD, $token->getType());
            $this->assertEquals($keyword, $token->getValue());
        }
    }

    /**
     * @expectedException \Vlaswinkel\Lua\ParseException
     * @expectedExceptionMessage Cannot handle character: * (ord: 42)
     */
    public function testInvalidCharacter() {
        $obj = new TokenStream(new InputStream("*"));

        $obj->next();
    }

    /**
     * @expectedException \Vlaswinkel\Lua\ParseException
     */
    public function testUnclosedNestedString() {
        $obj = new TokenStream(new InputStream("[=[ test ]]"));

        $obj->next();
    }

    /**
     * @expectedException \Vlaswinkel\Lua\ParseException
     */
    public function testInvalidDoubleBracketOpenString() {
        $obj = new TokenStream(new InputStream('[=== test ]===]'));

        $obj->next();
    }

    /**
     * @expectedException \Vlaswinkel\Lua\ParseException
     */
    public function testInvalidDoubleBracketCloseString() {
        $obj = new TokenStream(new InputStream('[==[ test ]== '));

        $obj->next();
    }
}