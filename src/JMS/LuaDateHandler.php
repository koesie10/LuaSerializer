<?php
/**
 * LuaDateHandler.php
 *
 * @author Koen Vlaswinkel <koen@vlaswinkel.info>
 * @since  21/12/2015 11:18
 */

namespace Vlaswinkel\Lua\JMS;

use JMS\Serializer\Context;
use JMS\Serializer\Exception\RuntimeException;
use JMS\Serializer\GraphNavigator;
use JMS\Serializer\Handler\SubscribingHandlerInterface;
use JMS\Serializer\VisitorInterface;

/**
 * Class LuaDateHandler
 *
 * @package Vlaswinkel\Lua\JMS
 */
class LuaDateHandler implements SubscribingHandlerInterface {
    private $defaultFormat;
    private $defaultTimezone;

    public static function getSubscribingMethods() {
        $methods   = [];
        $types     = ['DateTime', 'DateInterval'];
        $methods[] = [
            'type'      => 'DateTime',
            'direction' => GraphNavigator::DIRECTION_DESERIALIZATION,
            'format'    => 'lua',
        ];
        foreach ($types as $type) {
            $methods[] = [
                'type'      => $type,
                'format'    => 'lua',
                'direction' => GraphNavigator::DIRECTION_SERIALIZATION,
                'method'    => 'serialize' . $type,
            ];
        }
        return $methods;
    }

    public function __construct($defaultFormat = \DateTime::ISO8601, $defaultTimezone = 'UTC') {
        $this->defaultFormat   = $defaultFormat;
        $this->defaultTimezone = new \DateTimeZone($defaultTimezone);
    }

    public function serializeDateTime(VisitorInterface $visitor, \DateTime $date, array $type, Context $context) {
        return $visitor->visitString($date->format($this->getFormat($type)), $type, $context);
    }

    public function serializeDateInterval(
        VisitorInterface $visitor,
        \DateInterval $date,
        array $type,
        Context $context
    ) {
        $iso8601DateIntervalString = $this->format($date);
        return $visitor->visitString($iso8601DateIntervalString, $type, $context);
    }

    public function deserializeDateTimeFromLua(LuaDeserializationVisitor $visitor, $data, array $type) {
        if (null === $data) {
            return null;
        }
        return $this->parseDateTime($data, $type);
    }

    private function parseDateTime($data, array $type) {
        $timezone = isset($type['params'][1]) ? new \DateTimeZone($type['params'][1]) : $this->defaultTimezone;
        $format   = $this->getFormat($type);
        $datetime = \DateTime::createFromFormat($format, (string)$data, $timezone);
        if (false === $datetime) {
            throw new RuntimeException(sprintf('Invalid datetime "%s", expected format %s.', $data, $format));
        }
        return $datetime;
    }

    /**
     * @return string
     *
     * @param array $type
     */
    private function getFormat(array $type) {
        return isset($type['params'][0]) ? $type['params'][0] : $this->defaultFormat;
    }

    /**
     * @param \DateInterval $dateInterval
     *
     * @return string
     */
    public function format(\DateInterval $dateInterval) {
        $format = 'P';
        if (0 < $dateInterval->y) {
            $format .= $dateInterval->y . 'Y';
        }
        if (0 < $dateInterval->m) {
            $format .= $dateInterval->m . 'M';
        }
        if (0 < $dateInterval->d) {
            $format .= $dateInterval->d . 'D';
        }
        if (0 < $dateInterval->h || 0 < $dateInterval->i || 0 < $dateInterval->s) {
            $format .= 'T';
        }
        if (0 < $dateInterval->h) {
            $format .= $dateInterval->h . 'H';
        }
        if (0 < $dateInterval->i) {
            $format .= $dateInterval->i . 'M';
        }
        if (0 < $dateInterval->s) {
            $format .= $dateInterval->s . 'S';
        }
        return $format;
    }
}