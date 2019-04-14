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

class Product extends ObjectModel {
	/** @var $id Product ID */
	public $id;

	/** @var int $category_id */
	public $category_id;
	
	/** @var string $name */
	public $name;

	/** @var string $description */
	public $description;
	
	/** @var int $price */
	public $price = 0;    
	
	/** @var $date_add */
    public $date_add;
	
	/** @var $date_upd */
    public $date_upd;

	/**
     * @see ObjectModel::$definition
     */
    public static $definition = array(
        'table' => 'product',
        'primary' => 'product_id',
        'fields' => array(
			'category_id' => array('type' => self::TYPE_INT, 'validate' => 'isInt', 'size' => 11),
			'name' => array('type' => self::TYPE_STRING, 'required' => true, 'validate' => 'isString', 'size' => 32),
			'description' => array('type' => self::TYPE_STRING, 'required' => true),
			'price' => array('type' => self::TYPE_FLOAT, 'required' => true),
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