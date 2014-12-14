{
    "name": "your-name-here/<%= $plugin %>",
    "description": "<%= $plugin %> plugin for CakePHP",
    "type": "cakephp-plugin",
    "require": {
        "php": ">=5.4",
        "cakephp/plugin-installer": "*",
        "cakephp/cakephp": "3.0.x-dev"
    },
    "require-dev": {
        "phpunit/phpunit": "*"
    },
    "autoload": {
        "psr-4": {
            "<%= $plugin %>\\": "src"
        }
    },
    "autoload-dev": {
        "psr-4": {
            "<%= $plugin %>\\Test\\": "tests",
            "Cake\\Test\\": "./vendor/cakephp/cakephp/tests"
        }
    }
}
