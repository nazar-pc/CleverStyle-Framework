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
	cs\Cache,
	cs\Cache\Prefix,
	cs\Config,
	cs\Language,
	cs\Text,
	cs\CRUD,
	cs\Singleton;

/**
 * @method static Attributes instance($check = false)
 */
class Attributes {
	use
		CRUD,
		Singleton;

	protected $data_model = [
		'id'             => 'int',
		'type'           => 'int',
		'title'          => 'text',
		'internal_title' => 'text',
		'value'          => null
	];
	protected $table      = '[prefix]shop_attributes';
	/**
	 * @var Prefix
	 */
	protected $cache;

	protected function construct () {
		$this->cache = new Prefix('Shop/attributes');
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
	 * Get attribute
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
			$data                   = $this->read_simple($id);
			$data['title']          = $this->ml_process($data['title']);
			$data['internal_title'] = $this->ml_process($data['internal_title']);
			$data['value']          = $this->ml_process($data['value']);
			return $data;
		});
	}
	/**
	 * Add new attribute
	 *
	 * @param int    $type
	 * @param string $title
	 * @param string $internal_title
	 * @param int    $value
	 *
	 * @return bool|int Id of created attribute on success of <b>false</> on failure
	 */
	function add ($type, $title, $internal_title, $value) {
		$id = $this->create_simple([
			$type,
			'',
			'',
			''
		]);
		if (!$id) {
			return false;
		}
		return $this->set($id, $type, $title, $internal_title, $value);
	}
	/**
	 * Set data of specified  attribute
	 *
	 * @param int    $id
	 * @param int    $type
	 * @param string $title
	 * @param string $internal_title
	 * @param int    $value Attribute that will be considered as title
	 *
	 * @return bool
	 */
	function set ($id, $type, $title, $internal_title, $value) {
		return $this->update_simple([
			$id,
			$type,
			$this->ml_set('Shop/attributes/title', $id, $title),
			$this->ml_set('Shop/attributes/internal_title', $id, $internal_title),
			$this->ml_set('Shop/attributes/value', $id, $value)
		]);
	}
	/**
	 * Delete specified attribute
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
		$this->ml_del('Shop/attributes/title', $id);
		$this->ml_del('Shop/attributes/internal_title', $id);
		$this->ml_del('Shop/attributes/value', $id);
		unset(
			$this->cache->$id,
			Cache::instance()->{'Shop/categories'}
		);
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
