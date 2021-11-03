<?php
namespace PeterBenke\PbCheckExtensions\Utility;

/**
 * Class StringUtility
 * @package PeterBenke\PbCheckExtensions\Utility
 * @author Peter Benke <info@typomotor.de>
 */
class StringUtility
{

	/**
	 * Explodes a string by given delimiter and trims the single elements
	 * @author Peter Benke <pbenke@allplan.com>
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