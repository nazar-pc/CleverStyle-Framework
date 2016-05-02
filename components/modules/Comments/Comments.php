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
	h,
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
	 * @var Cache
	 */
	protected $cache;
	/**
	 * @var int    Avatar size in px, can be redefined
	 */
	public $avatar_size = 36;

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
	function get ($id) {
		return $this->read($id);
	}
	/**
	 * Add new comment
	 *
	 * @param int    $item   Item id
	 * @param string $module Module name
	 * @param string $text   Comment text
	 * @param int    $parent Parent comment id
	 *
	 * @return false|int
	 */
	function add ($item, $module, $text, $parent = 0) {
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
	function set ($id, $text) {
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
	function del ($id) {
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
	 * @param int    $item   Item id
	 * @param string $module Module name
	 *
	 * @return bool
	 */
	function del_all ($item, $module) {
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
		return $result;
	}
	/**
	 * Count of comments for specified item
	 *
	 * @param int    $item   Item id
	 * @param string $module Module name
	 *
	 * @return int
	 */
	function count ($item, $module) {
		$item = (int)$item;
		$L    = Language::instance();
		return $this->cache->get(
			"$module/$item/count/$L->clang",
			function () use ($item, $module, $L) {
				return $this->search(
					[
						'module'      => $module,
						'item'        => $item,
						'lang'        => $L->clang,
						'total_count' => true
					]
				);
			}
		);
	}
	/**
	 * Get comments tree in html format for specified item (look at ::block() method before usage)
	 *
	 * @param int    $item   Item id
	 * @param string $module Module name
	 *
	 * @return string
	 */
	function tree ($item, $module) {
		return $this->tree_html($this->tree_data($item, $module) ?: []);
	}
	/**
	 * Get comments structure of specified item
	 *
	 * @param int			$item
	 * @param string		$module
	 * @param int			$parent
	 *
	 * @return false|array
	 */
	function tree_data ($item, $module, $parent = 0) {
		$Cache	= $this->cache;
		$L		= Language::instance();
		if (($comments = $Cache->{"$module/$item/$L->clang"}) === false) {
			$item		= (int)$item;
			$parent		= (int)$parent;
			$comments	= $this->db()->qfa(
				"SELECT
					`id`,
					`parent`,
					`user`,
					`date`,
					`text`,
					`lang`
				FROM `[prefix]comments`
				WHERE
					`parent`	= '%d' AND
					`item`		= '%d' AND
					`module`	= '%s' AND
					`lang`		= '%s'",
				$parent,
				$item,
				$module,
				$L->clang
			) ?: [];
			foreach ($comments as &$comment) {
				$comment['comments'] = $this->tree_data($item, $module, $comment['id']);
			}
			unset($comment);
			/**
			 * Cache only root tree data
			 */
			if ($parent == 0) {
				$Cache->{"$module/$item/$L->clang"}	= $comments;
			}
		}
		return $comments;
	}
	/**
	 * Get comments tree in html format for given data structure (usually uses ::tree_data() method)
	 *
	 * @param array[]	$comments
	 *
	 * @return string
	 */
	function tree_html ($comments) {
		$L			= Language::instance();
		$User		= User::instance();
		if (!is_array($comments) || !$comments) {
			return '';
		}
		$content	= '';
		foreach ($comments as $comment) {
			$uniqid		= uniqid('comment_', true);
			$content	.= str_replace($uniqid, $comment['text'], h::{'article.cs-comments-comment'}(
				h::{'img.cs-comments-comment-avatar'}([
					'src'	=> $User->avatar($this->avatar_size, $comment['user']),
					'alt'	=> $User->username($comment['user']),
					'title'	=> $User->username($comment['user'])
				]).
				h::span($User->username($comment['user'])).
				h::{'time.cs-comments-comment-date'}(
					date('dmY', time()) == date('dmY', $comment['date']) ?
						date($L->_time, $comment['date']) : $L->to_locale(date($L->_datetime, $comment['date'])),
					[
						'datetime'		=> date('c', $comment['date'])
					]
				).
				h::{'a.cs-comments-comment-link'}(
					h::icon('anchor'),
					[
						'href'	=> "#comment_$comment[id]"
					]
				).
				(
					$comment['parent'] ? h::{'a.cs-comments-comment-parent'}(
						h::icon('level-up'),
						[
							'href'	=> "#comment_$comment[parent]"
						]
					) : ''
				).
				(
					$User->id == $comment['user'] || $User->admin() ? h::{'icon.cs-comments-comment-edit.cs-cursor-pointer'}('pencil') : ''
				).
				(
					!$comment['comments'] &&
					(
						$User->id == $comment['user'] || $User->admin()
					) ? h::{'icon.cs-comments-comment-delete.cs-cursor-pointer'}('trash-o') : ''
				).
				h::{'div.cs-comments-comment-text'}($uniqid).
				(
					$comment['comments'] ? $this->tree_html($comment['comments']) : ''
				),
				[
					'id'	=> "comment_$comment[id]"
				]
			));
		}
		return $content;
	}
	/**
	 * Get comments block with comments tree and comments sending form
	 *
	 * @param int    $item   Item id
	 * @param string $module Module name
	 *
	 * @return string
	 */
	function block ($item, $module) {
		$L	= Language::prefix('comments_');
		return h::{'section#comments.cs-comments-comments'}(
			$L->comments.':'.
			(
				$this->tree($item, $module) ?: h::{'article.cs-blogs-no-comments'}($L->no_comments_yet)
			)
		).
		h::{'p.cs-comments-add-comment'}("$L->add_comment:").
		(
			User::instance()->user() ? h::{'section.cs-comments-comment-write'}(
				h::{'cs-editor-simple textarea.cs-comments-comment-write-text[is=cs-textarea][autosize]'}(
					'',
					[
						'data-item'		=> $item,
						'data-parent'	=> 0,
						'data-id'		=> 0,
						'data-module'	=> $module
					]
				).
				h::br().
				h::{'button.cs-comments-comment-write-send[is=cs-button]'}(
					$L->send_comment
				).
				h::{'button.cs-comments-comment-write-edit[is=cs-button]'}(
					$L->save,
					[
						'style'	=>	'display: none'
					]
				).
				h::{'button.cs-comments-comment-write-cancel[is=cs-button]'}(
					$L->cancel,
					[
						'style'	=>	'display: none'
					]
				)
			) : h::p($L->register_for_comments_sending)
		);
	}
}
