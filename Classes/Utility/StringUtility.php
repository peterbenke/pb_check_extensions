<?php

namespace PeterBenke\PbCheckExtensions\Utility;

class StringUtility
{

    /**
     * Explodes a string by given delimiter and trims the single elements
     * @param $delimiter
     * @param $string
     * @return array
     * @author Peter Benke <info@typomotor.de>
     */
    public static function explodeAndTrim($delimiter, $string): array
    {
        return array_map('trim', explode($delimiter, $string));
    }

}