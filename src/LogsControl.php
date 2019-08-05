<?php declare(strict_types = 1);

namespace Netleak\Logs;

use Carbon\Carbon;
use DateTime;
use Nette\Application\Responses\FileResponse;
use Nette\Application\UI\Control;
use Nette\ComponentModel\IContainer;
use Nette\Utils\FileSystem;
use ZipArchive;

class LogsControl extends Control {

	public const RETURN_COUNT = 1;

	public const RETURN_DATA = 2;

	/**
	 * @var string|null
	 */
	private $rootPath;

	/**
	 * @var string|null
	 */
	private $logPath;

	/**
	 * @var string|null
	 */
	private $tempPath;

	/**
	 * @var string|null
	 */
	private $publicPath = '/';

	/**
	 * @var array<string, true>
	 */
	public $useLogs = [];

	public function __construct(string $rootPath, ?string $publicPath = null, ?IContainer $parent = null, ?string $name = null) {
		parent::__construct();

		if ($parent !== null) {
			$parent->addComponent($this, $name);
		}

		if ($publicPath !== null) {
			$this->publicPath = $publicPath;
		}

		$this->rootPath = $rootPath;
		$this->logPath = $rootPath . '/log';
		$this->tempPath = $rootPath . '/temp';
	}

	public function render(): void {
		$template = $this->getTemplate();

		$template->publicPath = $this->publicPath;
		$template->types = json_encode($this->useLogs);
		$template->logs = json_encode($this->readLogs());
		$template->setFile(__DIR__ . '/templates/logs.latte');
		$template->render();
	}

	/**
	 * @return array<int, array<string, string|null>>
	 */
	public function readLogs(): array {
		$info = $this->logPath . '/info.log';
		$debug = $this->logPath . '/debug.log';
		$exception = $this->logPath . '/exception.log';
		$error = $this->logPath . '/error.log';
		$terminal = $this->logPath . '/terminal.log';
		$warning = $this->logPath . '/warning.log';
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

		if (file_exists($warning) && is_array(file($warning))) {
			$warnings = [];

			foreach (file($warning) as $warn) {
				$warnings[] = [
					'message' => $warn,
					'type' => 'terminal',
				];
			}

			$all = array_merge($all, $warnings);
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

		$allList = [];

		if (is_array($all)) {
			foreach ($all as $row) {
				preg_match('/(exception--[\S]+.html)/', $row['message'], $file);
				$dateTime = DateTime::createFromFormat('Y-m-d H-i-s', substr($row['message'], 1, 19));

				$allList[] = [
					'dateTime' => $dateTime ? $dateTime->format('d.m.Y H:i:s') : 'datum neexistuje!',
					'message' => substr($row['message'], 22),
					'file' => empty($file) || !file_exists($this->logPath . '/' . $file[0]) ? null : $this->logPath . '/' . $file[0],
					'fileContent' => empty($file) || !file_exists($this->logPath . '/' . $file[0]) ? null : FileSystem::read($this->logPath . '/' . $file[0]),
					'type' => $row['type'],
				];
			}
		}

		return $allList;
	}

	public function handleExportLogs(): void {
		$logDirectory = $this->logPath . '/';
		$zipName = 'logs_' . Carbon::now()->format('d-m-Y_h-i-s') . '.zip';
		$zipPath = $this->tempPath . '/cache/' . $zipName;
		$logs = scandir($logDirectory, SCANDIR_SORT_NONE);

		if (is_array($logs)) {
			unset($logs[0], $logs[1]);
		}

		if (is_array($logs)) {
			$zip = new ZipArchive();
			$zip->open($zipPath, ZipArchive::CREATE);

			foreach ($logs as $log) {
				$zip->addFile($logDirectory . $log, $log);
			}

			$zip->close();
		}

		$this->getPresenter()->sendResponse(new FileResponse($zipPath, $zipName));
	}

	public function handleDeleteLogs(): void {
		$check = shell_exec('echo $SHELL');
		$delLogs = [
			'win' => 'del /q log\*',
			'unix' => 'rm -rf log/*',
		];

		$delLogs = $check === "\$SHELL\n"
			? $delLogs['win']
			: $delLogs['unix'];

		if ($this->rootPath !== null) {
			chdir($this->rootPath);

			exec($delLogs . ' 2>&1', $output);

			$template = $this->getTemplate();
			$template->logs = json_encode($this->readLogs());
		}

		$this->redirect('this');
	}

}
