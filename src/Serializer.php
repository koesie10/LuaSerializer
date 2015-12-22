<?php

namespace Vlaswinkel\Lua;

/**
 * Class Serializer
 *
 * @see     https://github.com/Sorroko/cclite/blob/62677542ed63bd4db212f83da1357cb953e82ce3/src/lua/rom/apis/textutils
 *
 * @author  Koen Vlaswinkel <koen@vlaswinkel.info>
 * @package Vlaswinkel\Lua
 */
class Serializer {
    public static function encode($data, $indent = '') {
        if (is_null($data)) {
            return self::encodeNull();
        } else {
            if (is_array($data)) {
                return self::encodeArray($data, $indent);
            } else {
                if (is_object($data)) {
                    return self::encodeArray((array)$data, $indent);
                } else {
                    if (is_string($data)) {
                        return self::encodeString($data);
                    } else {
                        if (is_numeric($data)) {
                            return self::encodeNumber($data);
                        } else {
                            if (is_bool($data)) {
                                return self::encodeBoolean($data);
                            }
                        }
                    }
                }
            }
        }

        throw new \InvalidArgumentException("Cannot encode type " . get_class($data) . ": " . var_export($data, true));
    }

    private static function encodeArray(array $data, $indent) {
        if (count($data) === 0) {
            return '{}';
        }

        $result    = "{\n";
        $subIndent = $indent . '  ';
        $seen      = [];
        foreach ($data as $key => $value) {
            if (is_int($key)) {
                $seen[$key] = true;
                $result .= $subIndent . self::encode($value, $subIndent) . ",\n";
            }
        }

        foreach ($data as $key => $value) {
            if (!array_key_exists($key, $seen)) {
                if (is_string($key)
                    && !in_array($key, Lua::$luaKeywords)
                    && preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $key)
                ) {
                    $entry = $key . ' = ' . self::encode($value, $subIndent) . ",\n";
                } else {
                    $entry = '[ ' . self::encode($key, $subIndent) . ' ] = ' . self::encode($value, $subIndent) . ",\n";
                }
                $result = $result . $subIndent . $entry;
            }
        }
        $result = $result . $indent . '}';
        return $result;
    }

    /**
     * @param $data
     *
     * @see http://luaj.cvs.sourceforge.net/viewvc/luaj/luaj-vm/src/core/org/luaj/vm2/lib/StringLib.java?view=markup
     * @return string
     */
    private static function encodeString($data) {
        $data   = str_replace(["\n\r", "\r\n"], "\n", $data);
        $result = '"';
        for ($i = 0, $n = strlen($data); $i < $n; $i++) {
            $char = $data[$i];
            switch ($char) {
                case '"':
                case "\\":
                case "\n":
                    $result .= "\\" . $char;
                    break;
                default:
                    if (($char <= chr(0x1F) || $char == chr(0x7F)) && $char != chr(9)) {
                        $result .= "\\";
                        if ($i + 1 == $n || $data[$i + 1] < '0' || $data[$i + 1] > '9') {
                            $result .= $char;
                        } else {
                            $result .= '0';
                            $result .= chr(ord('0') + $char / 10);
                            $result .= chr(ord('0') + $char % 10);
                        }
                    } else {
                        $result .= $char;
                    }
            }
        }
        $result .= '"';
        return $result;
    }

    private static function encodeNumber($data) {
        return $data;
    }

    private static function encodeBoolean($data) {
        return $data ? 'true' : 'false';
    }

    private static function encodeNull() {
        return 'nil';
    }
}