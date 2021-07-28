# Logs Module

### Instalace

Přidejte do `composer.json`:
```json
"repositories": [
	{
		"type": "vcs",
		"url": "ssh://git@bitbucket.oksystem.local:7999/web/logs-module.git"
	}
],
```

Nad projektem spusťte:
```shell
composer require oksystem/logs
npm install git+ssh://git@bitbucket.oksystem.local:7999/web/logs-module-assets.git
```

Následně v presenteru vygenerujte komponentu:

```php
public function createComponentLogs(string $name): void {
	$logs = new LogsControl(__DIR__ . '/../../../', '/'); // cesta k rootu aplikace, '/' je automatický prefix veřejné cesty k assetům

	// pokud chcete zakázat zobrazení logu warning.log a error.log
	// stačí přidat název souboru 
	// zbytek ze složky s logy je automaticky zobrazen
	$logs->disableLogs('warning', 'error');

    $this->addComponent($logs, $name);
}
```

Vytvořte latte soubor a do něj inicializujte control:

```latte
{block content}
{control logs}
```
