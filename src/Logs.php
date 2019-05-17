<?php declare(strict_types = 1);

namespace Netleak\Logs;

class Logs {

	/**
	 * @var string
	 */
	private $logDir;

	public function __construct(string $appDir) {
		$this->logDir = $appDir . '../log';
	}

}
