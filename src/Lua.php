<?php
/**
 * Lua.php
 *
 * @author Koen Vlaswinkel <koen@vlaswinkel.info>
 * @since  20/12/2015 14:47
 */

namespace Vlaswinkel\Lua;

/**
 * Class Lua
 *
 * @package Vlaswinkel\Lua
 */
class Lua {
    public static $luaKeywords = [
        'and',
        'break',
        'do',
        'else',
        'elseif',
        'end',
        'false',
        'for',
        'function',
        'if',
        'in',
        'local',
        'nil',
        'not',
        'or',
        'repeat',
        'return',
        'then',
        'true',
        'until',
        'while',
    ];

    public static function serialize($data) {
        return LuaSerializer::encode($data);
    }

    public static function deserialize($data) {
        $parser = new LuaParser(new LuaTokenStream(new LuaInputStream($data)));
        return LuaToPhpConverter::convertToPhpValue($parser->parse());
    }
}