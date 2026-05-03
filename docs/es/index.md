# Consola Bake

La consola Bake de CakePHP permite preparar y ejecutar CakePHP rápidamente.
Puede crear muchos de los ingredientes básicos de CakePHP, como modelos,
behaviours, vistas, helpers, controladores, componentes, casos de prueba,
fixtures y plugins.
Bake puede generar mucho más que esqueletos de clases y es un paso natural después de crear la base de la aplicación.

## Instalación

Antes de intentar utilizar o extender Bake, asegúrate de que está instalado en tu aplicación.
Bake se distribuye como un plugin que puedes instalar con Composer:

```bash
composer require --dev cakephp/bake:~1.0
```

La instrucción anterior instalará Bake como una dependencia de desarrollo.
Esto significa que no será instalado cuando hagas despliegues en producción.

## Mapa de documentación

- [Crear código con Bake](/es/usage) cubre la ejecución del CLI, el listado de tareas y los temas de Bake.
- [Extending Bake](/es/development) enlaza al contenido disponible para ampliaciones y personalización.
