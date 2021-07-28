<?php declare(strict_types = 1);

namespace Netleak\Logs;

use Cake\Chronos\Chronos;
use DateTime;
use Nette\Application\Responses\FileResponse;
use Nette\Application\UI\Control;
use Nette\ComponentModel\IContainer;
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
	 * @var array<string>
	 */
	public $types = [];

	/**
	 * @var array<string>
	 */
	public $disabled = [];

	public function __construct(string $rootPath, ?string $publicPath = null, ?IContainer $parent = null, ?string $name = null) {
		parent::__construct();

		if ($parent !== null) {
			$parent->addComponent($this, $name);
		}

		if ($publicPath !== null) {
			$this->publicPath = $publicPath;
		}

		$this->rootPath = $rootPath;
		$this->logPath = $rootPath . '/log/';
		$this->tempPath = $rootPath . '/temp/';
	}

	public function render(): void {
		$template = $this->getTemplate();
		$this->getTypes();

		$template->publicPath = $this->publicPath;
		$template->types = json_encode($this->types);
		$template->logs = json_encode($this->readLogs());
		$template->setFile(__DIR__ . '/templates/logs.latte');
		$template->render();
	}

	public function disableLogs(string ...$types): void {
		$this->disabled = $types;
	}

	private function getTypes(): void {
		$logFiles = glob($this->logPath . '*.log');

		if (!is_array($logFiles)) {
			return;
		}

		foreach ($logFiles as $logFile) {
			$type = (string) pathinfo($logFile, PATHINFO_FILENAME);

			if (in_array($type, $this->disabled)) {
				continue;
			}

			$this->types[] = $type;
		}
	}

	/**
	 * @return array<int, array<string, string|null>>
	 */
	public function readLogs(): array {
		$all = [];
		$logFiles = glob($this->logPath . '*.log');

		if (is_array($logFiles)) {
			foreach ($logFiles as $logFile) {
				$file = file($logFile);

				if (!is_array($file)) {
					continue;
				}

				$rows = [];
				$type = (string) pathinfo($logFile, PATHINFO_FILENAME);

				if (in_array($type, $this->disabled)) {
					continue;
				}

				$this->types[] = $type;

				foreach ($file as $row) {
					$rows[] = [
						'message' => trim($row),
						'type' => $type,
					];
				}

				$all = array_merge($all, $rows);
			}
		}

		$allList = [];

		foreach (array_reverse($all) as $row) {
			preg_match('/(exception--[\S]+.html)/', $row['message'], $file);
			$dateTime = DateTime::createFromFormat('Y-m-d H-i-s', substr($row['message'], 1, 19));

			$fileLink = !empty($file) || file_exists($this->logPath . $file[0])
				? $this->link('showLogHtml!', ['fileName' => $file[0]]) : null;

			$allList[] = [
				'dateTime' => $dateTime ? $dateTime->format('d.m.Y H:i:s') : 'datum neexistuje!',
				'message' => $dateTime ? substr($row['message'], 22) : $row['message'],
				'file' => $fileLink,
				'type' => $row['type'],
			];
		}

		return $allList;
	}

	public function handleShowLogHtml(string $fileName): void {
		$filePath = $this->logPath . $fileName;

		if (!file_exists($filePath)) {
			return;
		}

		$this->getPresenter()->sendResponse(new FileResponse($filePath));
	}

	public function handleExportLogs(): void {
		$logDirectory = $this->logPath;
		$zipName = 'logs_' . Chronos::now()->format('d-m-Y_h-i-s') . '.zip';
		$zipPath = $this->tempPath . 'cache/' . $zipName;
		$logs = glob($logDirectory . '/*');

		if (is_array($logs)) {
			$zip = new ZipArchive();
			$zip->open($zipPath, ZipArchive::CREATE);

			foreach ($logs as $log) {
				if (!is_file($log)) {
					continue;
				}

				$zip->addFile($log, $log);
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
