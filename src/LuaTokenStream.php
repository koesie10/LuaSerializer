<?php
/**
 * LuaTokenStream.php
 *
 * @author Koen Vlaswinkel <koen@vlaswinkel.info>
 * @since  20/12/2015 18:45
 */

namespace Vlaswinkel\Lua;

/**
 * Class LuaTokenStream
 *
 * @see     http://lisperator.net/pltut/parser/token-stream
 *
 * @package Vlaswinkel\Lua
 */
class LuaTokenStream {
    private $current = null;
    /**
     * @var LuaInputStream
     */
    private $input;

    /**
     * LuaTokenStream constructor.
     *
     * @param LuaInputStream $input
     */
    public function __construct(LuaInputStream $input) {
        $this->input = $input;
    }

    /**
     * @return LuaToken
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
     * @return LuaToken
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
     * @throws LuaParseException
     */
    public function error($msg) {
        $this->input->error($msg);
    }

    /**
     * @return LuaToken
     * @throws LuaParseException
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
     * @return LuaToken
     */
    protected function readDoubleQuotedString() {
        return new LuaToken(LuaToken::TYPE_STRING, $this->readEscaped('"'));
    }

    /**
     * @return LuaToken
     */
    protected function readSingleQuotedString() {
        return new LuaToken(LuaToken::TYPE_STRING, $this->readEscaped('\''));
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
     * @return LuaToken
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
        return new LuaToken(LuaToken::TYPE_NUMBER, $hasDot ? floatval($number) : intval($number));
    }

    /**
     * @return LuaToken
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
            return new LuaToken(LuaToken::TYPE_KEYWORD, $identifier);
        }
        return new LuaToken(LuaToken::TYPE_IDENTIFIER, $identifier);
    }

    /**
     * @return LuaToken
     */
    protected function readPunctuation() {
        return new LuaToken(LuaToken::TYPE_PUNCTUATION, $this->input->next());
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