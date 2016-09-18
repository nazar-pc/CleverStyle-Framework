<?php
/**
 * @package   Shop
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2014-2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs\modules\Shop;
use
	cs\Cache\Prefix,
	cs\Config,
	cs\Event,
	cs\Language,
	cs\User,
	cs\CRUD_helpers,
	cs\Singleton;

/**
 * Provides next events:<br>
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
 *
 * @method static $this instance($check = false)
 */
class Items {
	use CRUD_helpers {
		search as crud_search;
	}
	use
		Singleton;

	const DEFAULT_IMAGE = 'modules/Shop/includes/img/no-image.svg';

	protected $data_model                  = [
		'id'         => 'int:0',
		'date'       => 'int:0',
		'category'   => 'int:0',
		'price'      => 'float',
		'in_stock'   => 'int:0',
		'soon'       => 'int:0..1',
		'listed'     => 'int:0..1',
		'attributes' => [
			'data_model' => [
				'id'            => 'int:0',
				'attribute'     => 'int:0',
				'numeric_value' => 'float',
				'string_value'  => 'text',
				'text_value'    => 'html',
				'lang'          => 'text' // Some attributes are language-dependent, some aren't, so we'll handle that manually
			]
		],
		'images'     => [
			'data_model' => [
				'id'    => 'int:0',
				'image' => 'text'
			]
		],
		'videos'     => [
			'data_model' => [
				'id'     => 'int:0',
				'video'  => 'text',
				'poster' => 'text',
				'type'   => 'text'
			]
		],
		'tags'       => [
			'data_model'     => [
				'id'  => 'int:0',
				'tag' => 'int:0'
			],
			'language_field' => 'lang'
		]
	];
	protected $table                       = '[prefix]shop_items';
	protected $data_model_files_tag_prefix = 'Shop/items';
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
	 * @return array|false
	 */
	public function get ($id) {
		if (is_array($id)) {
			foreach ($id as &$i) {
				$i = $this->get($i);
			}
			return $id;
		}
		$L    = Language::instance();
		$id   = (int)$id;
		$data = $this->cache->get(
			"$id/$L->clang",
			function () use ($id, $L) {
				$data = $this->read($id);
				if (!$data) {
					return false;
				}
				$data['attributes']  = $this->read_attributes_processing($data['attributes'], $L->clang);
				$category            = Categories::instance()->get($data['category']);
				$data['title']       = $data['attributes'][$category['title_attribute']];
				$data['description'] = @$data['attributes'][$category['description_attribute']] ?: '';
				$data['tags']        = $this->read_tags_processing($data['tags']);
				return $data;
			}
		);
		if (!Event::instance()->fire(
			'Shop/Items/get',
			[
				'data' => &$data
			]
		)
		) {
			return false;
		}
		return $data;
	}
	/**
	 * Transform normalized attributes structure back into simple initial structure
	 *
	 * @param array  $attributes
	 * @param string $clang
	 *
	 * @return array
	 */
	protected function read_attributes_processing ($attributes, $clang) {
		/**
		 * Select language-independent attributes and ones that are set for current language
		 */
		$filtered_attributes = array_filter(
			$attributes,
			function ($attribute) use ($clang) {
				return !$attribute['lang'] || $attribute['lang'] == $clang;
			}
		);
		$existing_attributes = array_column($filtered_attributes, 'attribute');
		/**
		 * Now fill other existing attributes that are missing for current language
		 */
		foreach ($attributes as $attribute) {
			if (!in_array($attribute['attribute'], $existing_attributes)) {
				$existing_attributes[] = $attribute['attribute'];
				$filtered_attributes[] = $attribute;
			}
		}
		/**
		 * We have attributes of different types, so, here is normalization for that
		 */
		$Attributes = Attributes::instance();
		foreach ($filtered_attributes as &$value) {
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
		return array_column($filtered_attributes, 'value', 'attribute');
	}
	/**
	 * Transform tags ids back into array of strings
	 *
	 * @param int[] $tags
	 *
	 * @return string[]
	 */
	protected function read_tags_processing ($tags) {
		return array_column(Tags::instance()->get($tags) ?: [], 'text');
	}
	/**
	 * Get item data for specific user (price might be adjusted, some items may be restricted and so on)
	 *
	 * @param int|int[] $id
	 * @param bool|int  $user
	 *
	 * @return array|false
	 */
	public function get_for_user ($id, $user = false) {
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
		if (!Event::instance()->fire(
			'Shop/Items/get_for_user',
			[
				'data' => &$data,
				'user' => $user
			]
		)
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
	public function get_all () {
		return $this->cache->get(
			'all',
			function () {
				return $this->crud_search([], 1, PHP_INT_MAX, 'id', true) ?: [];
			}
		);
	}
	/**
	 * Items search
	 *
	 * @param mixed[] $search_parameters Array in form [attribute => value], [attribute => [value, value]], [attribute => [from => value, to => value]],
	 *                                   [property => value], [tag] or mixed; if `total_count => 1` element is present - total number of found rows will be
	 *                                   returned instead of rows themselves
	 * @param int     $page
	 * @param int     $count
	 * @param string  $order_by
	 * @param bool    $asc
	 *
	 * @return array|false|int
	 */
	public function search ($search_parameters = [], $page = 1, $count = 20, $order_by = 'id', $asc = false) {
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
				$where[]        = "`i`.`$key` = ?";
				$where_params[] = $details;
			} elseif (is_numeric($key)) { // Tag
				$joins .=
					"INNER JOIN `{$this->table}_tags` AS `t`
					ON
						`i`.`id`	= `t`.`id` AND
						`t`.`tag`	= ?";
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
						`a$join_index`.`attribute`	= ? AND
						(
							`a$join_index`.`lang`	= '$L->clang' OR
							`a$join_index`.`lang`	= ''
						)";
				if (is_array($details)) {
					if (isset($details['from']) || isset($details['to'])) {
						/** @noinspection NotOptimalIfConditionsInspection */
						if (isset($details['from'])) {
							$joins .= "AND `a$join_index`.`$field`	>= ?";
							$join_params[] = $details['from'];
						}
						/** @noinspection NotOptimalIfConditionsInspection */
						if (isset($details['to'])) {
							$joins .= "AND `a$join_index`.`$field`	<= ?";
							$join_params[] = $details['to'];
						}
					} else {
						$on = [];
						foreach ($details as $d) {
							$on[]          = "`a$join_index`.`$field` = ?";
							$join_params[] = $d;
						}
						$on = implode(' OR ', $on);
						$joins .= "AND ($on)";
						unset($on, $d);
					}
				} else {
					switch ($field) {
						case 'numeric_value':
							$joins .= "AND `a$join_index`.`$field` = ?";
							$join_params[] = $details;
							break;
						case 'string_value':
							$joins .= "AND `a$join_index`.`$field` LIKE ?";
							$join_params[] = $details.'%';
							break;
						default:
							$joins .= "AND MATCH (`a$join_index`.`$field`) AGAINST (? IN BOOLEAN MODE) > 0";
							$join_params[] = $details;
					}
				}
			}
		}
		return $this->search_do('i', @$search_parameters['total_count'], $where, $where_params, $joins, $join_params, $page, $count, $order_by, $asc);
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
	 * @return false|int Id of created item on success of <b>false</> on failure
	 */
	public function add ($category, $price, $in_stock, $soon, $listed, $attributes, $images, $videos, $tags) {
		$L  = Language::instance();
		$id = $this->create(
			time(),
			$category,
			$price,
			$in_stock,
			$soon && !$in_stock ? 1 : 0,
			$listed,
			$this->prepare_attributes($attributes, $category, $L->clang),
			$this->prepare_images($images),
			$this->prepare_videos($videos),
			$this->prepare_tags($tags)
		);
		if ($id) {
			unset($this->cache->all);
			Event::instance()->fire(
				'Shop/Items/add',
				[
					'id' => $id
				]
			);
		}
		return $id;
	}
	/**
	 * Normalize attributes array structure
	 *
	 * @param array  $attributes
	 * @param int    $category
	 * @param string $clang
	 *
	 * @return array
	 */
	protected function prepare_attributes ($attributes, $category, $clang) {
		$Attributes      = Attributes::instance();
		$title_attribute = Categories::instance()->get($category)['title_attribute'];
		foreach ($attributes as $attribute => &$value) {
			$attribute_data = $Attributes->get($attribute);
			if (!$attribute_data) {
				unset($attributes[$attribute]);
				continue;
			}
			$value_type = [
				'numeric' => 0,
				'string'  => '',
				'text'    => ''
			];
			$lang       = '';
			switch ($this->attribute_type_to_value_field($attribute_data['type'])) {
				case 'numeric_value':
					$value_type['numeric'] = $value;
					break;
				case 'string_value':
					$value_type['string'] = xap($value);
					/**
					 * Multilingual feature only for title attribute
					 */
					if ($attribute_data['id'] == $title_attribute) {
						$lang = $clang;
					}
					break;
				case 'text_value':
					$value_type['text'] = xap($value, true, true);
					$lang               = $clang;
					break;
			}
			$value = [
				$attribute_data['id'],
				$value_type['numeric'],
				$value_type['string'],
				$value_type['text'],
				$lang
			];
		}
		return array_values($attributes);
	}
	/**
	 * Filter images to remove non-URL elements
	 *
	 * @param array $images
	 *
	 * @return array
	 */
	protected function prepare_images ($images) {
		return array_filter(
			$images,
			function ($image) {
				return filter_var($image, FILTER_VALIDATE_URL);
			}
		);
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
	 * Transform array of string tags into array of their ids
	 *
	 * @param string[] $tags
	 *
	 * @return int[]
	 */
	protected function prepare_tags ($tags) {
		return Tags::instance()->add($tags) ?: [];
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
	public function set ($id, $category, $price, $in_stock, $soon, $listed, $attributes, $images, $videos, $tags) {
		$id   = (int)$id;
		$data = $this->get($id);
		if (!$data) {
			return false;
		}
		$L      = Language::instance();
		$result = $this->update(
			$id,
			$data['date'],
			$category,
			$price,
			$in_stock,
			$soon && !$in_stock ? 1 : 0,
			$listed,
			$this->prepare_attributes($attributes, $category, $L->clang),
			$this->prepare_images($images),
			$this->prepare_videos($videos),
			$this->prepare_tags($tags)
		);
		if ($result) {
			/**
			 * Attributes processing
			 */
			unset(
				$this->cache->{"$id/$L->clang"},
				$this->cache->all
			);
			Event::instance()->fire(
				'Shop/Items/set',
				[
					'id' => $id
				]
			);
		}
		return $result;
	}
	/**
	 * Delete specified item
	 *
	 * @param int $id
	 *
	 * @return bool
	 */
	public function del ($id) {
		$id     = (int)$id;
		$result = $this->delete($id);
		if ($result) {
			unset(
				$this->cache->$id,
				$this->cache->all
			);
			Event::instance()->fire(
				'Shop/Items/del',
				[
					'id' => $id
				]
			);
		}
		return $result;
	}
}
