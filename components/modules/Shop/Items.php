<?php
/**
 * @package   Shop
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2014, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs\modules\Shop;
use
	cs\Cache\Prefix,
	cs\Config,
	cs\Language,
	cs\Trigger,
	cs\CRUD,
	cs\Singleton;

/**
 * @method static Items instance($check = false)
 */
class Items {
	use
		CRUD,
		Singleton;

	protected $data_model = [
		'id'       => 'int',
		'category' => 'int',
		'price'    => 'float',
		'in_stock' => 'int'
	];
	protected $table      = '[prefix]shop_items';
	/**
	 * @var Prefix
	 */
	protected $cache;

	protected function construct () {
		$this->cache = new Prefix('Shop/items');
	}
	/**
	 * Returns database index
	 *
	 * @return int
	 */
	protected function cdb () {
		return Config::instance()->module('Shop')->db('shop');
	}
	/**
	 * Get item
	 *
	 * @param int|int[] $id
	 *
	 * @return array|bool
	 */
	function get ($id) {
		if (is_array($id)) {
			foreach ($id as &$i) {
				$i = $this->get($i);
			}
			return $id;
		}
		$L  = Language::instance();
		$id = (int)$id;
		return $this->cache->get("$id/$L->clang", function () use ($id, $L) {
			$data               = $this->read_simple($id);
			$data['attributes'] = $this->db()->qfa(
				"SELECT
					`atribute`,
					`numeric_value`,
					`string_value`,
					`text_value`
				FROM `{$this->table}_attributes`
				WHERE
					`id` = $id AND
					(
						`lang`	= '$L->clang' OR
						`lang`	= ''
					)"
			) ?: [];
			$Attributes         = Attributes::instance();
			foreach ($data['attributes'] as $attribute => &$value) {
				$attribute = $Attributes->get($attribute);
				if (!$attribute) {
					unset($data['attributes'][$attribute]);
					continue;
				}
				switch ($attribute['type']) {
					/**
					 * For numeric values and value sets (each value have its own index in set and does not depend on language) value is stored in numeric
					 * column for faster search
					 */
					case Attributes::TYPE_INT_SET:
					case Attributes::TYPE_INT_RANGE:
					case Attributes::TYPE_FLOAT_SET:
					case Attributes::TYPE_FLOAT_RANGE:
					case Attributes::TYPE_RADIO:
					case Attributes::TYPE_STRING_SET:
					case Attributes::TYPE_COLOR_SET:
						$value['value'] = $value['numeric_value'];
						break;
					case Attributes::TYPE_STRING:
						$value['value'] = $value['string_value'];
						break;
					default:
						$value['value'] = $value['text_value'];
						break;
				}
				unset($value['numeric_value'], $value['string_value'], $value['text_value']);
			}
			unset($attribute, $value);
			$data['images'] = $this->db()->qfas(
				"SELECT `image`
				FROM `{$this->table}_images`
				WHERE `id` = $id"
			) ?: [];
			return $data;
		});
	}
	/**
	 * Add new item
	 *
	 * @param int      $category
	 * @param float    $price
	 * @param int      $in_stock
	 * @param array    $attributes
	 * @param string[] $images
	 *
	 * @return bool|int Id of created item on success of <b>false</> on failure
	 */
	function add ($category, $price, $in_stock, $attributes, $images) {
		$id = $this->create_simple([
			$category,
			$price,
			$in_stock
		]);
		if (!$id) {
			return false;
		}
		return $this->set($id, $category, $price, $in_stock, $attributes, $images);
	}
	/**
	 * Set data of specified item
	 *
	 * @param int      $id
	 * @param int      $category
	 * @param float    $price
	 * @param int      $in_stock
	 * @param array    $attributes
	 * @param string[] $images
	 *
	 * @return bool
	 */
	function set ($id, $category, $price, $in_stock, $attributes, $images) {
		$id        = (int)$id;
		$data      = $this->get($id);
		$old_files = $data['images'];
		$new_files = $images;
		$result    = $this->update_simple([
			$id,
			$category,
			$price,
			$in_stock
		]);
		if (!$result) {
			return false;
		}
		$cdb            = $this->db_prime();
		$L              = Language::instance();
		$old_attributes = $cdb->qfas(
			"SELECT `text_value`
			FROM `{$this->table}_attributes`
			WHERE
				`id`			= $id AND
				`lang`			= $L->clang AND
				`text_value`	!= ''"
		);
		foreach ($old_attributes as $old_attribute) {
			$old_files = array_merge($old_files, find_links($old_attribute));
		}
		unset($old_attributes, $old_attribute);
		$cdb->q(
			"DELETE FROM `{$this->table}_attributes`
			WHERE
				`id`	= $id AND
				(
					`lang`	= $L->clang OR
					`lang`	= ''
				)"
		);
		$cdb->q(
			"DELETE FROM `{$this->table}_images`
			WHERE `id` = $id"
		);
		if ($attributes) {
			$Attributes = Attributes::instance();
			foreach ($attributes as $attribute => &$value) {
				$attribute = $Attributes->get($attribute);
				if (!$attribute) {
					unset($attributes[$attribute]);
					continue;
				}
				$numeric_value = 0;
				$string_value  = '';
				$text_value    = '';
				$lang          = '';
				switch ($attribute['type']) {
					/**
					 * For numeric values and value sets (each value have its own index in set and does not depend on language) store value in numeric column
					 * for faster search
					 */
					case Attributes::TYPE_INT_SET:
					case Attributes::TYPE_INT_RANGE:
					case Attributes::TYPE_FLOAT_SET:
					case Attributes::TYPE_FLOAT_RANGE:
					case Attributes::TYPE_RADIO:
					case Attributes::TYPE_STRING_SET:
					case Attributes::TYPE_COLOR_SET:
						$numeric_value = $value;
						break;
					case Attributes::TYPE_STRING:
						$string_value = $value;
						$lang         = $L->clang;
						break;
					default:
						$text_value = $value;
						$new_files  = array_merge($new_files, find_links($value));
						$lang       = $L->clang;
						break;
				}
				$value = [
					$attribute['id'],
					$numeric_value,
					$string_value,
					$text_value,
					$lang
				];
			}
			unset($attribute, $value, $numeric_value, $string_value, $text_value);
			/**
			 * @var array[] $attributes
			 */
			$cdb->insert(
				"INSERT INTO `{$this->table}_attributes`
					(
						`id`,
						`attribute`,
						`numeric_value`,
						`string_value`,
						`text_value`,
						`lang`
					)
				VALUES
					(
						$id,
						'%s',
						'%d',
						'%s',
						'%s',
						'%s'
					)",
				$attributes
			);
		}
		if ($images) {
			foreach ($images as &$image) {
				$image = [$image];
			}
			unset($image);
			/**
			 * @var string[][] $images
			 */
			$cdb->insert(
				"INSERT INTO `{$this->table}_attributes`
					(
						`id`,
						`image`
					)
				VALUES
					(
						$id,
						'%s'
					)",
				$images
			);
		}
		if ($old_files || $new_files) {
			foreach (array_diff($old_files, $new_files) as $file) {
				Trigger::instance()->run(
					'System/upload_files/del_tag',
					[
						'tag'	=> "Shop/items/$id/$L->clang",
						'url'	=> $file
					]
				);
			}
			unset($file);
			foreach (array_diff($new_files, $old_files) as $file) {
				Trigger::instance()->run(
					'System/upload_files/add_tag',
					[
						'tag'	=> "Shop/items/$id/$L->clang",
						'url'	=> $file
					]
				);
			}
			unset($file);
		}
		unset($old_files, $new_files);
		$this->cache->del("$id/$L->clang");
		return true;
	}
	/**
	 * Delete specified item
	 *
	 * @param int $id
	 *
	 * @return bool
	 */
	function del ($id) {
		$id = (int)$id;
		if (!$this->delete_simple($id)) {
			return false;
		}
		$this->db_prime()->q(
			"DELETE FROM `{$this->table}_attributes`
			WHERE `id` = $id"
		);
		$this->db_prime()->q(
			"DELETE FROM `{$this->table}_images`
			WHERE `id` = $id"
		);
		Trigger::instance()->run(
			'System/upload_files/del_tag',
			[
				'tag'	=> "Shop/items/$id%"
			]
		);
		unset($this->cache->$id);
		return true;
	}
}
