<?php 
/**
 * @package    BestShop
 * @author     Davison Pro <davisonpro.coder@gmail.com>
 * @copyright  2018 BestShop
 * @version    1.0.0
 * @since      File available since Release 1.0.0
 */

namespace BestShop\Product;

use Db;
use BestShop\Database\DbQuery;
use BestShop\ObjectModel;

class Category extends ObjectModel {
	/** @var $id Category ID */
	public $id;

	/** @var string $name */
	public $name;

	/** @var string $description */
	public $description;
	
	/** @var $date_add */
    public $date_add;
	
	/** @var $date_upd */
    public $date_upd;

	/**
     * @see ObjectModel::$definition
     */
    public static $definition = array(
        'table' => 'category',
        'primary' => 'category_id',
        'fields' => array(
			'name' => array('type' => self::TYPE_STRING, 'required' => true, 'validate' => 'isString', 'size' => 255),
			'description' => array('type' => self::TYPE_STRING, 'required' => true),
			'date_add' => array('type' => self::TYPE_DATE, 'validate' => 'isDate'),
			'date_upd' => array('type' => self::TYPE_DATE, 'validate' => 'isDate'),
        )
    );

     /**
     * constructor.
     *
     * @param null $id
     */
    public function __construct($id = null)
    {
        parent::__construct($id);
	}
}