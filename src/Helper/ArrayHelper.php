<?php
/**
 * Created by PhpStorm.
 * User: pedromdev
 * Date: 14/08/20
 * Time: 22:41
 */

namespace App\Helper;


class ArrayHelper
{
    public static function find(array $array, $callback)
    {
        foreach ($array as $key => $item) {
            if ($callback($item, $key, $array)) {
                return $item;
            }
        }

        return false;
    }

    public static function equals(array $arr1, array $arr2)
    {
        if (count($arr1) != count($arr2)) {
            return false;
        }

        foreach ($arr1 as $key => $item) {
            if ($arr1[$key] != $arr2[$key]) {
                return false;
            }
        }

        return true;
    }
}
