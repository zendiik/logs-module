# Logs Module

### Instalace

Přidejte do `composer.json`:
```json
"scripts": {
	"post-autoload-dump": "npm install git+ssh://git@bitbucket.oksystem.local:7999/web/logs-module-assets.git"
},
"repositories": [
	{
		"type": "vcs",
		"url": "ssh://git@bitbucket.oksystem.local:7999/web/logs-module.git"
	}
],
```

Nad projektem spusťte:
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
