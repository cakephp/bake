# Consola Bake

La consola bake de CakePHP es otro esfuerzo para ayudarle a empezar a trabajar con CakePHP rápidamente.
La consola bake puede crear cualquiera de los ingredientes básicos de CakePHP: modelos, behaviors, vistas, helpers, controladores, componentes, casos de prueba, fixtures y plugins.
Bake puede generar mucho más que esqueletos de clases y es un paso natural después de crear la base de la aplicación.

## Instalación

Antes de intentar utilizar o extender Bake, asegúrese de que está instalado en su aplicación.
Bake se distribuye como un plugin que puede instalar con Composer:

```bash
composer require --dev cakephp/bake:"^4.0"
```

La instrucción anterior instalará Bake como una dependencia de desarrollo, por lo que no será instalado cuando haga despliegues en producción.

Cuando utilice templates Twig, asegúrese de cargar el plugin `Cake/TwigView` con su bootstrap.
También puede omitirlo por completo, con lo que el plugin Bake lo cargará bajo demanda.

## Mapa de documentación

- [Crear código con Bake](/es/usage) cubre la ejecución de la consola, el listado de comandos, la generación de modelos y enums, y el cambio de temas de bake.
- [Extender Bake](/es/development) cubre los eventos, los templates Twig, los temas personalizados, las sobrescrituras de templates de la aplicación y la creación de comandos bake personalizados.
