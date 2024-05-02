<?php declare(strict_types = 1);

namespace Netleak\Logs;

ini_set('memory_limit', '1G');

use Cake\Chronos\Chronos;
use DateTime;
use JsonException;
use Nette\Application\Responses\FileResponse;
use Nette\Application\UI\Control;
use Nette\Application\UI\InvalidLinkException;
use Nette\ComponentModel\IContainer;
use ZipArchive;

final class LogsControl extends Control {

	public const RETURN_COUNT = 1;

	public const RETURN_DATA = 2;

	/** @var array<string> */
	public array $types = [];

	/** @var array<string> */
	public array $disabled = [];

	private ?string $rootPath; // @phpcs:ignore

	private ?string $logPath;

	private ?string $tempPath;

	private ?string $publicPath = '/';

	/** @var array<string>|false */
	private array|false $logFiles;

	public function __construct(
		string $rootPath,
		?string $publicPath = null,
		?IContainer $parent = null,
		?string $name = null,
	) {
		$parent?->addComponent($this, $name);

		if ($publicPath !== null) {
			$this->publicPath = $publicPath;
		}

		$this->rootPath = rtrim($rootPath, '/');
		$this->logPath = $this->rootPath . '/log/';
		$this->tempPath = $this->rootPath . '/temp/';
		$this->logFiles = glob($this->logPath . '*.log');
	}

	/**
	 * @throws JsonException
	 * @throws InvalidLinkException
	 */
	public function render(): void {
		$template = $this->getTemplate();
		$this->getTypes();

		$template->setParameters([
			'publicPath' => $this->publicPath,
			'types' => json_encode($this->types, JSON_THROW_ON_ERROR),
			'logs' => json_encode($this->readLogs(), JSON_THROW_ON_ERROR),
		]);
		$template->setFile(__DIR__ . '/templates/logs.latte');
		$template->render();
	}

	public function disableLogs(string ...$types): void {
		$this->disabled = $types;
	}

	/**
	 * @return array<int, array<string, string|null>>
	 * @throws InvalidLinkException
	 */
	public function readLogs(): array {
		$all = [];

		if (is_array($this->logFiles)) {
			foreach (array_slice($this->logFiles, -10000, 10000) as $logFile) {
				$file = file($logFile);

				if (!is_array($file)) {
					continue;
				}

				$rows = [];
				$type = (string) pathinfo($logFile, PATHINFO_FILENAME);

				if (in_array($type, $this->disabled, true)) {
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
			preg_match('/(' . $row['type'] . '--\S+.html)/', $row['message'], $file);
			$dateTime = DateTime::createFromFormat('Y-m-d H-i-s', substr($row['message'], 1, 19));

			$fileLink = !empty($file) && (isset($file[0]) && file_exists($this->logPath . $file[0]))
				? $this->link('showLogHtml!', ['fileName' => $file[0]])
				: null;

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
		$fileName = str_replace(['../', '..\\'], '', $fileName);

		$filePath = $this->logPath . basename($fileName);

		if (!preg_match('/--\S+.html/', $fileName)) {
			$filePath = $this->tempPath . 'cache/error.html';
		}

		if (!file_exists($filePath)) {
			$filePath = $this->tempPath . 'cache/error.html';
			$handle = fopen($filePath, 'wb');
			assert(is_resource($handle));

			fwrite($handle, 'File not found!');
			fclose($handle);
		}

		$this->getPresenter()->sendResponse(new FileResponse($filePath));
	}

	public function handleExport(): void {
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

				$zip->addFile($log, pathinfo($log, PATHINFO_BASENAME));
			}

			$zip->close();
		}

		$this->getPresenter()->sendResponse(new FileResponse($zipPath, $zipName));
	}

	public function handleDelete(): void {
		$files = glob($this->logPath . '*');

		if (is_array($files)) {
			foreach ($files as $file) {
				if (!is_file($file)) {
					continue;
				}

				unlink($file);
			}
		}

		$this->logFiles = glob($this->logPath . '*.log');
		$template = $this->getTemplate();
		$template->logs = json_encode($this->readLogs());

		$this->redirect('this');
	}

	private function getTypes(): void {
		$logFiles = glob($this->logPath . '*.log');

		if (!is_array($logFiles)) {
			return;
		}

		foreach ($logFiles as $logFile) {
			$type = (string) pathinfo($logFile, PATHINFO_FILENAME);

			if (in_array($type, $this->disabled, true)) {
				continue;
			}

			$this->types[] = $type;
		}
	}

}
