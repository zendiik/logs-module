# Logs Module

### Instalace

Toto přidejte do `composer.json`:
```json
"repositories": [
	{
		"type": "vcs",
		"url": "ssh://git@bitbucket.oksystem.local:7999/web/logs-module.git"
	}
],
```

Poté nad projektem spusťte:
```bash
composer require oksystem/logs 
```

Následně v presenteru vygenerujte komponentu:

```php
public function createComponentLogs(string $name): void {
	$logs = new LogsControl(__DIR__ . '/../../../'); // cesta k rootu aplikace 
	$logs->useLogs = array( // true je automaticky, není povinné
		'error' => true,
		'info' => true,
		'terminal' => true,
		'debug' => true,
		'exception' => true
	);
	$this->addComponent($logs, $name);
}
```

Vytvořte latte soubor a do něj inicializujte control:

```latte
{block content}
{control logs}
```

##### Nakonec extrahujte obsah vendor/oksystem/logs/src/assets.zip do složky www
