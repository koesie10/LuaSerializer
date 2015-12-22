<?php

namespace Vlaswinkel\Lua\JMS;

use JMS\Serializer\GenericSerializationVisitor;
use Vlaswinkel\Lua\Lua;

/**
 * Class LuaSerializationVisitor
 *
 * @see     https://github.com/schmittjoh/serializer/blob/1.1.0/src/JMS/Serializer/JsonSerializationVisitor.php
 *
 * @author  Johannes M. Schmitt <schmittjoh@gmail.com>
 * @author  Koen Vlaswinkel <koen@vlaswinkel.info>
 * @package Vlaswinkel\Lua\JMS
 */
class LuaSerializationVisitor extends GenericSerializationVisitor {
    /**
     * @return object|array
     */
    public function getResult() {
        return Lua::serialize($this->getRoot());
    }
}