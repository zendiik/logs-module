<?php declare(strict_types = 1);

namespace Netleak\DI;

use Nette\DI\Compiler;
use Nette\DI\CompilerExtension;

final class TestPackageExtension extends CompilerExtension {

	private $defaults = [
		'route' => 'admin/test-log',
	];

	public function loadConfiguration(): void {
		$this->validateConfig($this->defaults);

		Compiler::loadDefinitions(
			$this->getContainerBuilder(),
			$this->loadFromFile(__DIR__ . '/../config/config.neon')['services']
		);
	}
}
