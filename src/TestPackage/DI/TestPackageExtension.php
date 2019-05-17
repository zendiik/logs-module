<?php declare(strict_types = 1);

namespace Netleak\DI;

use Netleak\TestPackage\DI;
use Nette\Application\IRouter;
use Nette\DI\Compiler;
use Nette\DI\CompilerExtension;

final class TestPackageExtension extends CompilerExtension {

	/**
	 * @var array<string>
	 */
	private $defaults = [
		'route' => 'admin/test-log',
	];

	public function loadConfiguration(): void {
		$this->validateConfig($this->defaults);
		$builder = $this->getContainerBuilder();

		$builder->addDefinition($this->prefix('router'))
			->setClass(TestRouter::class)
			->addSetup('append', ['admin/test-log', 'Admin:Log']);

		Compiler::loadDefinitions(
			$builder,
			$this->loadFromFile(__DIR__ . '/../config/config.neon')['services']
		);
	}

	public function beforeCompile() {
		$builder = $this->getContainerBuilder();

		$builder->removeDefinition('routing.router');
		$builder->getDefinition($this->prefix('router'));
	}
}
