<?php
namespace Bake\View\Helper;

use Bake\Utility\Model\AssociationFilter;
use Cake\Core\Configure;
use Cake\Core\ConventionsTrait;
use Cake\Utility\Inflector;
use Cake\View\Helper;

/**
 * Bake helper
 */
class BakeHelper extends Helper
{
    use ConventionsTrait;

    /**
     * Default configuration.
     *
     * @var array
     */
    protected $_defaultConfig = [];

    /**
     * AssociationFilter utility
     *
     * @var AssociationFilter
     */
    protected $_associationFilter = null;

    /**
     * Used for generating formatted properties such as component and helper arrays
     *
     * @param string $name the name of the property
     * @param array $value the array of values
     * @param array $options extra options to be passed to the element
     * @return string
     */
    public function arrayProperty($name, array $value = [], array $options = [])
    {
        if (!$value) {
            return '';
        }

        foreach ($value as &$val) {
            $val = Inflector::camelize($val);
        }
        $options += [
            'name' => $name,
            'value' => $value
        ];
        return $this->_View->element('array_property', $options);
    }

    /**
     * Returns an array converted into a formatted multiline string
     *
     * @param array $list array of items to be stringified
     * @param array $options options to use
     * @return string
     */
    public function stringifyList(array $list, array $options = [])
    {
        $options += [
            'indent' => 2,
            'tab' => '    ',
            'trailingComma' => false,
        ];

        if (!$list) {
            return '';
        }

        foreach ($list as $k => &$v) {
            if (is_array($v)) {
                $v = "['" . join($v, "', '") . "']";
            } else {
                $v = "'" . $v . "'";
            }
            if (!is_numeric($k)) {
                $v = "'$k' => $v";
            }
        }

        $start = $end = '';
        $join = ', ';
        if ($options['indent']) {
            $join = ',';
            $start = "\n" . str_repeat($options['tab'], $options['indent']);
            $join .= $start;
            $end = "\n" . str_repeat($options['tab'], $options['indent'] - 1);
        }
        
        if ($options['trailingComma']) {
            $end = "," . $end;
        }

        return $start . implode($join, $list) . $end;
    }

    /**
     * Extract the aliases for associations, filters hasMany associations already extracted as
     * belongsToMany
     *
     * @param \Cake\ORM\Table $table object to find associations on
     * @param string $assoc association to extract
     * @return array
     */
    public function aliasExtractor($table, $assoc)
    {
        $extractor = function ($val) {
            return $val->target()->alias();
        };
        $aliases = array_map($extractor, $table->associations()->type($assoc));
        if ($assoc === 'HasMany') {
            return $this->_filterHasManyAssociationsAliases($table, $aliases);
        }

        return $aliases;
    }

    /**
     * Returns details about the given class.
     *
     * The returned array holds the following keys:
     *
     * - `fqn` (the fully qualified name)
     * - `namespace` (the full namespace without leading separator)
     * - `class` (the class name)
     * - `plugin` (either the name of the plugin, or `null`)
     * - `name` (the name of the component without suffix)
     * - `fullName` (the full name of the class, including possible vendor and plugin name)
     *
     * @param string $class Class name
     * @param string $type Class type/sub-namespace
     * @param string $suffix Class name suffix
     * @return array Class info
     */
    public function classInfo($class, $type, $suffix)
    {
        list($plugin, $name) = \pluginSplit($class);

        $base = Configure::read('App.namespace');
        if ($plugin !== null) {
            $base = $plugin;
        }
        $base = str_replace('/', '\\', trim($base, '\\'));
        $sub = '\\' . str_replace('/', '\\', trim($type, '\\'));
        $qn = $sub . '\\' . $name . $suffix;

        if (class_exists('\Cake' . $qn)) {
            $base = 'Cake';
        }

        return [
            'fqn' => '\\' . $base . $qn,
            'namespace' => $base . $sub,
            'plugin' => $plugin,
            'class' => $name . $suffix,
            'name' => $name,
            'fullName' => $class
        ];
    }

    /**
     * To be mocked elsewhere...
     *
     * @param \Cake\ORM\Table $table Table
     * @param array $aliases array of aliases
     */
    protected function _filterHasManyAssociationsAliases($table, $aliases)
    {
        if (is_null($this->_associationFilter)) {
            $this->_associationFilter = new AssociationFilter();
        }
        return $this->_associationFilter->filterHasManyAssociationsAliases($table, $aliases);
    }
}
