<?php

namespace Vlaswinkel\Lua\Tests;

use Vlaswinkel\Lua\AST\BoolASTNode;
use Vlaswinkel\Lua\AST\NilASTNode;
use Vlaswinkel\Lua\AST\NumberASTNode;
use Vlaswinkel\Lua\AST\StringASTNode;
use Vlaswinkel\Lua\AST\TableASTNode;
use Vlaswinkel\Lua\InputStream;
use Vlaswinkel\Lua\Parser;
use Vlaswinkel\Lua\TokenStream;

/**
 * Class LuaParserTest
 *
 * @author  Koen Vlaswinkel <koen@vlaswinkel.info>
 * @package Vlaswinkel\Lua\Tests
 */
class LuaParserTest extends \PHPUnit_Framework_TestCase {
    public function testString() {
        $parser = new Parser(new TokenStream(new InputStream('"foo"')));

        $node = $parser->parse();

        $this->assertEquals(StringASTNode::NAME, $node->getName());
        $this->assertInstanceOf(StringASTNode::class, $node);
        $this->assertEquals("foo", $node->getValue());
    }

    public function testStringWithSpaces() {
        $parser = new Parser(new TokenStream(new InputStream('"foo bar."')));

        $node = $parser->parse();

        $this->assertEquals(StringASTNode::NAME, $node->getName());
        $this->assertInstanceOf(StringASTNode::class, $node);
        $this->assertEquals("foo bar.", $node->getValue());
    }

    public function testAlternateString() {
        $parser = new Parser(new TokenStream(new InputStream('[[foo]]')));

        $node = $parser->parse();

        $this->assertEquals(StringASTNode::NAME, $node->getName());
        $this->assertInstanceOf(StringASTNode::class, $node);
        $this->assertEquals("foo", $node->getValue());
    }

    // https://github.com/koesie10/LuaSerializer/issues/1
    public function testAlternateStringWithSpaces() {
        $parser = new Parser(new TokenStream(new InputStream('[[foo bar.]]')));

        $node = $parser->parse();

        $this->assertEquals(StringASTNode::NAME, $node->getName());
        $this->assertInstanceOf(StringASTNode::class, $node);
        $this->assertEquals("foo bar.", $node->getValue());
    }

    public function testNumber() {
        $parser = new Parser(new TokenStream(new InputStream('1337')));

        $node = $parser->parse();

        $this->assertEquals(NumberASTNode::NAME, $node->getName());
        $this->assertInstanceOf(NumberASTNode::class, $node);
        $this->assertEquals(1337, $node->getValue());
    }

    public function testNil() {
        $parser = new Parser(new TokenStream(new InputStream('nil')));

        $node = $parser->parse();

        $this->assertEquals(NilASTNode::NAME, $node->getName());
        $this->assertInstanceOf(NilASTNode::class, $node);
    }

    public function testBoolTrue() {
        $parser = new Parser(new TokenStream(new InputStream('true')));

        $node = $parser->parse();

        $this->assertEquals(BoolASTNode::NAME, $node->getName());
        $this->assertInstanceOf(BoolASTNode::class, $node);
        $this->assertEquals(true, $node->getValue());
    }

    public function testBoolFalse() {
        $parser = new Parser(new TokenStream(new InputStream('false')));

        $node = $parser->parse();

        $this->assertEquals(BoolASTNode::NAME, $node->getName());
        $this->assertInstanceOf(BoolASTNode::class, $node);
        $this->assertEquals(false, $node->getValue());
    }

    public function testTableKey() {
        $parser = new Parser(new TokenStream(new InputStream('["test"]')));

        $node = $parser->parse();

        $this->assertEquals(StringASTNode::NAME, $node->getName());
        $this->assertInstanceOf(StringASTNode::class, $node);
        $this->assertEquals("test", $node->getValue());
    }

    public function testSimpleTable() {
        $parser = new Parser(
            new TokenStream(
                new InputStream(
                    '{
            foo = "bar"
        }'
                )
            )
        );

        $node = $parser->parse();

        $this->assertEquals(TableASTNode::NAME, $node->getName());
        $this->assertInstanceOf(TableASTNode::class, $node);

        $this->assertCount(1, $node->getEntries());
        $entry = $node->getEntries()[0];

        $this->assertTrue($entry->hasKey());
        $this->assertEquals(StringASTNode::NAME, $entry->getKey()->getName());
        $this->assertInstanceOf(StringASTNode::class, $entry->getKey());
        $this->assertEquals("foo", $entry->getKey()->getValue());

        $this->assertEquals(StringASTNode::NAME, $entry->getValue()->getName());
        $this->assertInstanceOf(StringASTNode::class, $entry->getValue());
        $this->assertEquals("bar", $entry->getValue()->getValue());
    }

    public function testNestedTable() {
        $parser = new Parser(
            new TokenStream(
                new InputStream(
                    '{
            foo = {
                ["test"] = {
                    1337,
                    "bar"
                }
            }
        }'
                )
            )
        );

        $node = $parser->parse();

        $this->assertEquals(TableASTNode::NAME, $node->getName());
        $this->assertInstanceOf(TableASTNode::class, $node);

        $this->assertCount(1, $node->getEntries());
        $entry = $node->getEntries()[0];

        $this->assertTrue($entry->hasKey());
        $this->assertEquals(StringASTNode::NAME, $entry->getKey()->getName());
        $this->assertInstanceOf(StringASTNode::class, $entry->getKey());
        $this->assertEquals("foo", $entry->getKey()->getValue());

        $this->assertEquals(TableASTNode::NAME, $entry->getValue()->getName());
        $this->assertInstanceOf(TableASTNode::class, $entry->getValue());
        $this->assertCount(1, $entry->getValue()->getEntries());

        $nestedEntry = $entry->getValue()->getEntries()[0];

        $this->assertTrue($nestedEntry->hasKey());
        $this->assertEquals(StringASTNode::NAME, $nestedEntry->getKey()->getName());
        $this->assertInstanceOf(StringASTNode::class, $nestedEntry->getKey());
        $this->assertEquals("test", $nestedEntry->getKey()->getValue());

        $this->assertEquals(TableASTNode::NAME, $nestedEntry->getValue()->getName());
        $this->assertInstanceOf(TableASTNode::class, $nestedEntry->getValue());
        $this->assertCount(2, $nestedEntry->getValue()->getEntries());

        $nestedNestedEntry1 = $nestedEntry->getValue()->getEntries()[0];

        $this->assertFalse($nestedNestedEntry1->hasKey());

        $this->assertEquals(NumberASTNode::NAME, $nestedNestedEntry1->getValue()->getName());
        $this->assertInstanceOf(NumberASTNode::class, $nestedNestedEntry1->getValue());
        $this->assertEquals(1337, $nestedNestedEntry1->getValue()->getValue());

        $nestedNestedEntry2 = $nestedEntry->getValue()->getEntries()[1];

        $this->assertFalse($nestedNestedEntry2->hasKey());

        $this->assertEquals(StringASTNode::NAME, $nestedNestedEntry2->getValue()->getName());
        $this->assertInstanceOf(StringASTNode::class, $nestedNestedEntry2->getValue());
        $this->assertEquals("bar", $nestedNestedEntry2->getValue()->getValue());
    }

    public function testTableWithNestedAlternateStrings() {
        $parser = new Parser(
            new TokenStream(
                new InputStream(
                    '{
            foo = [[bar]]
        }'
                )
            )
        );

        $node = $parser->parse();

        $this->assertEquals(TableASTNode::NAME, $node->getName());
        $this->assertInstanceOf(TableASTNode::class, $node);

        $this->assertCount(1, $node->getEntries());
        $entry = $node->getEntries()[0];

        $this->assertTrue($entry->hasKey());
        $this->assertEquals(StringASTNode::NAME, $entry->getKey()->getName());
        $this->assertInstanceOf(StringASTNode::class, $entry->getKey());
        $this->assertEquals("foo", $entry->getKey()->getValue());

        $this->assertEquals(StringASTNode::NAME, $entry->getValue()->getName());
        $this->assertInstanceOf(StringASTNode::class, $entry->getValue());
        $this->assertEquals("bar", $entry->getValue()->getValue());
    }

    /**
     * @expectedException \Vlaswinkel\Lua\ParseException
     */
    public function testInvalid() {
        $parser = new Parser(new TokenStream(new InputStream('{ test[bar }')));

        $parser->parse();
    }

    /**
     * @expectedException \Vlaswinkel\Lua\ParseException
     */
    public function testInvalidKeyword() {
        $parser  = new Parser(new TokenStream(new InputStream('function')));

        $node = $parser->parse();
        $this->assertEquals('test', $node->getName());
    }

    public function testComments() {
        $parser = new Parser(new TokenStream(new InputStream('{
        -- comment
    foo = {
        test = 123
    }
}')));

        $parser->parse();
    }

    public function testInlineComments() {
        $parser = new Parser(new TokenStream(new InputStream('{
    foo = {
        test = 123 -- comment
    }
}')));

        $parser->parse();
    }

    public function testAdvancedTable() {
        $parser = new Parser(new TokenStream(new InputStream(file_get_contents(__DIR__ . '/advanced-test.lua'))));

        $parser->parse();
    }

    public function testTableWithTrailingSemiColon() {
        $parser = new Parser(new TokenStream(new InputStream('{
    A = "B",
    C = [=[ D ]=]
}; --here')));

        $parser->parse();
    }
}