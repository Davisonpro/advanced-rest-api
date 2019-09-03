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
use BestShop\Product\Product as ProductObject;
use BestShop\Product\Category as CategoryObject;
use BestShop\Util\ArrayUtils;
use BestShop\Validate;

class Product extends Route {

	public function getProducts() {
		$api = $this->api;

		// Build query
		$sql = new DbQuery();
		// Build SELECT
		$sql->select('product.*');
		// Build FROM
		$sql->from('product', 'product');
		$products = Db::getInstance()->executeS($sql);

		return $api->response([
			'success' => true,
			'products' => $products
		]);
	}

	public function addProduct() {
		$api = $this->api;
		$payload = $api->request()->post(); 

		$name = ArrayUtils::get($payload, 'name');
		$description = ArrayUtils::get($payload, 'description');
		$price = ArrayUtils::get($payload, 'price');
		$category_id = ArrayUtils::get($payload, 'category_id');

		if (!Validate::isGenericName($name)) {
			return $api->response([
				'success' => false,
				'message' => 'Enter a valid product name'
			]);
		}

		if (!Validate::isCleanHtml($description)) {
			return $api->response([
				'success' => false,
				'message' => 'Enter a valid description of the product'
			]);
		}

		if (!Validate::isPrice($price)) {
			return $api->response([
				'success' => false,
				'message' => 'Enter a valid price of the product'
			]);
		}

		if(!Validate::isInt($category_id)) {
			return $api->response([
				'success' => false,
				'message' => 'Enter a valid category ID of the product'
			]);
		}

		$category = new CategoryObject( (int) $category_id );
		if (!Validate::isLoadedObject($category)) {
			return $api->response([
				'success' => false,
				'message' => 'The category ID (' . $category_id . ') does not exist'
			]);
		}

		$product = new ProductObject();
		$product->name = $name;
		$product->description = $description;
		$product->price = (float) $price;
		$product->category_id = $category->id;

		$ok = $product->save();
		// or $product->add();

		if (!$ok) {
			return $api->response([
				'success' => false,
				'message' => 'Unable to create product'
			]);
		}

		return $api->response([
			'success' => true,
			'message' => 'Product was Created',
			'product' => [
				'product_id' => $product->id,
				'name' => $product->id,
				'description' => $product->description,
				'price' => (float) $product->price,
				'category' => [
					'category_id' => $category->id,
					'name' => $category->name,
					'description' => $category->description,
				],
			]
		]);
	}

	public function getProduct( $productId ) {
		$api = $this->api;

		$product = new ProductObject( (int) $productId );
		if(!Validate::isLoadedObject($product)) {
			$api->response->setStatus(404);
			return $api->response([
				'success' => false,
				'message' => 'Product was not found'
			]);
		}
		
		$category = new CategoryObject( $product->category_id );

		return $api->response([
			'success' => true,
			'message' => 'Product was Created',
			'product' => [
				'product_id' => $product->id,
				'name' => $product->name,
				'description' => $product->description,
				'price' => (float) $product->price,
				'category' => [
					'category_id' => $category->id,
					'name' => $category->name,
					'description' => $category->description,
				],
			]
		]);
	}

	public function updateProduct($productId ) {
		$api = $this->api;
		$payload = $api->request()->post(); 

		$product = new ProductObject( (int) $productId );
		if(!Validate::isLoadedObject($product)) {
			$api->response->setStatus(404);
			return $api->response([
				'success' => false,
				'message' => 'Product was not found'
			]);
		}

		if (ArrayUtils::has($payload, 'name')) {
			$name = ArrayUtils::get($payload, 'name');
			if ( !Validate::isGenericName($name) ) {
				return $api->response([
					'success' => false,
					'message' => 'Enter a valid product name'
				]);
			}

			$product->name = $name;
		}

		if (ArrayUtils::has($payload, 'description')) {
			$description = ArrayUtils::get($payload, 'description');
			if (!Validate::isCleanHtml($description)) {
				return $api->response([
					'success' => false,
					'message' => 'Enter a valid description of the product'
				]);
			}

			$product->description = $description;
		}

		if (ArrayUtils::has($payload, 'description')) {
			$price = ArrayUtils::get($payload, 'price');
			if (!Validate::isPrice($price)) {
				return $api->response([
					'success' => false,
					'message' => 'Enter a valid price of the product'
				]);
			}

			$product->price = $price;
		}

		if (ArrayUtils::has($payload, 'category_id')) {
			$category_id = ArrayUtils::get($payload, 'category_id');
			if(!Validate::isInt($category_id)) {
				return $api->response([
					'success' => false,
					'message' => 'Enter a valid category ID of the product'
				]);
			}

			$category = new CategoryObject( (int) $category_id );
			if (!Validate::isLoadedObject($category)) {
				return $api->response([
					'success' => false,
					'message' => 'The category ID (' . $category_id . ') does not exist'
				]);
			}

			$product->category_id = $category->id;
		}

		return $api->response([
			'success' => false,
			'message' => 'Unable to update product'
		]);

		$ok = $product->save();
		// or product->update()
		
		if (!$ok) {
			return $api->response([
				'success' => false,
				'message' => 'Unable to update product'
			]);
		}

		return $api->response([
			'success' => true,
			'message' => 'Product updated successfully'
		]);
	}

	public function deleteProduct( $productId ) {
		$api = $this->api;

		$product = new ProductObject( (int) $productId );
		if(!Validate::isLoadedObject($product)) {
			$api->response->setStatus(404);
			return $api->response([
				'success' => false,
				'message' => 'Product was not found'
			]);
		}

		$ok = $product->delete();

		if (!$ok) {
			return $api->response([
				'success' => false,
				'message' => 'Unable to delete product'
			]);
		}

		return $api->response([
			'success' => true,
			'message' => 'Product deleted successfully'
		]);
	}
	
	public function searchProducts() {
		$api = $this->api;
		$params = $api->request()->get(); 

		$name = ArrayUtils::get($params, 'name');
		$description = ArrayUtils::get($params, 'description');

		if(!$name && !$description) {
			return $api->response([
				'success' => false,
				'message' => 'Enter name or description of the product'
			]);
		}

		// Build query
		$sql = new DbQuery();
		// Build SELECT
		$sql->select('product.*');
		// Build FROM
		$sql->from('product', 'product');

		// prevent sql from searching a NULL value if wither name or description is not provided eg. WHERE name = null
		$where_clause = array();
		if($name) {
			$where_clause[] = 'product.name LIKE \'%' . pSQL($name) . '%\'';
		}

		if ($description) {
			$where_clause[] = 'product.description LIKE \'%' . pSQL($description) . '%\'';
		}

		// join the search terms
		$where_clause = implode(' OR ', $where_clause);

		// Build WHERE
		$sql->where($where_clause);

		$products = Db::getInstance()->executeS($sql);

		return $api->response([
			'success' => true,
			'products' => $products
		]);
	}
}


