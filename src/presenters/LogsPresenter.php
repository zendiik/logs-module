<?php declare(strict_types = 1);

namespace Netleak\Logs\Presenters;

use Carbon\Carbon;
use DateTime;
use Nette\Application\Responses\FileResponse;
use Nette\Application\UI\Presenter;
use Nette\DI\Container;
use Nette\Utils\FileSystem;
use ZipArchive;

class LogsPresenter extends Presenter {

	public const NAME = 'Netleak:Logs';

	public const RETURN_COUNT = 1;

	public const RETURN_DATA = 2;

	public function beforeRender(): void {
		$notFound = $this->getSession()->getSection('logsFilter')->notFound ?? false;

		foreach (LogsPresenter::countLogs(LogsPresenter::RETURN_COUNT, null, $notFound) as $key => $countLog) {
			$this->template->$key = $countLog;
		}
	}

	public function actionDefault(int $page = 1): void {
		$notFound = $this->getSession()->getSection('logsFilter')->notFound ?? false;
		$accessDenied = $this->getSession()->getSection('logsFilter')->accessDenied ?? false;
		$infoHide = $this->getSession()->getSection('logsFilter')->infoHide ?? false;
		$debugHide = $this->getSession()->getSection('logsFilter')->debugHide ?? false;
		$exceptionHide = $this->getSession()->getSection('logsFilter')->exceptionHide ?? false;
		$errorHide = $this->getSession()->getSection('logsFilter')->errorHide ?? false;
		$terminalHide = $this->getSession()->getSection('logsFilter')->terminalHide ?? false;

		$this->template->notFound = $notFound ? 0 : 1;
		$this->template->accessDenied = $accessDenied ? 0 : 1;
		$this->template->infoHide = $infoHide ? 0 : 1;
		$this->template->debugHide = $debugHide ? 0 : 1;
		$this->template->exceptionHide = $exceptionHide ? 0 : 1;
		$this->template->errorHide = $errorHide ? 0 : 1;
		$this->template->terminalHide = $terminalHide ? 0 : 1;

		$all = self::countLogs(
			self::RETURN_DATA,
			$page,
			$notFound,
			$accessDenied,
			$infoHide,
			$debugHide,
			$exceptionHide,
			$errorHide,
			$terminalHide
		);

		$allList = [];

		if (is_array($all['all'])) {
			foreach ($all['all'] as $row) {
				preg_match('/(exception--[\S]+.html)/', $row['message'], $file);

				$allList[] = [
					'dateTime' => DateTime::createFromFormat('Y-m-d H-i-s', substr($row['message'], 1, 19))->format('<b>d.m.Y</b> <br> H:i:s'),
					'message' => substr($row['message'], 22),
					'file' => empty($file) ? null : __DIR__ . '/../../../../../' . $file[0],
					'fileContent' => empty($file) ? null : FileSystem::read(__DIR__ . '/../../../../../log/' . $file[0]),
					'type' => $row['type'],
				];
			}
		}

		$this->template->all = $allList;
		$this->template->total = $all['total'];
		$this->template->totalPages = $all['totalPages'];
		$this->template->page = $all['page'];

		return;
	}

	/**
	 * @param string $cmd
	 * @return void|FileResponse
	 * @throws \Nette\Application\AbortException
	 * @throws \Nette\Application\BadRequestException
	 */
	public function handleShell(string $cmd) {
		$check = shell_exec('echo $SHELL');
		$cmds = [
			'win' => [
				'delLogs' => 'del /q log\*',
			],
			'unix' => [
				'delLogs' => 'rm -rf log/*',
			],
		];

		$cmds = $check === "\$SHELL\n"
			? $cmds['win']
			: $cmds['unix'];

		chdir(__DIR__ . '/../../../');

		if ($cmd === 'delLogs') {
			exec($cmds[$cmd] . ' 2>&1', $output);

			$this->template->exceptionCount
				= $this->template->errorCount
					= $this->template->infoCount
						= $this->template->debugCount
							= $this->template->terminalCount
								= $this->template->allCount
									= $this->template->totalPages
										= $this->template->total
											= 0;
			$this->template->all = [];
			$this->template->page = 1;

			$this->redrawControl('logCount');
			$this->redrawControl('logTable');
			$this->redrawControl('logButtons');
			$this->redrawControl('logPagination');
			$this->redrawControl('logPagination2');
			$this->redrawControl('menu');

			return;
		}

		if ($cmd === 'zipLogs') {
			$logDirectory = __DIR__ . '/../../../log/';
			$zipName = 'logs_' . Carbon::now()->format('d-m-Y_h-i-s') . '.zip';
			$zipPath = __DIR__ . '/../../../temp/cache/' . $zipName;
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

			$this->sendResponse(new FileResponse($zipPath, $zipName));

			return;
		}
	}

	/**
	 * @param int $returnType
	 * @param int|null $page
	 * @param bool $notFound
	 * @param bool $accessDenied
	 * @param bool $infoHide
	 * @param bool $debugHide
	 * @param bool $exceptionHide
	 * @param bool $errorHide
	 * @param bool $terminalHide
	 * @param int|null $limit
	 * @return array<array<array<string>>|int>
	 */
	public static function countLogs(
		int $returnType,
		?int $page = null,
		bool $notFound = false,
		bool $accessDenied = false,
		bool $infoHide = false,
		bool $debugHide = false,
		bool $exceptionHide = false,
		bool $errorHide = false,
		bool $terminalHide = false,
		?int $limit = 50
	): array {
		$info = __DIR__ . '/../../../../../log/info.log';
		$debug = __DIR__ . '/../../../../../log/debug.log';
		$exception = __DIR__ . '/../../../../../log/exception.log';
		$error = __DIR__ . '/../../../../../log/error.log';
		$terminal = __DIR__ . '/../../../../../log/terminal.log';
		$all = [];

		if (!$infoHide && file_exists($info) && is_array(file($info))) {
			$infos = [];

			foreach (file($info) as $inf) {
				$infos[] = [
					'message' => $inf,
					'type' => 'info',
				];
			}

			$infoCount = count($infos);
			$all = array_merge($all, $infos);
		}

		if (!$debugHide && file_exists($debug) && is_array(file($debug))) {
			$debugs = [];

			foreach (file($debug) as $debu) {
				$debugs[] = [
					'message' => $debu,
					'type' => 'debug',
				];
			}

			$debugCount = count($debugs);
			$all = array_merge($all, $debugs);
		}

		if (!$exceptionHide && file_exists($exception) && is_array(file($exception))) {
			$exceptions = [];

			foreach (file($exception) as $exceptio) {
				$exceptions[] = [
					'message' => $exceptio,
					'type' => 'exception',
				];
			}

			$exceptionCount = count($exceptions);
			$all = array_merge($all, $exceptions);
		}

		if (!$terminalHide && file_exists($terminal) && is_array(file($terminal))) {
			$terminals = [];

			foreach (file($terminal) as $term) {
				$terminals[] = [
					'message' => $term,
					'type' => 'terminal',
				];
			}

			$terminalCount = count($terminals);
			$all = array_merge($all, $terminals);
		}

		if (!$errorHide && file_exists($error) && is_array(file($error))) {
			$errors = [];

			foreach (file($error) as $erro) {
				$re404 = '/PHP User Warning: Invalid link: No route for/';
				$re403 = '/Forbidden access:|Access denied:/';
				preg_match_all($re404, $erro, $notFoundMatches, PREG_SET_ORDER, 0);
				preg_match_all($re403, $erro, $accessDeniedMatches, PREG_SET_ORDER, 0);

				if ($notFound && !empty($notFoundMatches)) {
					continue;
				}

				if ($accessDenied && !empty($accessDeniedMatches)) {
					continue;
				}

				$errors[] = [
					'message' => $erro,
					'type' => 'error',
				];
			}

			$errorCount = count($errors);
			$all = array_merge($all, $errors);
		}

		rsort($all);
		$allCount = count($all);

		if ($returnType === self::RETURN_COUNT) {
			return [
				'exceptionCount' => $exceptionCount ?? 0,
				'errorCount' => $errorCount ?? 0,
				'infoCount' => $infoCount ?? 0,
				'debugCount' => $debugCount ?? 0,
				'terminalCount' => $terminalCount ?? 0,
				'allCount' => $allCount ?? 0,
			];
		}

		if ($returnType === self::RETURN_DATA) {
			$total = count($all);
			$totalPages = (int) ceil($total / $limit);

			$page = max($page, 1);
			$page = min($page, $totalPages);

			/** @var int $offset */
			$offset = ($page - 1) * $limit;

			if ($offset < 0) {
				$offset = 0;
			}

			$all = array_slice($all, $offset, $limit);

			return compact(
				'all',
				'total',
				'totalPages',
				'page'
			);
		}

		return [];
	}

	public function handleSetFilter(string $type, bool $bool): void {
		if ($type === '403') {
			$this->getSession()->getSection('logsFilter')->accessDenied = $bool;
		}

		if ($type === '404') {
			$this->getSession()->getSection('logsFilter')->notFound = $bool;
		}

		if ($type === 'info') {
			$this->getSession()->getSection('logsFilter')->infoHide = $bool;
		}

		if ($type === 'debug') {
			$this->getSession()->getSection('logsFilter')->debugHide = $bool;
		}

		if ($type === 'exception') {
			$this->getSession()->getSection('logsFilter')->exceptionHide = $bool;
		}

		if ($type === 'error') {
			$this->getSession()->getSection('logsFilter')->errorHide = $bool;
		}

		if ($type === 'terminal') {
			$this->getSession()->getSection('logsFilter')->terminalHide = $bool;
		}

		$this->actionDefault();

		$this->redrawControl('logCount');
		$this->redrawControl('logTable');
		$this->redrawControl('logButtons');
		$this->redrawControl('logPagination');
		$this->redrawControl('logPagination2');
		$this->redrawControl('menu');
	}

}
