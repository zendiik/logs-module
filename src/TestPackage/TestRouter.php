<?php declare(strict_types = 1);

namespace Netleak\TestPackage;

use Nette\Application\Routers\Route;
use Nette\Application\Routers\RouteList;
use Tracy\Debugger;

class TestRouter {

	private $router;

	public function __construct() {
		$this->router = new RouteList();
	}

	public function append($mask, $metadata = [], $flags = 0) {
		$this->router[] = new Route($mask, $metadata, $flags);
	}

	public function createRouter() {
		return $this->router;
	}

}
