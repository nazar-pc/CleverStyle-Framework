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
		'date'     => 'int',
		'category' => 'int',
		'price'    => 'float',
		'in_stock' => 'int',
		'soon'     => 'int:0..1',
		'listed'   => 'int:0..1'
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
			$data = $this->read_simple($id);
			if (!$data) {
				return false;
			}
			$data['attributes'] = $this->db()->qfa(
				"SELECT
					`attribute`,
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
			$title_attribute    = Categories::instance()->get($data['category'])['title_attribute'];
			/**
			 * If title attribute is not yet translated to current language
			 */
			if (!in_array($title_attribute, array_column($data['attributes'], 'attribute'))) {
				$data['attributes'][] = $this->db()->qfas(
					"SELECT
						`attribute`,
						`numeric_value`,
						`string_value`,
						`text_value`
					FROM `{$this->table}_attributes`
					WHERE
						`id`		= $id AND
						`attribute`	= $title_attribute
					LIMIT 1"
				);
			}
			$Attributes = Attributes::instance();
			foreach ($data['attributes'] as $index => &$value) {
				$attribute = $Attributes->get($value['attribute']);
				if (!$attribute) {
					unset($data['attributes'][$index]);
					continue;
				}
				$value['value'] = $value[$this->attribute_type_to_value_field($attribute['type'])];
			}
			unset($index, $value, $attribute);
			$data['title']      =
				array_column(
					$data['attributes'],
					'value',
					'attribute'
				)[Categories::instance()->get($data['category'])['title_attribute']];
			$data['attributes'] = array_column($data['attributes'], 'value', 'attribute');
			$data['images']     = $this->db()->qfas(
				"SELECT `image`
				FROM `{$this->table}_images`
				WHERE `id` = $id"
			) ?: [];
			$data['tags']       = $this->db()->qfas(
				"SELECT DISTINCT `tag`
				FROM `{$this->table}_tags`
				WHERE
					`id`	= $id AND
					`lang`	= '$L->clang'"
			) ?: [];
			if (!$data['tags']) {
				$l            = $this->db()->qfs(
					"SELECT `lang`
					FROM `{$this->table}_tags`
					WHERE `id` = $id
					LIMIT 1"
				);
				$data['tags'] = $this->db()->qfas(
					"SELECT DISTINCT `tag`
					FROM `{$this->table}_tags`
					WHERE
						`id`	= $id AND
						`lang`	= '$l'"
				) ?: [];
				unset($l);
			}
			$data['tags'] = Tags::instance()->get($data['tags']);
			return $data;
		});
	}
	/**
	 * Get array of all items
	 *
	 * @return int[] Array of items ids
	 */
	function get_all () {
		return $this->cache->get('all', function () {
			return $this->db()->qfas(
				"SELECT `id`
				FROM `$this->table`"
			) ?: [];
		});
	}
	/**
	 * @param int $type
	 *
	 * @return string
	 */
	protected function attribute_type_to_value_field ($type) {
		switch ($type) {
			/**
			 * For numeric values and value sets (each value have its own index in set and does not depend on language) value is stored in numeric
			 * column for faster search
			 */
			case Attributes::TYPE_INT_SET:
			case Attributes::TYPE_INT_RANGE:
			case Attributes::TYPE_FLOAT_SET:
			case Attributes::TYPE_FLOAT_RANGE:
			case Attributes::TYPE_SWITCH:
			case Attributes::TYPE_STRING_SET:
			case Attributes::TYPE_COLOR_SET:
				return 'numeric_value';
			case Attributes::TYPE_STRING:
				return 'string_value';
			default:
				return 'text_value';
		}
	}
	/**
	 * Add new item
	 *
	 * @param int      $category
	 * @param float    $price
	 * @param int      $in_stock
	 * @param int      $soon
	 * @param int      $listed
	 * @param array    $attributes
	 * @param string[] $images
	 * @param string[] $tags
	 *
	 * @return bool|int Id of created item on success of <b>false</> on failure
	 */
	function add ($category, $price, $in_stock, $soon, $listed, $attributes, $images, $tags) {
		$id = $this->create_simple([
			TIME,
			$category,
			$price,
			$in_stock,
			$soon && !$in_stock ? 1 : 0,
			$listed
		]);
		if ($id) {
			unset($this->cache->all);
			$this->set($id, $category, $price, $in_stock, $soon, $listed, $attributes, $images, $tags);
		}
		return $id;
	}
	/**
	 * Set data of specified item
	 *
	 * @param int      $id
	 * @param int      $category
	 * @param float    $price
	 * @param int      $in_stock
	 * @param int      $soon
	 * @param int      $listed
	 * @param array    $attributes
	 * @param string[] $images
	 * @param string[] $tags
	 *
	 * @return bool
	 */
	function set ($id, $category, $price, $in_stock, $soon, $listed, $attributes, $images, $tags) {
		$id   = (int)$id;
		$data = $this->get($id);
		if (!$data) {
			return false;
		}
		$result = $this->update_simple([
			$id,
			$data['date'],
			$category,
			$price,
			$in_stock,
			$soon && !$in_stock ? 1 : 0,
			$listed
		]);
		if (!$result) {
			return false;
		}
		$images    = array_filter($images, function ($image) {
			return filter_var($image, FILTER_VALIDATE_URL);
		});
		$old_files = $this->get($id)['images'];
		$new_files = $images;
		$cdb       = $this->db_prime();
		/**
		 * Attributes processing
		 */
		$L              = Language::instance();
		$old_attributes = $cdb->qfas(
			"SELECT `text_value`
			FROM `{$this->table}_attributes`
			WHERE
				`id`			= $id AND
				`lang`			= '$L->clang' AND
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
					`lang`	= '$L->clang' OR
					`lang`	= ''
				)"
		);
		if ($attributes) {
			$Attributes      = Attributes::instance();
			$title_attribute = Categories::instance()->get($category)['title_attribute'];
			foreach ($attributes as $attribute => &$value) {
				$attribute = $Attributes->get($attribute);
				if (!$attribute && $attribute != '0') {
					unset($attributes[$attribute]);
					continue;
				}
				$value_type = [
					'numeric' => 0,
					'string'  => '',
					'text'    => ''
				];
				$lang       = '';
				switch ($this->attribute_type_to_value_field($attribute['type'])) {
					case 'numeric_value':
						$value_type['numeric'] = $value;
						break;
					case 'string_value':
						$value_type['string'] = xap($value);
						/**
						 * Multilingual feature only for title attribute
						 */
						if ($attribute['id'] == $title_attribute) {
							$lang = $L->clang;
						}
						break;
					case 'text_value':
						$value_type['text'] = xap($value, true, true);
						$new_files          = array_merge($new_files, find_links($value_type['text']));
						$lang               = $L->clang;
						break;
				}
				$value = [
					$attribute['id'],
					$value_type['numeric'],
					$value_type['string'],
					$value_type['text'],
					$lang
				];
			}
			unset($title_attribute, $attribute, $value, $value_type);
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
		/**
		 * Images processing
		 */
		$cdb->q(
			"DELETE FROM `{$this->table}_images`
			WHERE `id` = $id"
		);
		if ($images) {
			$cdb->insert(
				"INSERT INTO `{$this->table}_images`
					(
						`id`,
						`image`
					)
				VALUES
					(
						$id,
						'%s'
					)",
				xap($images)
			);
		}
		/**
		 * Cleaning old files and registering new ones
		 */
		if ($old_files || $new_files) {
			foreach (array_diff($old_files, $new_files) as $file) {
				Trigger::instance()->run(
					'System/upload_files/del_tag',
					[
						'tag' => "Shop/items/$id/$L->clang",
						'url' => $file
					]
				);
			}
			unset($file);
			foreach (array_diff($new_files, $old_files) as $file) {
				Trigger::instance()->run(
					'System/upload_files/add_tag',
					[
						'tag' => "Shop/items/$id/$L->clang",
						'url' => $file
					]
				);
			}
			unset($file);
		}
		unset($old_files, $new_files);
		/**
		 * Tags processing
		 */
		$cdb->q(
			"DELETE FROM `{$this->table}_tags`
			WHERE
				`id`	= $id AND
				`lang`	= '$L->clang'"
		);
		$Tags = Tags::instance();
		$tags = array_unique($tags);
		$tags = $Tags->process($tags);
		$cdb->insert(
			"INSERT INTO `{$this->table}_tags`
				(`id`, `tag`, `lang`)
			VALUES
				($id, '%d', '$L->clang')",
			$tags
		);
		unset(
			$this->cache->{"$id/$L->clang"},
			$this->cache->all
		);
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
		$id     = (int)$id;
		$result = $this->delete_simple($id);
		if ($result) {
			$this->db_prime()->q([
				"DELETE FROM `{$this->table}_attributes`
				WHERE `id` = $id",
				"DELETE FROM `{$this->table}_images`
				WHERE `id` = $id",
				"DELETE FROM `{$this->table}_tags`
				WHERE `id` = $id"
			]);
			Trigger::instance()->run(
				'System/upload_files/del_tag',
				[
					'tag' => "Shop/items/$id%"
				]
			);
			unset(
				$this->cache->$id,
				$this->cache->all
			);
		}
		return $result;
	}
}
