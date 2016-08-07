<?php

namespace Vlaswinkel\Lua;

/**
 * Class TokenStream
 *
 * @see     http://lisperator.net/pltut/parser/token-stream
 *
 * @author  Koen Vlaswinkel <koen@vlaswinkel.info>
 * @package Vlaswinkel\Lua
 */
class TokenStream {
    private $current = null;
    /**
     * @var InputStream
     */
    private $input;

    /**
     * TokenStream constructor.
     *
     * @param InputStream $input
     */
    public function __construct(InputStream $input) {
        $this->input = $input;
    }

    /**
     * @return Token
     */
    public function next() {
        $token         = $this->current;
        $this->current = null;
        if ($token) {
            return $token;
        }
        return $this->readNext();
    }

    /**
     * @return bool
     */
    public function eof() {
        return $this->peek() == null;
    }

    /**
     * @return Token
     */
    public function peek() {
        if ($this->current) {
            return $this->current;
        }
        $this->current = $this->readNext();
        return $this->current;
    }

    /**
     * @param string $msg
     *
     * @throws ParseException
     */
    public function error($msg) {
        $this->input->error($msg);
    }

    /**
     * @return Token
     * @throws ParseException
     */
    protected function readNext() {
        $this->readWhile([$this, 'isWhitespace']);
        if ($this->input->eof()) {
            return null;
        }
        $char = $this->input->peek();
        if ($char == "--") {
            $this->skipComment();
            return $this->readNext();
        }
        if ($char == '"') {
            return $this->readDoubleQuotedString();
        }
        if ($char == '\'') {
            return $this->readSingleQuotedString();
        }
        if ($this->isDoubleBracketString()) {
            return $this->readDoubleBracketString();
        }
        if ($this->isDigit($char)) {
            return $this->readNumber();
        }
        if ($this->isStartIdentifierCharacter($char)) {
            return $this->readIdentifier();
        }
        if ($this->isPunctuation($char)) {
            return $this->readPunctuation();
        }
        $this->input->error('Cannot handle character: ' . $char . ' (ord: ' . ord($char) . ')');
    }

    protected function skipComment() {
        $this->readWhile(
            function ($char) {
                return $char != "\n";
            }
        );
        $this->input->next();
    }

    /**
     * @return Token
     */
    protected function readDoubleQuotedString() {
        return new Token(Token::TYPE_STRING, $this->readEscaped('"'));
    }

    /**
     * @return Token
     */
    protected function readSingleQuotedString() {
        return new Token(Token::TYPE_STRING, $this->readEscaped('\''));
    }

    /**
     * @return Token
     */
    protected function readDoubleBracketString() {
        // we cannot use readEscaped because it only supports a single char as $end
        $escaped = false;
        $str     = "";
        // skip both
        $this->input->next();
        $this->input->next();
        while (!$this->input->eof()) {
            $char = $this->input->next();
            if ($escaped) {
                $str .= $char;
                $escaped = false;
            } else {
                if ($char == "\\") {
                    $escaped = true;
                } else {
                    if ($char == ']' && $this->input->peek() == ']') { // we reached the end
                        break;
                    } else {
                        $str .= $char;
                    }
                }
            }
        }
        return new Token(Token::TYPE_STRING, $str);
    }

    /**
     * @param string $end
     *
     * @return string
     */
    protected function readEscaped($end) {
        $escaped = false;
        $str     = "";
        $this->input->next();
        while (!$this->input->eof()) {
            $char = $this->input->next();
            if ($escaped) {
                $str .= $char;
                $escaped = false;
            } else {
                if ($char == "\\") {
                    $escaped = true;
                } else {
                    if ($char == $end) {
                        break;
                    } else {
                        $str .= $char;
                    }
                }
            }
        }
        return $str;
    }

    /**
     * @return Token
     */
    protected function readNumber() {
        $hasDot = false;
        $number = $this->readWhile(
            function ($char) use (&$hasDot) {
                if ($char == '.') {
                    if ($hasDot) {
                        return false;
                    }
                    $hasDot = true;
                    return true;
                }
                return $this->isDigit($char);
            }
        );
        return new Token(Token::TYPE_NUMBER, $hasDot ? floatval($number) : intval($number));
    }

    /**
     * @return Token
     */
    protected function readIdentifier() {
        $first      = false;
        $identifier = $this->readWhile(
            function ($char) use (&$first) {
                if ($first) {
                    $first = false;
                    return $this->isStartIdentifierCharacter($char);
                }
                return $this->isIdentifierCharacter($char);
            }
        );
        if ($this->isKeyword($identifier)) {
            return new Token(Token::TYPE_KEYWORD, $identifier);
        }
        return new Token(Token::TYPE_IDENTIFIER, $identifier);
    }

    /**
     * @return Token
     */
    protected function readPunctuation() {
        return new Token(Token::TYPE_PUNCTUATION, $this->input->next());
    }

    /**
     * @param callable $predicate
     *
     * @return string
     */
    protected function readWhile(callable $predicate) {
        $str = "";
        while (!$this->input->eof() && call_user_func($predicate, $this->input->peek())) {
            $str .= $this->input->next();
        }
        return $str;
    }

    /**
     * @param string $char
     *
     * @return bool
     */
    protected function isWhitespace($char) {
        return strpos(" \t\n\r", $char) !== false;
    }

    /**
     * @param string $char
     *
     * @return bool
     */
    protected function isDigit($char) {
        return is_numeric($char);
    }

    /**
     * @return bool
     */
    protected function isDoubleBracketString() {
        return $this->input->peek() == '[' && !$this->input->eof(1) && $this->input->peek(1) == '[';
    }

    /**
     * @param string $char
     *
     * @return bool
     */
    protected function isStartIdentifierCharacter($char) {
        return preg_match('/[a-zA-Z_]/', $char) === 1;
    }

    /**
     * @param string $char
     *
     * @return bool
     */
    protected function isIdentifierCharacter($char) {
        return preg_match('/[a-zA-Z0-9_]/', $char) === 1;
    }

    /**
     * @param string $char
     *
     * @return bool
     */
    protected function isPunctuation($char) {
        return strpos(",{}=[]", $char) !== false;
    }

    /**
     * @param string $text
     *
     * @return bool
     */
    protected function isKeyword($text) {
        return in_array($text, Lua::$luaKeywords);
    }
}