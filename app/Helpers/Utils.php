<?php

namespace App\Helpers;

class Utils
{
    public static function randomChars($length)
    {
        $chars = str_split('01234567890abcdefghijklmnopqrstuvwxyz01234567890ABCDEFGHIJKLMNOPQRSTUVWXYZ01234567890');
        $randomChars = '';
        for ($x = 0; $x < $length; $x++) {
            $randomChars .= $chars[rand(0, count($chars) - 1)];
        }
        return $randomChars;
    }

    public static function logFilePath()
    {
        return app_path('../storage') . '/log.txt';
    }

    public static function logToFile($data)
    {
        $logFilePath = self::logFilePath();
        $fp = fopen($logFilePath, 'a');
        fwrite($fp, $data . "\n");
    }

    public static function getLinkExt($link)
    {
        $dot = strrpos($link, '.');
        if ($dot === false) {
            return false;
        }
        $ext = substr($link, $dot + 1);
        $q = strpos($ext, '?');
        if ($q === false) {
            return $ext;
        }
        $ext = substr($ext, 0, $q);
        return $ext;
    }
}
