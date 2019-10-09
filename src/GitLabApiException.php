<?php

declare(strict_types=1);

namespace Baraja\GitLabApi;


class GitLabApiException extends \Exception
{

	public const ERROR_ERROR = 'error';
	public const ERROR_INVALID_TOKEN = 'invalid_token';

	/**
	 * error: "invalid_token"
	 * error_description: "Token was revoked. You have to re-authorize from the user."
	 *
	 * @var string[]
	 */
	private $errorConfigs;

	/**
	 * @param string $message
	 * @param string[] $errorConfigs
	 * @throws GitLabApiException
	 */
	public function __construct(string $message = '', array $errorConfigs = [])
	{
		parent::__construct($message, 403, null);
		foreach ($errorConfigs as $errorConfig) {
			if (preg_match('/^\"?(?<key>\w+?)\"?\:\s*\"?(?<value>.*?)\"?$/', $errorConfig, $errorConfigParser)) {
				$this->errorConfigs[$errorConfigParser['key']] = $errorConfigParser['value'];
			} else {
				throw new self('Invalid error config:' . "\n\n" . \json_encode($errorConfigs));
			}
		}
	}

	/**
	 * @param string $token
	 * @throws GitLabApiException
	 */
	public static function tokenIsInvalid(string $token): void
	{
		throw new self(
			'GitLab token "' . $token . '" is invalid.'
		);
	}

	/**
	 * @return string[]
	 */
	public function getErrorConfigs(): array
	{
		return $this->errorConfigs;
	}

	/**
	 * @return string
	 */
	public function getErrorType(): string
	{
		if ($this->isKey('error')) {
			return $this->getKey('error', self::ERROR_ERROR);
		}

		return self::ERROR_ERROR;
	}

	/**
	 * @return bool
	 */
	public function isDefaultError(): bool
	{
		return $this->getErrorType() === self::ERROR_ERROR;
	}

	/**
	 * @param string $key
	 * @return bool
	 */
	public function isKey(string $key): bool
	{
		return isset($this->errorConfigs[$key]);
	}

	/**
	 * @param string $key
	 * @param string|null $default
	 * @return string|null
	 */
	public function getKey(string $key, ?string $default = null): ?string
	{
		if ($this->isKey($key)) {
			return $this->errorConfigs[$key];
		}

		return $default;
	}

}