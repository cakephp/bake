# Geração de Código com Bake

O console do Bake é executado usando o PHP CLI.
Se você tiver problemas para executar o script, assegure-se de que:

1. Você instalou o PHP CLI e possui os módulos apropriados habilitados, por exemplo MySQL e `intl`.
2. Se o host do banco de dados for `localhost`, tente `127.0.0.1`, pois `localhost` pode causar problemas no PHP CLI.
3. Dependendo de como o seu computador está configurado, pode ser necessário definir permissões de execução no script Cake para chamá-lo com `bin/cake bake`.

Antes de executar o Bake, você deve ter pelo menos uma conexão de banco de dados configurada.

Para ver as opções disponíveis no Bake, digite:

```bash
$ bin/cake bake --help

Current Paths:

* app:  src
* root: .
* core: .\vendor\cakephp\cakephp

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
 - bake task
 - bake template
 - bake template all
 - bake test

To run a command, type `cake command_name [args|options]`
To get help on a specific command, type `cake command_name --help`
```

Você pode obter mais informações sobre o que cada tarefa faz e quais são suas opções usando `--help`:

```bash
$ bin/cake bake model --help

Bake table and entity classes.

Usage:
cake bake model [options] [<name>]

Options:

--connection, -c       The datasource connection to get data from.
                       (default: default)
--display-field        The displayField if you would like to choose one.
--fields               A comma separated list of fields to make
                       accessible.
--force, -f            Force overwriting existing files without
                       prompting.
--help, -h             Display this help.
--hidden               A comma separated list of fields to hide.
--no-associations      Disable generating associations.
--no-entity            Disable generating an entity class.
--no-fields            Disable generating accessible fields in the
                       entity.
--no-fixture           Do not generate a test fixture skeleton.
--no-hidden            Disable generating hidden fields in the entity.
--no-rules             Disable generating a rules checker.
--no-table             Disable generating a table class.
--no-test              Do not generate a test case skeleton.
--no-validation        Disable generating validation rules.
--plugin, -p           Plugin to bake into.
--primary-key          The primary key if you would like to manually set
                       one. Can be a comma separated list if you are
                       using a composite primary key.
--quiet, -q            Enable quiet output.
--table                The table name to use if you have
                       non-conventional table names.
--theme, -t            The theme to use when baking code.
--verbose, -v          Enable verbose output.

Arguments:

name  Name of the model to bake (without the Table suffix). You can use
      Plugin.name to bake plugin models. (optional)

Omitting all arguments and options will list the table names you can
generate models for.
```

## Temas para o Bake

A opção `theme` é comum a todos os comandos do Bake e permite mudar os arquivos de template usados por ele.
Para criar seus próprios templates, veja [Criando um Tema Bake](/pt/development#criando-um-tema-bake).
