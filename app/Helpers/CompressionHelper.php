<?php

namespace App\Helpers;

class CompressionHelper
{
    public static function compressFile($source)
    {
        $command = "gzip {$source}";
        exec($command);
    }

    public static function uncompressFile($source)
    {
        $command = "gunzip {$source}";
        exec($command);
    }
}
