<?php
/**
 * LuaArrayCollectionHandler.php
 *
 * @author Koen Vlaswinkel <koen@vlaswinkel.info>
 * @since  20/12/2015 21:17
 */

namespace Vlaswinkel\Lua\JMS;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use JMS\Serializer\Context;
use JMS\Serializer\GraphNavigator;
use JMS\Serializer\Handler\SubscribingHandlerInterface;
use JMS\Serializer\VisitorInterface;

/**
 * Class LuaArrayCollectionHandler
 *
 * @package Vlaswinkel\JMS\Lua
 */
class LuaArrayCollectionHandler implements SubscribingHandlerInterface {
    public static function getSubscribingMethods() {
        $methods         = [];
        $collectionTypes = [
            'ArrayCollection',
            'Doctrine\Common\Collections\ArrayCollection',
            'Doctrine\ORM\PersistentCollection',
            'Doctrine\ODM\MongoDB\PersistentCollection',
            'Doctrine\ODM\PHPCR\PersistentCollection',
        ];
        foreach ($collectionTypes as $type) {
            $methods[] = [
                'direction' => GraphNavigator::DIRECTION_SERIALIZATION,
                'type'      => $type,
                'format'    => 'lua',
                'method'    => 'serializeCollection',
            ];
            $methods[] = [
                'direction' => GraphNavigator::DIRECTION_DESERIALIZATION,
                'type'      => $type,
                'format'    => 'lua',
                'method'    => 'deserializeCollection',
            ];
        }
        return $methods;
    }

    public function serializeCollection(
        VisitorInterface $visitor,
        Collection $collection,
        array $type,
        Context $context
    ) {
        // We change the base type, and pass through possible parameters.
        $type['name'] = 'array';
        return $visitor->visitArray($collection->toArray(), $type, $context);
    }

    public function deserializeCollection(VisitorInterface $visitor, $data, array $type, Context $context) {
        // See above.
        $type['name'] = 'array';
        return new ArrayCollection($visitor->visitArray($data, $type, $context));
    }
}