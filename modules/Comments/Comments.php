<?php
/**
 * @package   Comments
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2011-2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs\modules\Comments;
use
	cs\Cache,
	cs\Config,
	cs\Language,
	cs\Request,
	cs\User,
	cs\CRUD_helpers,
	cs\Singleton;

/**
 * @method static $this instance($check = false)
 */
class Comments {
	use
		CRUD_helpers,
		Singleton;

	/**
	 * @var Cache\Prefix
	 */
	protected $cache;

	protected $data_model = [
		'id'     => 'int:1',
		'parent' => 'int:0',
		'module' => 'text',
		'item'   => 'int:1',
		'user'   => 'int:1',
		'date'   => 'int:1',
		'text'   => 'html',
		'lang'   => 'text'
	];

	protected $table = '[prefix]comments';

	protected function construct () {
		$this->cache = Cache::prefix('Comments');
	}
	/**
	 * Returns database index
	 *
	 * @return int
	 */
	protected function cdb () {
		return Config::instance()->module('Comments')->db('comments');
	}
	/**
	 * Get comment data
	 *
	 * @param int|int[] $id
	 *
	 * @return array|false
	 */
	public function get ($id) {
		return $this->read($id);
	}
	/**
	 * Get comment data with user details and formatted date
	 *
	 * @param int|int[] $id
	 *
	 * @return array|false
	 */
	public function get_extended ($id) {
		if (is_array($id)) {
			return array_map([$this, 'get_extended'], $id);
		}
		$comment                   = $this->get($id);
		$User                      = User::instance();
		$comment['username']       = $User->username($comment['user']);
		$comment['avatar']         = $User->avatar(null, $comment['user']);
		$comment['date_formatted'] = date(Language::instance()->_datetime, $comment['date']);
		$comment['time_formatted'] = date(Language::instance()->_datetime, $comment['date']);
		return $comment;
	}
	/**
	 * @param string $module
	 * @param int    $item
	 *
	 * @return int[]
	 */
	public function get_for_module_item ($module, $item) {
		$search_parameters = [
			'module' => $module,
			'item'   => $item
		];
		return $this->search($search_parameters, 1, PHP_INT_MAX, 'id', true) ?: [];
	}
	/**
	 * @param string $module
	 * @param int    $item
	 *
	 * @return int
	 */
	public function get_for_module_item_count ($module, $item) {
		$search_parameters = [
			'module'      => $module,
			'item'        => $item,
			'total_count' => true
		];
		return $this->search($search_parameters);
	}
	/**
	 * Add new comment
	 *
	 * @param string $module Module name
	 * @param int    $item   Item id
	 * @param string $text   Comment text
	 * @param int    $parent Parent comment id
	 *
	 * @return false|int
	 */
	public function add ($module, $item, $text, $parent = 0) {
		$L    = Language::instance();
		$User = User::instance();
		$text = xap($text, true);
		if (!$text) {
			return false;
		}
		if ($parent) {
			$parent_comment = $this->read($parent);
			if ($parent_comment['item'] != $item || $parent_comment['module'] != $module) {
				return false;
			}
		}
		$id = $this->create($parent, $module, $item, $User->id, time(), $text, $L->clang);
		if ($id) {
			$this->cache->del("$module/$item");
		}
		return $id;
	}
	/**
	 * Set comment text
	 *
	 * @param int    $id
	 * @param string $text
	 *
	 * @return bool
	 */
	public function set ($id, $text) {
		$text = xap($text, true);
		if (!$text) {
			return false;
		}
		$comment = $this->get($id);
		if (!$comment) {
			return false;
		}
		$comment['text'] = $text;
		$result          = $this->update($comment);
		if ($result) {
			$this->cache->del("$comment[module]/$comment[item]");
		}
		return $result;
	}
	/**
	 * Delete comment
	 *
	 * @param int $id
	 *
	 * @return bool
	 */
	public function del ($id) {
		$comment = $this->read($id);
		if (
			!$comment ||
			$this->search(
				[
					'parent'      => $id,
					'total_count' => true
				]
			)
		) {
			return false;
		}
		$result = $this->delete($id);
		if ($result) {
			$this->cache->del("$comment[module]/$comment[item]");
		}
		return $result;
	}
	/**
	 * Delete all comments of specified item
	 *
	 * @param string $module Module name
	 * @param int    $item   Item id
	 *
	 * @return bool
	 */
	public function del_all ($module, $item) {
		$item   = (int)$item;
		$result = $this->db_prime()->q(
			"DELETE FROM `[prefix]comments`
			WHERE
				`module`	= '%s' AND
				`item`		= '%d'",
			$module,
			$item
		);
		if ($result) {
			$this->cache->del("$module/$item");
		}
		return (bool)$result;
	}
}
