# Crear código con Bake

La consola de Cake se ejecuta usando PHP CLI.
Si tiene problemas para ejecutar el script, asegúrese de:

1. Tener instalado PHP CLI y los módulos correspondientes habilitados, por ejemplo MySQL e `intl`.
2. Si el host de la base de datos es `localhost`, intentar la conexión con `127.0.0.1`, ya que en algunos casos PHP CLI tiene problemas al usar `localhost`.
3. Dependiendo de cómo esté configurado su equipo, la ejecución de `bin/cake bake` puede requerir permisos de ejecución.

Antes de comenzar, asegúrese de disponer al menos de una conexión a base de datos configurada.

Para comenzar con la ejecución del comando puede abrir la consola y ejecutar `Cake bake`.

1. Ir a Inicio > Ejecutar.
2. Escribir `cmd` y presionar Enter.
3. Navegar hasta la carpeta de instalación de Cake.
4. Acceder a la carpeta `bin`.
5. Escribir `Cake bake`, lo cual deberá devolver un listado con las tareas disponibles.

El resultado debería ser algo similar a lo siguiente:

```bash
$ bin/cake bake

Welcome to CakePHP v3.1.6 Console
---------------------------------------------------------------
App : src
Path: /var/www/cakephp.dev/src/
PHP: 5.5.8
---------------------------------------------------------------
The following commands can be used to generate skeleton code for your application.

Available bake commands:

- all
- behavior
- cell
- component
- controller
- fixture
- form
- helper
- mailer
- migration
- migration_snapshot
- model
- plugin
- template
- test

By using 'cake bake [name]' you can invoke a specific bake task.
```

Puede obtener más información sobre lo que realiza cada tarea y sus opciones usando `--help`:

```bash
$ bin/cake bake controller --help

Welcome to CakePHP v3.1.6 Console
---------------------------------------------------------------
App : src
Path: /var/www/cakephp.dev/src/
---------------------------------------------------------------
Bake a controller skeleton.

Usage:
cake bake controller [subcommand] [options] [<name>]

Subcommands:

all  Bake all controllers with CRUD methods.

To see help on a subcommand use `cake bake controller [subcommand] --help`

Options:

--help, -h        Display this help.
--verbose, -v     Enable verbose output.
--quiet, -q       Enable quiet output.
--plugin, -p      Plugin to bake into.
--force, -f       Force overwriting existing files without prompting.
--connection, -c  The datasource connection to get data from.
                  (default: default)
--theme, -t       The theme to use when baking code.
--components      The comma separated list of components to use.
--helpers         The comma separated list of helpers to use.
--prefix          The namespace/routing prefix to use.
--no-test         Do not generate a test skeleton.
--no-actions      Do not generate basic CRUD action methods.

Arguments:

name  Name of the controller to bake. Can use Plugin.name to bake
    controllers into plugins. (optional)
```

## Temas Bake / Templates

La opción `theme` es genérica para todos los comandos Bake y permite cambiar los templates utilizados para generar los archivos finales.
Para crear sus propios templates, vea [Creating a Bake Theme](/es/development#creating-a-bake-theme).
