<?php

namespace Vlaswinkel\Lua\JMS;

use JMS\Serializer\Context;
use JMS\Serializer\GraphNavigator;
use JMS\Serializer\Handler\SubscribingHandlerInterface;
use JMS\Serializer\VisitorInterface;
use PhpCollection\Map;
use PhpCollection\Sequence;

/**
 * Class LuaPhpCollectionHandler
 *
 * @see     https://github.com/schmittjoh/serializer/blob/1.1.0/src/JMS/Serializer/Handler/PhpCollectionHandler.php
 *
 * @author  Johannes M. Schmitt <schmittjoh@gmail.com>
 * @author  Koen Vlaswinkel <koen@vlaswinkel.info>
 * @package Vlaswinkel\Lua\JMS
 */
class LuaPhpCollectionHandler implements SubscribingHandlerInterface {
    public static function getSubscribingMethods() {
        $methods         = [];
        $collectionTypes = [
            'PhpCollection\Sequence' => 'Sequence',
            'PhpCollection\Map'      => 'Map',
        ];
        foreach ($collectionTypes as $type => $shortName) {
            $methods[] = [
                'direction' => GraphNavigator::DIRECTION_SERIALIZATION,
                'type'      => $type,
                'format'    => 'lua',
                'method'    => 'serialize' . $shortName,
            ];
            $methods[] = [
                'direction' => GraphNavigator::DIRECTION_DESERIALIZATION,
                'type'      => $type,
                'format'    => 'lua',
                'method'    => 'deserialize' . $shortName,
            ];
        }
        return $methods;
    }

    public function serializeMap(VisitorInterface $visitor, Map $map, array $type, Context $context) {
        $type['name'] = 'array';
        return $visitor->visitArray(iterator_to_array($map), $type, $context);
    }

    public function deserializeMap(VisitorInterface $visitor, $data, array $type, Context $context) {
        $type['name'] = 'array';
        return new Map($visitor->visitArray($data, $type, $context));
    }

    public function serializeSequence(VisitorInterface $visitor, Sequence $sequence, array $type, Context $context) {
        // We change the base type, and pass through possible parameters.
        $type['name'] = 'array';
        return $visitor->visitArray($sequence->all(), $type, $context);
    }

    public function deserializeSequence(VisitorInterface $visitor, $data, array $type, Context $context) {
        // See above.
        $type['name'] = 'array';
        return new Sequence($visitor->visitArray($data, $type, $context));
    }
}