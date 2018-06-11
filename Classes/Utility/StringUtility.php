<?php
namespace PeterBenke\PbCheckExtensions\Utility;

class StringUtility{
	/**
	 * Explodes a string by given delimiter and trims the single elements
	 * @author Peter Benke <pbenke@allplan.com>
	 * @param $delimiter
	 * @param $string
	 * @return array
	 */
	public static function explodeAndTrim($delimiter, $string){
		$array = array_map('trim', explode($delimiter, $string));
		return $array;
	}
}