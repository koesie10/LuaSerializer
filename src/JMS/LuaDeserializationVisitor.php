<?php
/**
 * LuaDeserializationVisitor.php
 *
 * @author Koen Vlaswinkel <koen@vlaswinkel.info>
 * @since  20/12/2015 20:03
 */

namespace Vlaswinkel\Lua\JMS;

use JMS\Serializer\GenericDeserializationVisitor;
use Vlaswinkel\Lua\LuaInputStream;
use Vlaswinkel\Lua\LuaParser;
use Vlaswinkel\Lua\LuaTokenStream;
use Vlaswinkel\Lua\LuaToPhpConverter;

/**
 * Class LuaDeserializationVisitor
 *
 * @package Vlaswinkel\Lua\JMS
 */
class LuaDeserializationVisitor extends GenericDeserializationVisitor {
    protected function decode($str) {
        $parser = new LuaParser(new LuaTokenStream(new LuaInputStream($str)));
        return LuaToPhpConverter::convertToPhpValue($parser->parse());
    }
}