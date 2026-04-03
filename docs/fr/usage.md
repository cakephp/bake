# Génération de code avec Bake

La console Bake est exécutée avec le CLI PHP.
Si vous avez des problèmes en exécutant ce script, vérifiez que :

1. Le CLI PHP est installé et qu'il a les bons modules activés, par exemple MySQL et `intl`.
2. Si l'hôte de la base de données est `localhost`, essayez `127.0.0.1`, car `localhost` peut causer des problèmes avec PHP CLI.
3. Selon la configuration de votre ordinateur, vous devrez peut-être donner les permissions d'exécution au script `cake` pour autoriser le lancement via `bin/cake bake`.

Avant de lancer Bake, vous devez vous assurer qu'au moins une connexion de base de données est configurée.

Vous pouvez voir la liste des commandes disponibles en lançant `bin/cake bake --help`.
Pour Windows, utilisez `bin\cake bake --help` :

```bash
$ bin/cake bake --help
Current Paths:

* app:  src/
* root: /path/to/your/app/
* core: /path/to/your/app/vendor/cakephp/cakephp/

Available Commands:

Bake:
- bake all
- bake behavior
- bake cell
- bake command
- bake component
- bake controller
- bake controller all
- bake fixture
- bake fixture all
- bake form
- bake helper
- bake mailer
- bake middleware
- bake model
- bake model all
- bake plugin
- bake template
- bake template all
- bake test

To run a command, type `cake command_name [args|options]`
To get help on a specific command, type `cake command_name --help`
```

## Thèmes de Bake

L'option `theme` est commune à toutes les commandes Bake et permet de changer les fichiers de template utilisés lors de la génération.
Pour créer vos propres templates, référez-vous à [Créer un thème de Bake](/fr/development#creer-un-theme-de-bake).
