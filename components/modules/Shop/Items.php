<?php
/**
 * @package   Shop
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2014-2015, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs\modules\Shop;
use
	cs\Cache\Prefix,
	cs\Config,
	cs\Language,
	cs\Trigger,
	cs\User,
	cs\CRUD,
	cs\Singleton;

/**
 * @method static Items instance($check = false)
 *
 * Provides next triggers:<br>
 *  Shop/Items/get<code>
 *  [
 *   'data' => &$data
 *  ]</code>
 *
 *  Shop/Items/get_for_user<code>
 *  [
 *   'data' => &$data,
 *   'user' => $user
 *  ]</code>
 *
 *  Shop/Items/add<code>
 *  [
 *   'id' => $id
 *  ]</code>
 *
 *  Shop/Items/set<code>
 *  [
 *   'id' => $id
 *  ]</code>
 *
 *  Shop/Items/del<code>
 *  [
 *   'id' => $id
 *  ]</code>
 */
class Items {
	use
		CRUD,
		Singleton;

	const DEFAULT_IMAGE = 'components/modules/Shop/includes/img/no-image.svg';

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
		$L    = Language::instance();
		$id   = (int)$id;
		$data = $this->cache->get("$id/$L->clang", function () use ($id, $L) {
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
			$category           = Categories::instance()->get($data['category']);
			/**
			 * If title attribute is not yet translated to current language
			 */
			if (!in_array($category['title_attribute'], array_column($data['attributes'], 'attribute'))) {
				$data['attributes'][] = $this->db()->qfas(
					"SELECT
						`attribute`,
						`numeric_value`,
						`string_value`,
						`text_value`
					FROM `{$this->table}_attributes`
					WHERE
						`id`		= $id AND
						`attribute`	= $category[title_attribute]
					LIMIT 1"
				);
			}
			/**
			 * If title attribute is not yet translated to current language
			 */
			if ($category['description_attribute'] && !in_array($category['description_attribute'], array_column($data['attributes'], 'attribute'))) {
				$data['attributes'][] = $this->db()->qfas(
					"SELECT
						`attribute`,
						`numeric_value`,
						`string_value`,
						`text_value`
					FROM `{$this->table}_attributes`
					WHERE
						`id`		= $id AND
						`attribute`	= $category[description_attribute]
					LIMIT 1"
				);
			}
			$Attributes = Attributes::instance();
			foreach ($data['attributes'] as $index => &$value) {
				$attribute = $Attributes->get($value['attribute']);
				if ($attribute) {
					$value['value'] = $value[$this->attribute_type_to_value_field($attribute['type'])];
				} else {
					$value['value'] = $value['text_value'];
					if (!strlen($value['value'])) {
						$value['value'] = $value['string_value'];
					}
					if (!strlen($value['value'])) {
						$value['value'] = $value['numeric_value'];
					}
				}
			}
			unset($index, $value, $attribute);
			$data['attributes']  = array_column($data['attributes'], 'value', 'attribute');
			$data['title']       = $data['attributes'][$category['title_attribute']];
			$data['description'] = @$data['attributes'][$category['description_attribute']] ?: '';
			$data['images']      = $this->db()->qfas(
				"SELECT `image`
				FROM `{$this->table}_images`
				WHERE `id` = $id"
			) ?: [];
			$data['videos']      = $this->db()->qfa(
				"SELECT
					`video`,
					`poster`,
					`type`
				FROM `{$this->table}_videos`
				WHERE `id` = $id"
			) ?: [];
			$data['tags']        = $this->db()->qfas(
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
		if (!Trigger::instance()->run('Shop/Items/get', [
			'data' => &$data
		])
		) {
			return false;
		}
		return $data;
	}
	/**
	 * Get item data for specific user (price might be adjusted, some items may be restricted and so on)
	 *
	 * @param $id
	 * @param $user
	 *
	 * @return array|bool
	 */
	function get_for_user ($id, $user = false) {
		if (is_array($id)) {
			foreach ($id as $index => &$i) {
				$i = $this->get_for_user($i, $user);
				if ($i === false) {
					unset($id[$index]);
				}
			}
			return $id;
		}
		$user = (int)$user ?: User::instance()->id;
		$data = $this->get($id);
		if (!Trigger::instance()->run('Shop/Items/get_for_user', [
			'data' => &$data,
			'user' => $user
		])
		) {
			return false;
		}
		return $data;
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
	 * Items search
	 *
	 * @param mixed[] $search_parameters Array in form [attribute => value], [attribute => [value, value]], [attribute => [from => value, to => value]],
	 *                                   [property => value], [tag] or mixed; if `total_count => 1` element is present - total number of found rows will be returned
	 *                                   instead of rows themselves
	 * @param int     $page
	 * @param int     $count
	 * @param string  $order_by
	 * @param bool    $asc
	 *
	 * @return array|bool|string
	 */
	function search ($search_parameters = [], $page = 1, $count = 20, $order_by = 'id', $asc = false) {
		if (!isset($this->data_model[$order_by])) {
			return false;
		}
		$Attributes   = Attributes::instance();
		$L            = Language::instance();
		$joins        = '';
		$join_params  = [];
		$join_index   = 0;
		$where        = [];
		$where_params = [];
		foreach ($search_parameters as $key => $details) {
			if (isset($this->data_model[$key])) { // Property
				$where[]        = "`i`.`$key` = '%s'";
				$where_params[] = $details;
			} elseif (is_numeric($key)) { // Tag
				$joins .=
					"INNER JOIN `{$this->table}_tags` AS `t`
					ON
						`i`.`id`	= `t`.`id` AND
						`t`.`tag`	= '%s'";
				$where_params[] = $details;
			} else { // Attribute
				$field = @$this->attribute_type_to_value_field($Attributes->get($key)['type']);
				if (!$field || empty($details)) {
					continue;
				}
				$join_params[] = $key;
				++$join_index;
				$joins .=
					"INNER JOIN `{$this->table}_attributes` AS `a$join_index`
					ON
						`i`.`id`					= `a$join_index`.`id` AND
						`a$join_index`.`attribute`	= '%s' AND
						(
							`a$join_index`.`id`.`lang`	= '$L->clang' OR
							`a$join_index`.`id`.`lang`	= ''
						)";
				if (is_array($details)) {
					if (isset($details['from']) || isset($details['to'])) {
						if (isset($details['from'])) {
							$joins .= "AND `a$join_index`.`$field`	>= '%s'";
							$join_params[] = $details['from'];
						}
						if (isset($details['to'])) {
							$joins .= "AND `a$join_index`.`$field`	<= '%s'";
							$join_params[] = $details['to'];
						}
					} else {
						$on = [];
						foreach ($details as $d) {
							$on[]          = "`a$join_index`.`$field` = '%s'";
							$join_params[] = $d;
						}
						$on = implode(' OR ', $on);
						$joins .= "AND ($on)";
						unset($on, $d);
					}
				} else {
					switch ($field) {
						case 'numeric_value':
							$joins .= "AND `a$join_index`.`$field` = '%s'";
							break;
						case 'string_value':
							$joins .= "AND `a$join_index`.`$field` LIKE '%s%%'";
							break;
						default:
							$joins .= "AND MATCH (`a$join_index`.`$field`) AGAINST ('%s' IN BOOLEAN MODE) > 0";
					}
					$join_params[] = $details;
				}
			}
		}
		unset($key, $details, $join_index, $field);
		$where = $where ? 'WHERE '.implode(' AND ', $where) : '';
		if (@$search_parameters['total_count']) {
			return $this->db()->qfs([
				"SELECT COUNT(DISTINCT `i`.`id`)
				FROM `$this->table` AS `i`
				$joins
				$where",
				array_merge($join_params, $where_params)
			]);
		} else {
			$where_params[] = ($page - 1) * $count;
			$where_params[] = $count;
			$asc            = $asc ? 'ASC' : 'DESC';
			return $this->db()->qfas([
				"SELECT `i`.`id`
				FROM `$this->table` AS `i`
				$joins
				$where
				GROUP BY `i`.`id`
				ORDER BY `i`.`$order_by` $asc
				LIMIT %d, %d",
				array_merge($join_params, $where_params)
			]);
		}
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
			case Attributes::TYPE_TEXT:
				return 'text_value';
			default:
				return false;
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
	 * @param array[]  $videos
	 * @param string[] $tags
	 *
	 * @return bool|int Id of created item on success of <b>false</> on failure
	 */
	function add ($category, $price, $in_stock, $soon, $listed, $attributes, $images, $videos, $tags) {
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
			Trigger::instance()->run('Shop/Items/add', [
				'id' => $id
			]);
			$this->set($id, $category, $price, $in_stock, $soon, $listed, $attributes, $images, $videos, $tags);
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
	 * @param array[]  $videos
	 * @param string[] $tags
	 *
	 * @return bool
	 */
	function set ($id, $category, $price, $in_stock, $soon, $listed, $attributes, $images, $videos, $tags) {
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
		$videos    = $this->prepare_videos($videos);
		$old_data  = $this->get($id);
		$old_files = array_merge(
			$old_data['images'],
			array_column($old_data['videos'], 'video'),
			array_column($old_data['videos'], 'poster')
		);
		$new_files = array_merge(
			$images,
			$videos ? array_column($videos, 0) : [],
			$videos ? array_column($videos, 1) : []
		);
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
		 * Videos processing
		 */
		$cdb->q(
			"DELETE FROM `{$this->table}_videos`
			WHERE `id` = $id"
		);
		if ($videos) {
			$cdb->insert(
				"INSERT INTO `{$this->table}_videos`
					(
						`id`,
						`video`,
						`poster`,
						`type`
					)
				VALUES
					(
						$id,
						'%s',
						'%s',
						'%s'
					)",
				xap($videos)
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
		Trigger::instance()->run('Shop/Items/set', [
			'id' => $id
		]);
		return true;
	}
	/**
	 * Normalize videos array structure
	 *
	 * @param array[] $videos
	 *
	 * @return array[]
	 */
	protected function prepare_videos ($videos) {
		if (!$videos || !is_array($videos)) {
			return [];
		}
		$videos = array_flip_3d($videos);
		foreach ($videos as $i => &$video) {
			if (!@$video['video']) {
				unset($videos[$i]);
			}
			if (
				$video['type'] == 'iframe' &&
				preg_match('#(http[s]?:)?//[^\s"\'>]+#ims', $video['video'], $match)
			) {
				$video['video'] = $match[0];
			}
			$video = [
				$video['video'],
				$video['poster'],
				$video['type']
			];
		}
		return $videos;
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
			Trigger::instance()->run('Shop/Items/del', [
				'id' => $id
			]);
		}
		return $result;
	}
}
