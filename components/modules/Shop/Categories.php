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
 * @method static Categories instance($check = false)
 */
class Categories {
	use
		CRUD,
		Singleton;

	const VISIBLE   = 1;
	const INVISIBLE = 0;

	protected $data_model          = [
		'id'                    => 'int',
		'parent'                => 'int',
		'title'                 => 'ml:text',
		'description'           => 'ml:html',
		'title_attribute'       => 'int',
		'description_attribute' => 'int',
		'image'                 => 'string',
		'visible'               => 'int:0..1'
	];
	protected $data_model_ml_group = 'Shop/categories';
	protected $table               = '[prefix]shop_categories';
	/**
	 * @var Prefix
	 */
	protected $cache;

	protected function construct () {
		$this->cache = new Prefix('Shop/categories');
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
	 * Get category
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
		return $this->cache->get("$id/$L->clang", function () use ($id) {
			$data               = $this->read_simple($id);
			$data['attributes'] = $this->db()->qfas(
				"SELECT `attribute`
				FROM `{$this->table}_attributes`
				WHERE `id` = $id"
			);
			$data['attributes'] = $this->clean_nonexistent_attributes($data['attributes']);
			return $data;
		});
	}
	/**
	 * Get array of all categories
	 *
	 * @return int[] Array of categories ids
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
	 * @param int[] $attributes
	 *
	 * @return int[]
	 */
	protected function clean_nonexistent_attributes ($attributes) {
		if (!$attributes) {
			return [];
		}
		$Attributes = Attributes::instance();
		/**
		 * Remove nonexistent attributes
		 */
		foreach ($attributes as $i => &$attribute) {
			if (!$Attributes->get($attribute)) {
				unset($attributes[$i]);
			}
		}
		return $attributes;
	}
	/**
	 * Add new category
	 *
	 * @param int    $parent
	 * @param string $title
	 * @param string $description
	 * @param int    $title_attribute       Attribute that will be considered as title
	 * @param int    $description_attribute Attribute that will be considered as description
	 * @param string $image
	 * @param int    $visible               `Categories::VISIBLE` or `Categories::INVISIBLE`
	 * @param int[]  $attributes            Array of attributes ids used in category
	 *
	 * @return bool|int Id of created category on success of <b>false</> on failure
	 */
	function add ($parent, $title, $description, $title_attribute, $description_attribute, $image, $visible, $attributes) {
		$id = $this->create_simple([
			$parent,
			'',
			'',
			0,
			0,
			'',
			static::INVISIBLE
		]);
		if ($id) {
			unset($this->cache->all);
			$this->set($id, $parent, $title, $description, $title_attribute, $description_attribute, $image, $visible, $attributes);
		}
		return $id;
	}
	/**
	 * Set data of specified category
	 *
	 * @param int    $id
	 * @param int    $parent
	 * @param string $title
	 * @param string $description
	 * @param int    $title_attribute       Attribute that will be considered as title
	 * @param int    $description_attribute Attribute that will be considered as description
	 * @param string $image
	 * @param int    $visible               `Categories::VISIBLE` or `Categories::INVISIBLE`
	 * @param int[]  $attributes            Array of attributes ids used in category
	 *
	 * @return bool
	 */
	function set ($id, $parent, $title, $description, $title_attribute, $description_attribute, $image, $visible, $attributes) {
		$id   = (int)$id;
		$data = $this->read_simple($id);
		if (!$data) {
			return false;
		}
		$attributes = $this->clean_nonexistent_attributes($attributes);
		$result     = $this->update_simple([
			$id,
			$parent,
			trim($title),
			trim($description),
			in_array($title_attribute, $attributes) ? $title_attribute : $attributes[0],
			in_array($description_attribute, $attributes) || $description == 0 ? $description_attribute : $attributes[0],
			$image,
			$visible
		]);
		if (!$result) {
			return false;
		}
		if ($data['image'] != $image) {
			Trigger::instance()->run(
				'System/upload_files/del_tag',
				[
					'tag' => "Shop/categories/$id",
					'url' => $data['image']
				]
			);
			Trigger::instance()->run(
				'System/upload_files/add_tag',
				[
					'tag' => "Shop/categories/$id",
					'url' => $image
				]
			);
		}
		$cdb = $this->db_prime();
		$cdb->q(
			"DELETE FROM `{$this->table}_attributes`
			WHERE `id` = $id"
		);
		if ($attributes) {
			$cdb->insert(
				"INSERT INTO `{$this->table}_attributes`
					(
						`id`,
						`attribute`
					)
				VALUES
					(
						$id,
						'%s'
					)",
				$attributes
			) ?: [];
		}
		$L = Language::instance();
		unset(
			$this->cache->{"$id/$L->clang"},
			$this->cache->all
		);
		return true;
	}
	/**
	 * Delete specified category
	 *
	 * @param int $id
	 *
	 * @return bool
	 */
	function del ($id) {
		$id     = (int)$id;
		$result = $this->delete_simple($id);
		if ($result) {
			$this->db_prime()->q(
				"DELETE FROM `{$this->table}_attributes`
				WHERE `id` = $id"
			);
			Trigger::instance()->run(
				'System/upload_files/del_tag',
				[
					'tag' => "Shop/categories/$id"
				]
			);
			// TODO do something with items in category on removal
			unset(
				$this->cache->$id,
				$this->cache->all
			);
		}
		return $result;
	}
}
