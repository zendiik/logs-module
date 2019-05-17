<?php declare(strict_types = 1);

namespace Netleak;

use Tracy\Debugger;

class TestRouter {

	public function __construct() {
		Debugger::barDump('ahoj');
	}
}
