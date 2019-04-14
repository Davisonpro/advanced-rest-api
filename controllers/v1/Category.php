<?php
/**
 * @package    PHP Advanced API Guide
 * @author     Davison Pro <davisonpro.coder@gmail.com>
 * @copyright  2019 DavisonPro
 * @version    1.0.0
 * @since      File available since Release 1.0.0
 */

namespace BestShop\v1;

use Db;
use BestShop\Route;
use BestShop\Database\DbQuery;
use BestShop\Product\Category as CategoryObject;
use BestShop\Util\ArrayUtils;
use BestShop\Validate;

class Category extends Route {
	public function getCategories() {
		$api = $this->api;

		// Build query
		$sql = new DbQuery();
		// Build SELECT
		$sql->select('category.*');
		// Build FROM
		$sql->from('category', 'category');
		$categories = Db::getInstance()->executeS($sql);

		return $api->response([
			'success' => true,
			'categories' => $categories
		]);
	}

	public function addCategory() {
		$api = $this->api;
		$payload = $api->request()->post(); 

		$name = ArrayUtils::get($payload, 'name');
		$description = ArrayUtils::get($payload, 'description');

		if (!Validate::isCatalogName($name)) {
			return $api->response([
				'success' => false,
				'message' => 'Enter a valid category name'
			]);
		}

		if (!Validate::isCleanHtml($description)) {
			return $api->response([
				'success' => false,
				'message' => 'Enter a valid description of the category'
			]);
		}

		$category = new CategoryObject();
		$category->name = $name;
		$category->description = $description;

		$ok = $category->save();
		// or $category->add();

		if (!$ok) {
			return $api->response([
				'success' => false,
				'message' => 'Unable to create category'
			]);
		}

		return $api->response([
			'success' => true,
			'message' => 'Category was created',
			'category' => [
				'category_id' => $category->id,
				'name' => $category->name,
				'description' => $category->description,
			]
		]);
	}

}	