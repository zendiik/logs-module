<?php declare(strict_types = 1);

namespace Netleak\Logs\DI;

use Netleak\Logs\Presenters\LogsPresenter;
use Nette\Application\IPresenterFactory;
use Nette\DI\CompilerExtension;
use Nette\DI\Helpers;
use Nette\PhpGenerator\ClassType;
use Tracy\Debugger;

final class LogsExtension extends CompilerExtension {

	/**
	 * @var array<string>
	 */
	private $defaults = [
		'adminRoute' => 'admin',
		'appDir' => '%appDir%',
		'tempDir' => '%tempDir%',
	];

	public function loadConfiguration(): void {
		$this->validateConfig($this->defaults);
		$builder = $this->getContainerBuilder();
	}

	public function beforeCompile(): void {
		$builder = $this->getContainerBuilder();

		$builder->getDefinition($builder->getByType(IPresenterFactory::class) ?: 'nette.presenterFactory')
			->addSetup(
				'if (method_exists($service, ?)) { $service->setMapping([? => ?]); }',
				[
					'setMapping',
					'Netleak',
					'Netleak\Logs\Presenters\*Presenter',
				]
			);
	}

	public function afterCompile(ClassType $class): void {
		$initialize = $class->getMethod('initialize');
		$builder = $this->getContainerBuilder();

		$initialize->addBody($builder->formatPhp(
			'$this->getService(?)->prepend(new \Nette\Application\Routers\Route(?, ?))',
			Helpers::filterArguments([
				'router',
				$this->getConfig()['adminRoute'] . '/test-log',
				[
					'presenter' => LogsPresenter::NAME,
					'action' => 'default',
				],
			])
		));
	}

}
