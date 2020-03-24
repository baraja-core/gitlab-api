<?php

declare(strict_types=1);

namespace Baraja\GitLabApi;


final class Helper
{
	private const FORCE_ARRAY = 0b0001;


	/**
	 * @throws \Error
	 */
	public function __construct()
	{
		throw new \Error('Class ' . get_class($this) . ' is static and cannot be instantiated.');
	}


	/**
	 * Migrated from Nette/Tracy.
	 *
	 * @param string|null $name
	 * @return float
	 */
	public static function timer(?string $name = null): float
	{
		static $time = [];
		$now = microtime(true);
		$delta = isset($time[$name]) ? $now - $time[$name] : 0;
		$time[$name] = $now;

		return $delta;
	}


	/**
	 * Migrated from Nette/Utils.
	 * Decodes a JSON string. Accepts flag Json::FORCE_ARRAY.
	 *
	 * @param string $json
	 * @param int $flags
	 * @return mixed
	 * @throws GitLabApiException
	 */
	public static function decode(string $json, int $flags = 0)
	{
		$forceArray = (bool) ($flags & self::FORCE_ARRAY);
		$value = json_decode($json, $forceArray, 512, JSON_BIGINT_AS_STRING);

		if ($error = json_last_error()) {
			throw new GitLabApiException(json_last_error_msg() . "\nCode: " . $error);
		}

		return $value;
	}


	/**
	 * Return last PHP error (notice, warning..)
	 *
	 * Function will return last error in case of "display_errors=0" or "error_reporting=0" or "@ syntax" too.
	 *
	 * Use case:
	 *
	 * if (!@rename($src, $dst)) {
	 *    throw new Exception('Unable to move directory: ' . Helper::getLastErrorMessage());
	 * }
	 *
	 * @return string|null
	 */
	public static function getLastErrorMessage(): ?string
	{
		$return = null;
		static $pattern = '/\s*\[\<a[^>]+>[a-z0-9\.\-\_\(\)]+<\/a>\]\s*/i';

		$lastError = error_get_last();
		if ($lastError && isset($lastError['message'])) {
			$return = trim((string) preg_replace($pattern, ' ', (string) $lastError['message']));
		}

		return $return;
	}
}
