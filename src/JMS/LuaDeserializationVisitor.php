<?php

namespace Vlaswinkel\Lua\JMS;

use JMS\Serializer\GenericDeserializationVisitor;
use Vlaswinkel\Lua\Lua;

/**
 * Class LuaDeserializationVisitor
 *
 * @see     https://github.com/schmittjoh/serializer/blob/1.1.0/src/JMS/Serializer/JsonDeserializationVisitor.php
 *
 * @author  Johannes M. Schmitt <schmittjoh@gmail.com>
 * @author  Koen Vlaswinkel <koen@vlaswinkel.info>
 * @package Vlaswinkel\Lua\JMS
 */
class LuaDeserializationVisitor extends GenericDeserializationVisitor {
    protected function decode($str) {
        return Lua::deserialize($str);
    }
}