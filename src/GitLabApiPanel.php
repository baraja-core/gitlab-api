<?php

declare(strict_types=1);

namespace Baraja\GitLabApi;


use Tracy\Dumper;
use Tracy\IBarPanel;

class GitLabApiPanel implements IBarPanel
{
	private const ICON = 'data:image/png;base64,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';

	private static string $baseUrl;

	/** @var mixed[][] */
	private static array $calls = [];


	/**
	 * @param mixed[] $data
	 */
	public static function addData(string $baseUrl, array $data): void
	{
		self::$baseUrl = $baseUrl;
		self::$calls[] = $data;
	}


	public function getTab(): string
	{
		$duration = 0.0;
		foreach (self::$calls as $call) {
			$duration += (float) $call['duration'];
		}

		return '<span title="GitLab calls">'
			. '<img style="height:16px" src="' . self::ICON . '" alt="GitLab panel" title="GitLab">'
			. \count(self::$calls) . ' requests'
			. ($duration > 0 ? ' / ' . number_format($duration, 2, '.', ' ') . ' ms' : '')
			. '</span>';
	}


	public function getPanel(): string
	{
		if (\count(self::$calls) > 0) {
			$table = '<table>';
			$table .= '<tr><th>Time&nbsp;ms</th><th>Url</th><th>Data</th><th>Body</th></tr>';
			foreach (self::$calls as $call) {
				$table .= '<tr>'
					. '<td>' . number_format($call['duration'], 2, '.', ' ') . '&nbsp;ms'
					. (($call['isCache'] ?? false) ? '<br><small style="color:green">cache</small>' : '')
					. '</td>'
					. '<td><span style="color:blue">' . ($call['method'] ?? 'GET') . '</span>&nbsp;' . $call['url'] . '</td>'
					. '<td>' . Dumper::toHtml($call['data']) . '</td>'
					. '<td>' . Dumper::toHtml($call['body']) . '</td>'
					. '</tr>';
			}

			$table .= '</table>';
		} else {
			$table = '<em>No GitLab requests.</em>';
		}

		return '<h1>GitLab requests' . (self::$baseUrl ? ' [' . self::$baseUrl . ']' : '') . '</h1>'
			. '<div class="tracy-inner">' . $table . '</div>';
	}
}
