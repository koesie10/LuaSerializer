<?php

namespace Vlaswinkel\Lua;

use Vlaswinkel\Lua\AST\ASTNode;
use Vlaswinkel\Lua\AST\NilASTNode;
use Vlaswinkel\Lua\AST\NumberASTNode;
use Vlaswinkel\Lua\AST\StringASTNode;
use Vlaswinkel\Lua\AST\TableASTNode;
use Vlaswinkel\Lua\AST\TableEntryASTNode;

/**
 * Class Parser
 *
 * @see     http://lisperator.net/pltut/parser/the-parser
 *
 * @author  Koen Vlaswinkel <koen@vlaswinkel.info>
 * @package Vlaswinkel\Lua
 */
class Parser {
    /**
     * @var TokenStream
     */
    private $input;

    /**
     * Parser constructor.
     *
     * @param TokenStream $input
     */
    public function __construct(TokenStream $input) {
        $this->input = $input;
    }

    /**
     * @return ASTNode
     *
     * @throws ParseException
     */
    public function parse() {
        $result = $this->parseInternal();

        if (!$this->input->eof()) {
            if ($result instanceof StringASTNode && $this->isPunctuation('=')) {
                $this->skipPunctuation('=');
                $value = $this->parseInternal();

                return new TableASTNode([new TableEntryASTNode($value, $result)]);
            }
            
            $this->input->error('Parser has finished parsing, but end of file was not reached. Next character is ' . $this->input->peek()->getValue());
        }

        return $result;
    }

    /**
     * @return ASTNode
     *
     * @throws ParseException
     */
    protected function parseInternal() {
        if ($this->isPunctuation('{')) {
            return $this->parseTable();
        }
        if ($this->isPunctuation('[')) {
            return $this->parseTableKey();
        }
        $token = $this->input->next();
        if ($token->getType() == Token::TYPE_NUMBER) {
            return new NumberASTNode($token->getValue());
        }
        if ($token->getType() == Token::TYPE_STRING || $token->getType() == Token::TYPE_IDENTIFIER) {
            return new StringASTNode($token->getValue());
        }
        if ($token->getType() == Token::TYPE_KEYWORD) {
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
        $token = $this->parseInternal();
        if ($this->isPunctuation('=')) {
            $this->skipPunctuation('=');
            $value = $this->parseInternal();
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
        $token = $this->parseInternal();
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
        return $token && $token->getType() == Token::TYPE_PUNCTUATION && ($char === null || $token->getValue(
            ) == $char);
    }

    /**
     * @param string|null $char
     *
     * @throws ParseException
     */
    protected function skipPunctuation($char = null) {
        if ($this->isPunctuation($char)) {
            $this->input->next();
        } else {
            $this->input->error('Expecting punctuation: "' . $char . '"');
        }
    }

    /**
     * @throws ParseException
     */
    protected function unexpected() {
        $this->input->error('Unexpected token: ' . json_encode($this->input->peek()));
    }
}