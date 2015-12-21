<?php
/**
 * LuaConstraintViolationHandler.php
 *
 * @author Koen Vlaswinkel <koen@vlaswinkel.info>
 * @since  21/12/2015 11:24
 */

namespace Vlaswinkel\Lua\JMS;

use JMS\Serializer\Context;
use JMS\Serializer\GraphNavigator;
use JMS\Serializer\Handler\SubscribingHandlerInterface;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;

/**
 * Class LuaConstraintViolationHandler
 *
 * @package Vlaswinkel\Lua\JMS
 */
class LuaConstraintViolationHandler implements SubscribingHandlerInterface {
    public static function getSubscribingMethods() {
        $methods = [];
        $types   = [
            'Symfony\Component\Validator\ConstraintViolationList' => 'serializeList',
            'Symfony\Component\Validator\ConstraintViolation'     => 'serializeViolation',
        ];
        foreach ($types as $type => $method) {
            $methods[] = [
                'direction' => GraphNavigator::DIRECTION_SERIALIZATION,
                'type'      => $type,
                'format'    => 'lua',
                'method'    => $method,
            ];
        }
        return $methods;
    }

    public function serializeList(
        LuaSerializationVisitor $visitor,
        ConstraintViolationList $list,
        array $type,
        Context $context
    ) {
        return $visitor->visitArray(iterator_to_array($list), $type, $context);
    }

    public function serializeViolation(
        LuaSerializationVisitor $visitor,
        ConstraintViolation $violation,
        array $type = null
    ) {
        $data = [
            'property_path' => $violation->getPropertyPath(),
            'message'       => $violation->getMessage(),
        ];
        if (null === $visitor->getRoot()) {
            $visitor->setRoot($data);
        }
        return $data;
    }
}