<?php

namespace App\Helpers;

class ArrayHelpers
{
    /**
     * Takes keyed array and changes keys out for translated keys
     *
     * @param $arrayToTranslate - raw array
     * @param $keyTranslations - keyed array [[key=>newKey],]
     * @return array
     */
    public static function translateArrayKeys($arrayToTranslate, $keyTranslations)
    {
        foreach ($keyTranslations as $key => $newKey) {
            if (array_key_exists($key, $arrayToTranslate)) {
                $arrayToTranslate[$newKey] = $arrayToTranslate[$key];
                unset($arrayToTranslate[$key]);
            } else {
                $arrayToTranslate[$newKey] = null;
            }
        }

        return $arrayToTranslate;
    }

    /**
     * Takes an array list of keyed arrays and changes keys out for translated keys
     *
     * @param $listToTranslate - raw array
     * @param $keyTranslations - keyed array [[key=>newKey],]
     * @return array
     */
    public static function translateArrayListKeys($listToTranslate, $keyTranslations)
    {
        foreach ($listToTranslate as &$arrayToTranslate) {
            $arrayToTranslate = self::translateArrayKeys($arrayToTranslate, $keyTranslations);
        }
        return $listToTranslate;
    }

    public static function keysInArray($expectedKeys, $arrayToCheck, $atLeastOne = false)
    {
        $countKeys = count(array_intersect_key(array_flip($expectedKeys), $arrayToCheck));
        if ($atLeastOne) {
            return ($countKeys > 0);
        } else {
            return $countKeys == count($expectedKeys);
        }
    }

    /**
     * returns $array[$key] if exists
     *
     * @param $key
     * @param $array
     * @return mixed|null
     */
    public static function getArrayKeyIfExists($key, $array, $default = null)
    {
        if (is_array($array)) {
            if (array_key_exists($key, $array)) {
                return $array[$key];
            }
        }
        return $default;
    }

    /**
     * Checks to see if the value exists in an array, but confirms the haystack is an array first
     *
     * @param $value
     * @param $arr
     * @return bool
     */
    public static function valueExistsInArray($value, $arr)
    {
        if (is_array($arr)) {
            if (in_array($value, $arr)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param $newKey
     * @param $oldKey
     * @param array $array
     * @param array|null $options
     * @return array
     */
    public static function swapKeys($newKey, $oldKey, array $array, ?array $options = [])
    {
        if ($options == null) {
            $options = [];
        }
        $overwritePreExistingKey = !in_array('nooverwrite', $options);

        if (array_key_exists($oldKey, $array)) {

            if (!array_key_exists($newKey, $array) || $overwritePreExistingKey) {
                $array[$newKey] = $array[$oldKey];
            }
            unset($array[$oldKey]);
        } else {
            $array[$newKey] = null;
        }
        return $array;
    }

    /**
     * returns array values that are duplicated in the given array
     *
     * @param $array
     * @return array
     */
    public static function getDupes($array)
    {
        $dupes = array();
        foreach (array_count_values($array) as $val => $c) {
            if ($c > 1) {
                $dupes[] = $val;
            }
            return $dupes;
        }
    }
}
