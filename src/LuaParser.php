<?php
/**
 * LuaParser.php
 *
 * @since  20/12/2015 18:45
 * @author Koen Vlaswinkel
 */

namespace Vlaswinkel\Lua;

use Vlaswinkel\Lua\AST\ASTNode;
use Vlaswinkel\Lua\AST\NilASTNode;
use Vlaswinkel\Lua\AST\NumberASTNode;
use Vlaswinkel\Lua\AST\StringASTNode;
use Vlaswinkel\Lua\AST\TableASTNode;
use Vlaswinkel\Lua\AST\TableEntryASTNode;

/**
 * Class LuaParser
 *
 * @param   http://lisperator.net/pltut/parser/the-parser
 *
 * @package Vlaswinkel\Lua
 */
class LuaParser {
    /**
     * @var LuaTokenStream
     */
    private $input;

    /**
     * LuaParser constructor.
     *
     * @param LuaTokenStream $input
     */
    public function __construct(LuaTokenStream $input) {
        $this->input = $input;
    }

    /**
     * @return ASTNode
     *
     * @throws LuaParseException
     */
    public function parse() {
        if ($this->isPunctuation('{')) {
            return $this->parseTable();
        }
        if ($this->isPunctuation('[')) {
            return $this->parseTableKey();
        }
        $token = $this->input->next();
        if ($token->getType() == LuaToken::TYPE_NUMBER) {
            return new NumberASTNode($token->getValue());
        }
        if ($token->getType() == LuaToken::TYPE_STRING || $token->getType() == LuaToken::TYPE_IDENTIFIER) {
            return new StringASTNode($token->getValue());
        }
        if ($token->getType() == LuaToken::TYPE_KEYWORD) {
            if ($token->getValue() === 'nil') {
                return new NilASTNode();
            } else {
                $this->input->error('Unexpected keyword: ' . $token->getValue());
            }
        }
        $this->unexpected();
    }

    /**
     * @return TableASTNode
     */
    protected function parseTable() {
        return new TableASTNode(
            $this->delimited(
                '{',
                '}',
                ',',
                [$this, 'parseTableEntry']
            )
        );
    }

    /**
     * @return TableEntryASTNode
     */
    protected function parseTableEntry() {
        $token = $this->parse();
        if ($this->isPunctuation('=')) {
            $this->skipPunctuation('=');
            $value = $this->parse();
            return new TableEntryASTNode(
                $value,
                $token
            );
        }
        return new TableEntryASTNode($token);
    }

    /**
     * @return ASTNode
     */
    protected function parseTableKey() {
        $this->skipPunctuation('[');
        $token = $this->parse();
        $this->skipPunctuation(']');
        return $token;
    }

    /**
     * @param string   $start
     * @param string   $stop
     * @param string   $separator
     * @param callable $parser
     *
     * @return array
     */
    protected function delimited($start, $stop, $separator, callable $parser) {
        $a     = [];
        $first = true;
        $this->skipPunctuation($start);
        while (!$this->input->eof()) {
            if ($this->isPunctuation($stop)) {
                break;
            }
            if ($first) {
                $first = false;
            } else {
                $this->skipPunctuation($separator);
            }
            if ($this->isPunctuation($stop)) {
                break;
            }
            $a[] = $parser();
        }
        $this->skipPunctuation($stop);
        return $a;
    }

    /**
     * @param string|null $char
     *
     * @return bool
     */
    protected function isPunctuation($char = null) {
        $token = $this->input->peek();
        return $token && $token->getType() == LuaToken::TYPE_PUNCTUATION && ($char === null || $token->getValue(
            ) == $char);
    }

    /**
     * @param string|null $char
     *
     * @throws LuaParseException
     */
    protected function skipPunctuation($char = null) {
        if ($this->isPunctuation($char)) {
            $this->input->next();
        } else {
            $this->input->error('Expecting punctuation: "' . $char . '"');
        }
    }

    /**
     * @throws LuaParseException
     */
    protected function unexpected() {
        $this->input->error('Unexpected token: ' . json_encode($this->input->peek()));
    }
}