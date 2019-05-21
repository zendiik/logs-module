<?php declare(strict_types = 1);

namespace Netleak\Logs;

use DateTime;
use Nette\Application\UI\Control;
use Nette\ComponentModel\IContainer;
use Nette\Utils\FileSystem;

class LogsControl extends Control {

	public const RETURN_COUNT = 1;

	public const RETURN_DATA = 2;

	/**
	 * @var string|null
	 */
	private $logPath;

	/**
	 * @var string|null
	 */
	private $tempPath;

	/**
	 * @var array<string>
	 */
	public $useLogs = [];

	public function __construct(string $rootPath, IContainer $parent = null, $name = null) {
		parent::__construct();

		if ($parent !== null) {
			$parent->addComponent($this, $name);
		}

		$this->logPath = $rootPath . '/log';
		$this->tempPath = $rootPath . '/temp';
	}

	public function render(): void {
		$template = $this->getTemplate();

		$template->types = json_encode($this->useLogs);
		$template->logs = json_encode($this->readLogs());
		$template->setFile(__DIR__ . '/templates/logs.latte');
		$template->render();
	}

	/**
	 * @return array<array<array<string>>|int>
	 */
	public function readLogs(): array {
		$info = $this->logPath . '/info.log';
		$debug = $this->logPath . '/debug.log';
		$exception = $this->logPath . '/exception.log';
		$error = $this->logPath . '/error.log';
		$terminal = $this->logPath . '/terminal.log';
		$all = [];

		if (file_exists($info) && is_array(file($info))) {
			$infos = [];

			foreach (file($info) as $inf) {
				$infos[] = [
					'message' => $inf,
					'type' => 'info',
				];
			}

			$all = array_merge($all, $infos);
		}

		if (file_exists($debug) && is_array(file($debug))) {
			$debugs = [];

			foreach (file($debug) as $debu) {
				$debugs[] = [
					'message' => $debu,
					'type' => 'debug',
				];
			}

			$all = array_merge($all, $debugs);
		}

		if (file_exists($exception) && is_array(file($exception))) {
			$exceptions = [];

			foreach (file($exception) as $exceptio) {
				$exceptions[] = [
					'message' => $exceptio,
					'type' => 'exception',
				];
			}

			$all = array_merge($all, $exceptions);
		}

		if (file_exists($terminal) && is_array(file($terminal))) {
			$terminals = [];

			foreach (file($terminal) as $term) {
				$terminals[] = [
					'message' => $term,
					'type' => 'terminal',
				];
			}

			$all = array_merge($all, $terminals);
		}

		if (file_exists($error) && is_array(file($error))) {
			$errors = [];

			foreach (file($error) as $erro) {
				// $re404 = '/PHP User Warning: Invalid link: No route for/';
				// $re403 = '/Forbidden access:|Access denied:/';

				$errors[] = [
					'message' => $erro,
					'type' => 'error',
				];
			}

			$all = array_merge($all, $errors);
		}

		rsort($all);
		$allList = [];

		if (is_array($all)) {
			foreach ($all as $row) {
				preg_match('/(exception--[\S]+.html)/', $row['message'], $file);

				$allList[] = [
					'dateTime' => DateTime::createFromFormat('Y-m-d H-i-s', substr($row['message'], 1, 19))->format("<b>d.m.Y</b> <b\\r> H:i:s"),
					'message' => substr($row['message'], 22),
					'file' => empty($file) ? null : $this->logPath . '/' . $file[0],
					'fileContent' => empty($file) ? null : FileSystem::read($this->logPath . '/' . $file[0]),
					'type' => $row['type'],
				];
			}
		}

		return $allList;
	}

}