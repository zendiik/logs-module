<?php declare(strict_types = 1);

namespace Netleak\Logs\Presenters;

use Carbon\Carbon;
use Nette\Application\Responses\FileResponse;
use Nette\Application\UI\Presenter;
use ZipArchive;

class LogsPresenter extends Presenter {

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

}
