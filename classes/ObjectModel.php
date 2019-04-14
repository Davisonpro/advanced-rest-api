<?php
/**
 * @package    PHP Advanced API Guide
 * @author     Davison Pro <davisonpro.coder@gmail.com>
 * @copyright  2019 DavisonPro
 * @version    1.0.0
 * @since      File available since Release 1.0.0
 */

namespace BestShop; 

use Db;
use BestShop\Database\DbQuery;
use BestShop\Validate;
use BestShop\Database\EntityInterface;
use BestShop\Database\EntityMapper;

/**
 * ObjectModel
 */
class ObjectModel {

	/**
     * List of field types.
     */
    const TYPE_INT = 1;
    const TYPE_BOOL = 2;
    const TYPE_STRING = 3;
    const TYPE_FLOAT = 4;
    const TYPE_DATE = 5;
    const TYPE_HTML = 6;
    const TYPE_NOTHING = 7;
    const TYPE_SQL = 8;

    /** @var int Object ID */
    public $id;

    /** @var array|null Holds required fields for each ObjectModel class */
    protected static $fieldsRequiredDatabase = null;

    /**
     * @deprecated 1.0.0 Define property using $definition['table'] property instead.
     *
     * @var string
     */
    protected $table;

    /**
     * @deprecated 1.0.0 Define property using $definition['table'] property instead.
     *
     * @var string
     */
    protected $identifier;

    /**
     * @deprecated 1.0.0 Define property using $definition['table'] property instead.
     *
     * @var array
     */
    protected $fieldsRequired = array();

    /**
     * @deprecated 1.0.0 Define property using $definition['table'] property instead.
     *
     * @var array
     */
    protected $fieldsSize = array();

    /**
     * @deprecated 1.0.0 Define property using $definition['table'] property instead.
     *
     * @var array
     */
    protected $fieldsValidator = array();

    /**
     * @var array Contains object definition
     *
     * @since 1.0.0
     */
    public static $definition = array();

    /**
     * Holds compiled definitions of each ObjectModel class.
     * Values are assigned during object initialization.
     *
     * @var array
     */
    protected static $loaded_classes = array();

    /** @var array Contains current object definition. */
    protected $def;

    /** @var array|null List of specific fields to update (all fields if null). */
    protected $update_fields = null;

    /** @var Db An instance of the db in order to avoid calling Db::getInstance() thousands of times. */
    protected static $db = false;

    /** @var array|null List of HTML field (based on self::TYPE_HTML) */
    public static $htmlFields = null;

    /** @var bool Enables to define an ID before adding object. */
    public $force_id = false;

	/**
     * Returns object validation rules (fields validity).
     *
     * @param string $class Child class name for static use (optional)
     *
     * @return array Validation rules (fields validity)
     */
    public static function getValidationRules($class = __CLASS__)
    {
        $object = new $class();

        return array(
            'required' => $object->fieldsRequired,
            'size' => $object->fieldsSize,
            'validate' => $object->fieldsValidate
        );
	}
	
	/**
     * Builds the object.
     *
     * @param int|null $id if specified, loads and existing object from DB (optional)
     *
     * @throws Exception
     * @throws Exception
     */
    public function __construct($id = null) {
        $class_name = get_class($this);

        if (!isset(ObjectModel::$loaded_classes[$class_name])) {
            $this->def = ObjectModel::getDefinition($class_name);

            if (!Validate::isTableOrIdentifier($this->def['primary']) || !Validate::isTableOrIdentifier($this->def['table'])) {
                throw new \Exception('Identifier or table format not valid for class ' . $class_name);
            }

            ObjectModel::$loaded_classes[$class_name] = get_object_vars($this);
        } else {
            foreach (ObjectModel::$loaded_classes[$class_name] as $key => $value) {
                $this->{$key} = $value;
            }
        }

        if ($id) {
            $this->load($id, $this, $this->def);
        }
	}

    /**
     * Prepare fields for ObjectModel class (add, update)
     * All fields are verified (pSQL, intval, ...).
     *
     * @return array All object fields
     *
     * @throws \Exception
     */
    public function getFields()
    {
        $this->validateFields();
        $fields = $this->formatFields();

        // Ensure that we get something to insert
        if (!$fields && isset($this->id)) {
            $fields[$this->def['primary']] = $this->id;
        }

        return $fields;
    }

    /**
     * Formats values of each fields.
     *
     * @since 1.0.0
     *
     * @return array
     */
    protected function formatFields()
    {
        $fields = array();

        // Set primary key in fields
        if (isset($this->id)) {
            $fields[$this->def['primary']] = $this->id;
        }

        foreach ($this->def['fields'] as $field => $data) {
            // Only get fields we need for the type
            $value = $this->$field;

            $purify = (isset($data['validate']) && Tools::strtolower($data['validate']) == 'iscleanhtml') ? true : false;
            // Format field value
            $fields[$field] = ObjectModel::formatValue($value, $data['type'], false, $purify, !empty($data['allow_null']));
        }

        return $fields;
    }

	/**
     * Formats a value.
     *
     * @param mixed $value
     * @param int $type
     * @param bool $with_quotes
     * @param bool $purify
     * @param bool $allow_null
     *
     * @return mixed
     */
    public static function formatValue($value, $type, $with_quotes = false, $purify = true, $allow_null = false)
    {
        if ($allow_null && $value === null) {
            return array('type' => 'sql', 'value' => 'NULL');
        }

        switch ($type) {
            case self::TYPE_INT:
                return (int) $value;

            case self::TYPE_BOOL:
                return (int) $value;

            case self::TYPE_FLOAT:
                return (float) str_replace(',', '.', $value);

            case self::TYPE_DATE:
                if (!$value) {
                    $value = '0000-00-00';
                }

                if ($with_quotes) {
                    return '\'' . pSQL($value) . '\'';
                }

                return pSQL($value);

            case self::TYPE_HTML:
                if ($purify) {
                    $value = Tools::purifyHTML($value);
                }
                if ($with_quotes) {
                    return '\'' . pSQL($value, true) . '\'';
                }

                return pSQL($value, true);

            case self::TYPE_SQL:
                if ($with_quotes) {
                    return '\'' . pSQL($value, true) . '\'';
                }

                return pSQL($value, true);

            case self::TYPE_NOTHING:
                return $value;

            case self::TYPE_STRING:
            default:
                if ($with_quotes) {
                    return '\'' . pSQL($value) . '\'';
                }

                return pSQL($value);
        }
    }

    /**
     * Saves current object to database (add or update).
     *
     * @param bool $null_values
     * @param bool $auto_date
     *
     * @return bool Insertion result
     *
     * @throws \Exception
     */
    public function save($null_values = false, $auto_date = true)
    {
        return $this->id ? $this->update($null_values) : $this->add($auto_date, $null_values);
    }

	/**
     * Adds current object to the database.
     *
     * @param bool $auto_date
     * @param bool $null_values
     *
     * @return bool Insertion result
     *
     * @throws Exception
     */
    public function add($auto_date = true, $null_values = false)
    {
        if (isset($this->id) && !$this->force_id) {
            unset($this->id);
        }

        // Automatically fill dates
        if ($auto_date && property_exists($this, 'date_add')) {
            $this->date_add = date('Y-m-d H:i:s');
        }
        if ($auto_date && property_exists($this, 'date_upd')) {
            $this->date_upd = date('Y-m-d H:i:s');
        }

        if (!$result = Db::getInstance()->insert($this->def['table'], $this->getFields(), $null_values)) {
            return false;
        }

        // Get object id in database
        if ( !$this->force_id) {
            $this->id = Db::getInstance()->Insert_ID();
        }
        
        if (!$result) {
            return false;
        }

        return $result;
    }

	/**
     * Updates the current object in the database.
     *
     * @param bool $null_values
     *
     * @return bool
     *
     * @throws Exception
     */
    public function update($null_values = false)
    {
        // Automatically fill dates
        if (array_key_exists('date_upd', $this)) {
            $this->date_upd = date('Y-m-d H:i:s');
            if (isset($this->update_fields) && is_array($this->update_fields) && count($this->update_fields)) {
                $this->update_fields['date_upd'] = true;
            }
        }

        // Automatically fill dates
        if (array_key_exists('date_add', $this) && $this->date_add == null) {
            $this->date_add = date('Y-m-d H:i:s');
            if (isset($this->update_fields) && is_array($this->update_fields) && count($this->update_fields)) {
                $this->update_fields['date_add'] = true;
            }
        }

        // Database update
        if (!$result = Db::getInstance()->update($this->def['table'], $this->getFields(), '`' . pSQL($this->def['primary']) . '` = "' . pSQL($this->id) . '"', 0, $null_values)) {
            return false;
        }

        return $result;
    }

	/**
     * Deletes current object from database.
     *
     * @return bool True if delete was successful
     *
     * @throws Exception
     */
    public function delete()
    {
        $result = Db::getInstance()->delete($this->def['table'], '`' . bqSQL($this->def['primary']) . '` = "' . $this->id . '"');

        if (!$result) {
            return false;
        }

        return $result;
    }

	/**
     * Deletes multiple objects from the database at once.
     *
     * @param array $ids array of objects IDs
     *
     * @return bool
     */
    public function deleteSelection($ids)
    {
        $result = true;
        foreach ($ids as $id) {
            $this->id = $id;
            $result = $result && $this->delete();
        }

        return $result;
    }

	/**
     * Validate a single field.
     *
     * @since 1.0.0
     *
     * @param string $field Field name
     * @param mixed $value Field value
     * @param array $skip array of fields to skip
     * @param bool $human_errors if true, uses more descriptive error strings
     *
     * @return true|string true or error message string
     *
     * @throws Exception
     */
    public function validateField($field, $value, $skip = array(), $human_errors = false)
    {
        static $hp_allow_html_iframe = null;

        $data = $this->def['fields'][$field];

        // Default value
        if (!$value && !empty($data['default'])) {
            $value = $data['default'];
            $this->$field = $value;
        }

        // Check field values
        if (!in_array('values', $skip) && !empty($data['values']) && is_array($data['values']) && !in_array($value, $data['values'])) {
            return $this->trans('Property %1$s has a bad value (allowed values are: %2$s).', array(get_class($this) . '->' . $field, implode(', ', $data['values'])), 'Admin.Notifications.Error');
        }

        // Check field size
        if (!in_array('size', $skip) && !empty($data['size'])) {
            $size = $data['size'];
            if (!is_array($data['size'])) {
                $size = array('min' => 0, 'max' => $data['size']);
            }

            $length = Tools::strlen($value);
            if ($length < $size['min'] || $length > $size['max']) {
                if ($human_errors) {
                    return sprintf('The %1$s field is too long (%2$d chars max).', $this->displayFieldName($field, get_class($this), $size['max']));
                } else {
                    return sprintf('The length of property %1$s is currently %2$d chars. It must be between %3$d and %4$d chars.', 
                        get_class($this) . '->' . $field,
                        $length,
                        $size['min'],
                        $size['max']
                    );
                }
            }
        }

        // Check field validator
        if (!in_array('validate', $skip) && !empty($data['validate'])) {
            if (!method_exists('BestShop\Validate', $data['validate'])) {
                throw new \Exception(
                    sprintf('Validation function not found: %s.', $data['validate'])
                );
            }

            if (!empty($value)) {
                $res = true;
                if (Tools::strtolower($data['validate']) == 'iscleanhtml') {
                    if (!call_user_func(array('BestShop\Validate', $data['validate']), $value, $hp_allow_html_iframe)) {
                        $res = false;
                    }
                } else {
                    if (!call_user_func(array('BestShop\Validate', $data['validate']), $value)) {
                        $res = false;
                    }
                }
                if (!$res) {
                    if ($human_errors) {
                        return sprintf('The %s field is invalid.', $this->displayFieldName($field, get_class($this)));
                    } else {
                        return sprintf('Property %s is not valid', get_class($this) . '->' . $field);
                    }
                }
            }
        }

        return true;
    }

	/**
     * Checks if object field values are valid before database interaction.
     *
     * @param bool $die
     * @param bool $error_return
     *
     * @return bool|string true, false or error message
     *
     * @throws Exception
     */
    public function validateFields($die = true, $error_return = false)
    {   
        foreach ($this->def['fields'] as $field => $data) {
            if ( is_array($this->update_fields) && empty($this->update_fields[$field]) && isset($this->def['fields'][$field]) && $this->def['fields'][$field]) {
                continue;
            }

            $message = $this->validateField($field, $this->$field);
            if ($message !== true) {
                if ($die) {
                    throw new \Exception($message);
                }

                return $error_return ? $message : false;
            }
        }

        return true;
    }

    /**
     * Returns object definition.
     *
     * @param string $class Name of object
     * @param string|null $field Name of field if we want the definition of one field only
     *
     * @return array
     */
    public static function getDefinition($class, $field = null)
    {
        if (is_object($class)) {
            $class = get_class($class);
        }

        $definition = $class::$definition;

        $definition['classname'] = $class;

        if ($field) {
            return isset($definition['fields'][$field]) ? $definition['fields'][$field] : null;
        }

        return $definition;
    }
	
	/**
     * Load ObjectModel.
     *
     * @param $id
     * @param $entity \ObjectModel
     * @param $entity_defs
     *
     * @throws \Exception
     */
    public function load($id, $entity, $entity_defs)
    {
        // Load object from database if object id is present
		$sql = new DbQuery();
		$sql->from($entity_defs['table'], 'a');
		$sql->where('a.`' . bqSQL($entity_defs['primary']) . '` =  \'' . pSQL($id) . '\'');

		if ($object_datas = Db::getInstance()->getRow($sql)) {
			$entity->id = $id;
			foreach ($object_datas as $key => $value) {
				if (array_key_exists($key, $entity_defs['fields'])
					|| array_key_exists($key, $entity)) {
					$entity->{$key} = $value;
				} else {
					unset($object_datas[$key]);
				}
			}
		}
        
    }
}