<?php

declare(strict_types=1);

namespace Baraja\GitLabApi;


use Baraja\GitLabApi\Entity\ApiData;
use Nette\Caching\Cache;
use Nette\Caching\IStorage;
use Nette\Security\User;

final class GitLabApi
{

	/**
	 * @var string
	 */
	private $token;

	/**
	 * @var bool
	 */
	private $validateToken = false;

	/**
	 * @var Cache|null
	 */
	private $cache;

	/**
	 * @var string
	 */
	private $baseUrl = 'https://gitlab.com/api/v4/';

	/**
	 * For valid service you must set $token or Nette user profile.
	 * If user profile is not available API will use default $token.
	 *
	 * @param string $token
	 * @param User|null $user
	 */
	public function __construct(string $token, ?User $user = null)
	{
		// Nette User bridge
		if ($user !== null && $user->isLoggedIn() && \method_exists($identity = $user->getIdentity(), 'getGitLabToken')) {
			$token = $identity->getGitLabToken() ?? $token;
		}

		$this->token = $token;
	}

	/**
	 * @param string $baseUrl
	 */
	public function setBaseUrl(string $baseUrl): void
	{
		$this->baseUrl = rtrim($baseUrl, '/') . '/';
	}

	/**
	 * Use Nette Cache for storage API responses.
	 *
	 * @param IStorage $IStorage
	 */
	public function setCache(IStorage $IStorage): void
	{
		$this->cache = new Cache($IStorage, 'gitlab-api');
	}

	/**
	 * @param string $url
	 * @param string[]|null $data
	 * @param string $cache
	 * @param string|null $token
	 * @return ApiData|ApiData[]
	 * @throws GitLabApiException
	 */
	public function request(string $url, ?array $data = null, string $cache = '12 hours', ?string $token = null)
	{
		$token = $token ? : $this->token;
		if ($this->validateToken === false) {
			if ($url !== 'projects' && $this->validateToken($token) === false) {
				GitLabApiException::tokenIsInvalid($token);
			}
			$this->validateToken = true;
		}

		$requestHash = 'url' . md5($url);
		Helper::timer($requestHash);

		$hash = md5(json_encode([$url, $data, $token]));
		$body = $this->cache === null ? null : $this->cache->load($hash);

		if (!isset($_SERVER['REMOTE_ADDR'])) {
			echo "\e[0;32m" . '[GitLab | URL: ' . "\e[0m\e[0;33m" . $url . "\e[0m\e[0;32m"
				. ($data !== null ? ', DATA: ' . json_encode($data) : '')
				. ' | TOKEN: ' . json_encode($token)
				. ']' . "\e[0m\n";
		}

		if ($body !== null) {
			GitLabApiPanel::$baseUrl = $this->baseUrl;
			GitLabApiPanel::$calls[] = [
				'duration' => Helper::timer($requestHash) * 1000,
				'url' => $url,
				'isCache' => true,
				'data' => $data,
				'body' => $body,
			];

			return $body;
		}

		$fullUrl = rtrim($this->baseUrl, '/') . '/' . ltrim($url, '/');

		$curl = curl_init();
		curl_setopt_array($curl, [
			CURLOPT_CUSTOMREQUEST => 'GET',
			CURLOPT_RETURNTRANSFER => 1,
			CURLOPT_URL => $fullUrl,
			CURLOPT_HEADER => 1,
			CURLINFO_HEADER_OUT => true,
		]);

		curl_setopt($curl, CURLOPT_HTTPHEADER, [
			'PRIVATE-TOKEN: ' . $token,
		]);

		$resp = curl_exec($curl);
		$headerSize = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
		$rawData = substr($resp, $headerSize);

		$body = $this->mapToApiData(Helper::decode($rawData));

		if (isset($body['error'])) {
			$errorMessages = [];

			foreach ($body as $key => $value) {
				$errorMessages[] = trim($key) . ': ' . json_encode($value);
			}

			throw new GitLabApiException(implode("\n", $errorMessages), $errorMessages);
		}

		GitLabApiPanel::$baseUrl = $this->baseUrl;
		GitLabApiPanel::$calls[] = [
			'duration' => Helper::timer($requestHash) * 1000,
			'url' => $url,
			'isCache' => false,
			'data' => $data,
			'body' => $body,
		];

		curl_close($curl);

		if ($this->cache !== null) {
			$this->cache->save($hash, $body, [
				Cache::EXPIRE => $cache,
			]);
		}

		return $body;
	}

	/**
	 * @param string $url
	 * @param string[]|null $data
	 * @param string $method
	 * @param string|null $token
	 * @return ApiData|ApiData[]
	 * @throws GitLabApiException
	 */
	public function changeRequest(string $url, ?array $data = null, string $method = 'PUT', ?string $token = null)
	{
		$token = $token ? : $this->token;
		if ($this->validateToken === false) {
			if ($url !== 'projects' && $this->validateToken($token) === false) {
				GitLabApiException::tokenIsInvalid($token);
			}
			$this->validateToken = true;
		}

		$requestHash = 'url' . md5($url);
		Helper::timer($requestHash);

		if (!isset($_SERVER['REMOTE_ADDR'])) {
			echo "\e[0;32m" . '[GitLab | URL: ' . "\e[0m\e[0;33m" . $url . "\e[0m\e[0;32m"
				. ($data !== null ? ', DATA: ' . json_encode($data) : '')
				. ' | TOKEN: ' . json_encode($token)
				. ']' . "\e[0m\n";
		}

		$fullUrl = rtrim($this->baseUrl, '/') . '/' . ltrim($url, '/');

		$configRequest = [
			CURLOPT_CUSTOMREQUEST => $method,
			CURLOPT_RETURNTRANSFER => 1,
			CURLOPT_URL => $fullUrl,
			CURLOPT_HEADER => 1,
		];

		if ($data !== null) {
			$configRequest[CURLOPT_POSTFIELDS] = http_build_query($data);
		}

		$curl = curl_init();
		curl_setopt_array($curl, $configRequest);

		curl_setopt($curl, CURLOPT_HTTPHEADER, [
			'PRIVATE-TOKEN: ' . $token,
		]);

		$resp = curl_exec($curl);

		if ($resp === false) {
			GitLabApiPanel::$baseUrl = $this->baseUrl;
			GitLabApiPanel::$calls[] = [
				'duration' => Helper::timer($requestHash) * 1000,
				'method' => $method,
				'url' => $url,
				'data' => $data,
				'body' => $resp,
			];

			throw new GitLabApiException('[' . $url . ']: Curl return FALSE: ' . Helper::getLastErrorMessage());
		}

		$headerSize = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
		$rawData = substr((string) $resp, $headerSize);

		$body = $this->mapToApiData(Helper::decode($rawData));

		if (isset($body['error'])) {
			$errorMessages = [];

			foreach ($body as $key => $value) {
				$errorMessages[] = trim($key) . ': ' . json_encode($value);
			}

			GitLabApiPanel::$baseUrl = $this->baseUrl;
			GitLabApiPanel::$calls[] = [
				'duration' => Helper::timer($requestHash) * 1000,
				'method' => $method,
				'url' => $url,
				'data' => $data,
				'body' => $body,
			];

			throw new GitLabApiException('[' . $url . ']: ' . implode("\n", $errorMessages), $errorMessages);
		}

		GitLabApiPanel::$baseUrl = $this->baseUrl;
		GitLabApiPanel::$calls[] = [
			'duration' => Helper::timer($requestHash) * 1000,
			'method' => $method,
			'url' => $url,
			'data' => $data,
			'body' => $body,
		];

		curl_close($curl);

		return $body;
	}

	/**
	 * @param string $token
	 * @return bool
	 * @throws GitLabApiException
	 */
	public function validateToken(string $token): bool
	{
		$response = $this->request('projects', null, '1 hour', $token);

		return (\is_array($response) === false
				&& $response->offsetExists('message')
				&& $response->message === '401 Unauthorized'
			) === false;
	}

	/**
	 * @param \stdClass|\stdClass[] $haystack
	 * @return ApiData|ApiData[]|string|bool|int|float
	 */
	private function mapToApiData($haystack)
	{
		if (\is_array($haystack)) {
			$return = [];

			foreach ($haystack as $key => $value) {
				$return[$key] = $this->mapToApiData($value);
			}

			return $return;
		}

		if ($haystack instanceof \stdClass) {
			$return = new ApiData;

			foreach ($haystack as $key => $value) {
				$return->{$key} = $value;
			}

			return $return;
		}

		return $haystack;
	}

}
