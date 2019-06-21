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
	$logs = new LogsControl(__DIR__ . '/../../../', '/'); // cesta k rootu aplikace, '/' je automatický prefix veřejné cesty k assetům

	// pokud chcete logování terminal.log či warning.log 
	// zbytek je automaticky zobrazen
	// pro vypnutí stačí přidat řádek s false hodnotou (eg. `'error' => false`)
	$logs->useLogs = array(
		'terminal' => true,
		'warning' => true
	);

    $this->addComponent($logs, $name);
}
```

Vytvořte latte soubor a do něj inicializujte control:

```latte
{block content}
{control logs}
```
