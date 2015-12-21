<?php
/**
 * LuaSerializationVisitor.php
 *
 * @author Koen Vlaswinkel <koen@vlaswinkel.info>
 * @since  20/12/2015 20:04
 */

namespace Vlaswinkel\Lua\JMS;

use JMS\Serializer\GenericSerializationVisitor;
use Vlaswinkel\Lua\Lua;

/**
 * Class LuaSerializationVisitor
 *
 * @package Vlaswinkel\Lua\JMS
 */
class LuaSerializationVisitor extends GenericSerializationVisitor {
    /**
     * @return object|array
     */
    public function getResult() {
        return Lua::encode($this->getRoot());
    }
}