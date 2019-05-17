<?php declare(strict_types = 1);

namespace Netleak\TestPackage\DI;

use Nette\DI\Compiler;
use Nette\DI\CompilerExtension;
use Nette\DI\Helpers;
use Nette\PhpGenerator\ClassType;

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

		/*$builder->addDefinition($this->prefix('router'))
			->setClass(TestRouter::class)
			->addSetup('append', ['admin/test-log', 'Admin:Log']);*/

		Compiler::loadDefinitions(
			$builder,
			$this->loadFromFile(__DIR__ . '/../config/config.neon')['services']
		);
	}

	public function afterCompile(ClassType $class): void {
		$initialize = $class->getMethod('initialize');
		$builder = $this->getContainerBuilder();

		$initialize->addBody($builder->formatPhp(
			'$this->getService(?)->addRoute(new \Nette\Application\Routers\Route(?, ?));',
			Helpers::filterArguments([
				$this->prefix('router'),
				'admin/test-log',
				'Admin:Log',
			])
		));
	}
}
