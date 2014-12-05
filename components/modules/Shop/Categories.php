<?php
/**
 * @package        Shop
 * @category       modules
 * @author         Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright      Copyright (c) 2014, Nazar Mokrynskyi
 * @license        MIT License, see license.txt
 */
namespace cs\modules\Shop;
use
	cs\Cache\Prefix,
	cs\Config,
	cs\Language,
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

	protected $data_model = [
		'id'              => 'int',
		'parent'          => 'int',
		'title'           => 'ml:text',
		'description'     => 'ml:html',
		'title_attribute' => 'int',
		'visible'         => 'int:0..1'
	];
	protected $table      = '[prefix]shop_categories';
	/**
	 * @var Prefix
	 */
	protected $cache;

	protected function construct () {
		$this->cache               = new Prefix('Shop/categories');
		$this->data_model_ml_group = 'Shop/categories';
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
				"SELECT `atribute`
				FROM `{$this->table}_attributes`
				WHERE `id` = $id"
			);
			$data['attributes'] = $this->clean_nonexistent_attributes($data['attributes']);
			return $data;
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
	 * @param int    $title_attribute Attribute that will be considered as title
	 * @param int    $visible         `Categories::VISIBLE` or `Categories::INVISIBLE`
	 * @param int[]  $attributes      Array of attributes ids used in category
	 *
	 * @return bool|int Id of created category on success of <b>false</> on failure
	 */
	function add ($parent, $title, $description, $title_attribute, $visible, $attributes) {
		$id = $this->create_simple([
			$parent,
			'',
			'',
			[],
			static::INVISIBLE
		]);
		if (!$id) {
			return false;
		}
		return $this->set($id, $parent, $title, $description, $title_attribute, $visible, $attributes);
	}
	/**
	 * Set data of specified  category
	 *
	 * @param int    $id
	 * @param int    $parent
	 * @param string $title
	 * @param string $description
	 * @param int    $title_attribute Attribute that will be considered as title
	 * @param int    $visible         `Categories::VISIBLE` or `Categories::INVISIBLE`
	 * @param int[]  $attributes      Array of attributes ids used in category
	 *
	 * @return bool
	 */
	function set ($id, $parent, $title, $description, $title_attribute, $visible, $attributes) {
		$id         = (int)$id;
		$attributes = $this->clean_nonexistent_attributes($attributes);
		$result     = $this->update_simple([
			$id,
			$parent,
			trim($title),
			trim($description),
			in_array($title_attribute, $attributes) ? $title_attribute : $attributes[0],
			$visible
		]);
		if (!$result) {
			return false;
		}
		$cdb = $this->db_prime();
		$cdb->q(
			"DELETE FROM `{$this->table}_attributes`
			WHERE `id` = $id"
		);
		if ($attributes) {
			foreach ($attributes as &$attribute) {
				$attribute = [$attribute];
			}
			unset($attribute);
			/**
			 * @var int[][] $attributes
			 */
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
			);
		}
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
		$id = (int)$id;
		if (!$this->delete_simple($id)) {
			return false;
		}
		$this->db_prime()->q(
			"DELETE FROM `{$this->table}_attributes`
			WHERE `id` = $id"
		);
		// TODO do something with items in category on removal
		unset($this->cache->$id);
		return true;
	}
}
