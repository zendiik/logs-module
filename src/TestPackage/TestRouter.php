<?php declare(strict_types = 1);

namespace Netleak\TestPackage;

use Nette\Application\Routers\Route;
use Nette\Application\Routers\RouteList;

class TestRouter {

	/**
	 * @var RouteList
	 */
	private $router;

	public function __construct() {
		$this->router = new RouteList();
	}

	/**
	 * @param string $mask
	 * @param string|array<string> $metadata
	 * @param int $flags
	 * @return self
	 */
	public function append(string $mask, $metadata = [], int $flags = 0): self {
		$this->router[] = new Route($mask, $metadata, $flags);

		return $this;
	}

	public function createRouter(): RouteList {
		return $this->router;
	}

}
