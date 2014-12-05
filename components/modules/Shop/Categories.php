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
	cs\Text,
	cs\CRUD,
	cs\Singleton;

/**
 * @method static Categories instance($check = false)
 */
class Categories {
	use
		CRUD,
		Singleton;

	protected $data_model = [
		'id'              => 'int',
		'parent'          => 'int',
		'title'           => 'text',
		'description'     => 'html',
		'title_attribute' => 'int',
		'visible'         => 'int:0..1'
	];
	protected $table      = '[prefix]shop_categories';
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
	 * @param int $id
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
			$data                = $this->read_simple($id);
			$data['title']       = $this->ml_process($data['title']);
			$data['description'] = $this->ml_process($data['description']);
			return $data;
		});
	}
	/**
	 * Add new category
	 *
	 * @param int    $parent
	 * @param string $title
	 * @param string $description
	 * @param int    $title_attribute Attribute that will be considered as title
	 * @param int    $visible         `0` or `1`
	 * @param int[]  $attributes      Array of attributes ids used in category
	 *
	 * @return bool|int Id of created category on success of <b>false</> on failure
	 */
	function add ($parent, $title, $description, $title_attribute, $visible, $attributes) {
		$id = $this->create_simple([
			$parent,
			'',
			'',
			$title_attribute,
			$visible
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
	 * @param int    $visible         `0` or `1`
	 * @param int[]  $attributes      Array of attributes ids used in category
	 *
	 * @return bool
	 */
	function set ($id, $parent, $title, $description, $title_attribute, $visible, $attributes) {
		$id     = (int)$id;
		$result = $this->update_simple([
			$id,
			$parent,
			$this->ml_set('Shop/categories/title', $id, $title),
			$this->ml_set('Shop/categories/description', $id, $description),
			$title_attribute,
			$visible
		]);
		if (!$result) {
			return false;
		}
		$cdb	= $this->db_prime();
		$cdb->q(
			"DELETE FROM `{$this->table}_attributes`
			WHERE `id` = $id"
		);
		/**
		 * @var int[][] $attributes
		 */
		foreach ($attributes as &$attribute) {
			$attribute	= [$attribute];
		}
		unset($attribute);
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
	/**
	 * Delete specified post
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
		$this->ml_del('Shop/categories/title', $id);
		$this->ml_del('Shop/categories/description', $id);
		unset($this->cache->$id);
		return true;
	}
	private function ml_process ($text) {
		return Text::instance()->process($this->cdb(), $text, true);
	}
	private function ml_set ($group, $label, $text) {
		return Text::instance()->set($this->cdb(), $group, $label, trim($text));
	}
	private function ml_del ($group, $label) {
		return Text::instance()->del($this->cdb(), $group, $label);
	}
}
