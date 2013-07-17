<?php
/**
 * @package		Comments
 * @category	modules
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2013, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
namespace	cs\modules\Comments;
use			cs\DB\Accessor,
			h,
			cs\Cache,
			cs\Config,
			cs\Language,
			cs\User,
			cs\Singleton;
class Comments extends Accessor {
	use	Singleton;

	/**
	 * @var string
	 */
	protected $module;
	protected function construct () {
		$this->module	= MODULE;
	}
	/**
	 * Set module (current module assumed by default)
	 *
	 * @param string	$module	Module name
	 */
	function set_module ($module) {
		$this->module	= $module;
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
	 * @param int			$id Comment id
	 *
	 * @return array|bool		Array of comment data on success or <b>false</b> on failure
	 */
	function get ($id) {
		$id	= (int)$id;
		return $this->db()->qf([
			"SELECT
				`id`,
				`parent`,
				`module`,
				`item`,
				`user`,
				`date`,
				`lang`
			FROM `[prefix]comments`
			WHERE
				`id`		= '%s' AND
				`module`	= '%s'
			LIMIT 1",
			$id,
			$this->module
		]);
	}
	/**
	 * Add new comment
	 *
	 * @param int			$item	Item id
	 * @param string		$text	Comment text
	 * @param int			$parent	Parent comment id
	 *
	 * @return array|bool			Array of comment data on success or <b>false</b> on failure
	 */
	function add ($item, $text, $parent = 0) {
		$L		= Language::instance();
		$User	= User::instance();
		$text	= xap($text, true);
		if (!$text) {
			return false;
		}
		$item	= (int)$item;
		$parent	= (int)$parent;
		if (
			$parent != 0 &&
			$this->db_prime()->qfs([
				"SELECT `item`
				FROM `[prefix]comments`
				WHERE
					`id`		= '%s' AND
					`module`	= '%s'
				LIMIT 1",
				$parent,
				$this->module
			]) != $item
		) {
			return false;
		}
		if ($this->db_prime()->q(
			"INSERT INTO `[prefix]comments`
				(
					`parent`,
					`module`,
					`item`,
					`user`,
					`date`,
					`text`,
					`lang`
				)
			VALUES
				(
					'%s',
					'%s',
					'%s',
					'%s',
					'%s',
					'%s',
					'%s'
				)",
			$parent,
			$this->module,
			$item,
			$User->id,
			TIME,
			$text,
			$L->clang
		)) {
			unset(Cache::instance()->{'Comments/'.$this->module.'/'.$item});
			return [
				'id'		=> $this->db_prime()->id(),
				'parent'	=> $parent,
				'module'	=> $this->module,
				'item'		=> $item,
				'user'		=> $User->id,
				'date'		=> TIME,
				'text'		=> $text,
				'lang'		=> $L->clang
			];
		}
		return false;
	}
	/**
	 * Set comment text
	 *
	 * @param int			$id		Comment id
	 * @param string		$text	New comment text
	 *
	 * @return array|bool			Array of comment data on success or <b>false</b> on failure
	 */
	function set ($id, $text) {
		$text	= xap($text, true);
		if (!$text) {
			return false;
		}
		$id				= (int)$id;
		$comment		= $this->get($id);
		if (!$comment) {
			return false;
		}
		if ($this->db_prime()->q(
			"UPDATE `[prefix]comments`
			SET `text` = '%s'
			WHERE
				`id`		= '%s' AND
				`module`	= '%s'
			LIMIT 1",
			$text,
			$id,
			$this->module
		)) {
			unset(Cache::instance()->{'Comments/'.$this->module.'/'.$comment['item']});
			$comment['text']	= $text;
			return $comment;
		}
		return false;
	}
	/**
	 * Delete comment
	 *
	 * @param int	$id	Comment id
	 *
	 * @return bool
	 */
	function del ($id) {
		$id				= (int)$id;
		$comment		= $this->db_prime()->qf([
			"SELECT `p`.`item`, COUNT(`c`.`id`) AS `count`
			FROM `[prefix]comments` AS `p`
			LEFT JOIN `[prefix]comments` AS `c`
			ON `p`.`id` = `c`.`parent`
			WHERE
				`p`.`id`		= '%s' AND
				`p`.`module`	= '%s'
			LIMIT 1",
			$id,
			$this->module
		]);
		if (!$comment || $comment['count']) {
			return false;
		}
		if ($this->db_prime()->q(
			"DELETE FROM `[prefix]comments`
			WHERE
				`id`		= '%s' AND
				`module`	= '%s'
			LIMIT 1",
			$id,
			$this->module
		)) {
			unset(Cache::instance()->{'Comments/'.$this->module.'/'.$comment['item']});
			return true;
		}
		return false;
	}
	/**
	 * Delete all comments of specified item
	 *
	 * @param int	$item	Item id
	 *
	 * @return bool
	 */
	function del_all ($item) {
		$item			= (int)$item;
		if ($this->db_prime()->q(
			"DELETE FROM `[prefix]comments`
			WHERE
				`module`	= '%s' AND
				`item`		= '%s'",
			$this->module,
			$item
		)) {
			unset(Cache::instance()->{'Comments/'.$this->module.'/'.$item});
			return true;
		}
		return false;
	}
	/**
	 * Count of comments for specified item
	 *
	 * @param int	$item	Item id
	 *
	 * @return int
	 */
	function count ($item) {
		$Cache														= Cache::instance();
		$L															= Language::instance();
		$count														= $Cache->{'Comments/'.$this->module.'/'.$item.'/count/'.$L->clang};
		if ($count !== false) {
			return $count;
		}
		$data														= $this->tree_data($item);
		$count														= $this->count_internal($data) ?: 0;
		$Cache->{"Comments/$this->module/$item/count/$L->clang"}	= $count;
		return $count;
	}
	protected function count_internal ($data) {
		if (!is_array($data)) {
			return 0;
		}
		$count	= 0;
		foreach ($data as &$d) {
			$count	+= $this->count_internal($d['comments'], $count) + 1;
		}
		return $count;
	}
	/**
	 * Get comments tree in html format for specified item (look at ::block() method before usage)
	 *
	 * @param int	$item	Item id
	 *
	 * @return string
	 */
	function tree ($item) {
		return $this->tree_html($this->tree_data($item));
	}
	/**
	 * Get comments structure of specified item
	 *
	 * @param int			$item
	 * @param int			$parent
	 *
	 * @return bool|array
	 */
	function tree_data ($item, $parent = 0) {
		$Cache	= Cache::instance();
		$L		= Language::instance();
		if ($parent != 0 || ($comments = $Cache->{'Comments/'.$this->module.'/'.$item.'/'.$L->clang}) === false) {
			$item		= (int)$item;
			$parent		= (int)$parent;
			$comments	= $this->db()->qfa([
				"SELECT
					`id`,
					`parent`,
					`user`,
					`date`,
					`text`,
					`lang`
				FROM `[prefix]comments`
				WHERE
					`parent`	= '%s' AND
					`item`		= '%s' AND
					`lang`		= '%s'",
				$parent,
				$item,
				$L->clang
			]);
			if ($comments) {
				foreach ($comments as &$comment) {
					$comment['comments']	= $this->tree_data($item, $comment['id']);
				}
				unset($comment);
			}
			if ($parent == 0) {
				$Cache->{'Comments/'.$this->module.'/'.$item.'/'.$L->clang}	= $comments;
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
		$content	= '';
		if (is_array($comments) && !empty($comments)) {
			foreach ($comments as $comment) {
				$content	.= h::{'article.cs-comments-comment'}(
					h::a(
						h::{'img.cs-comments-comment-avatar'}([
							'src'	=> $User->get('avatar', $comment['user']) ? h::prepare_url($User->get('avatar', $comment['user']), true) : 'includes/img/guest.gif',
							'alt'	=> $User->username($comment['user']),
							'title'	=> $User->username($comment['user'])
						]),
						[
							'href'			=> path($L->profile).'/'.$User->get('login', $comment['user']),
							'rel'			=> 'author'
						]
					).
					h::{'a.cs-comments-comment-author'}(
						$User->username($comment['user']),
						[
							'href'			=> path($L->profile).'/'.$User->get('login', $comment['user']),
							'rel'			=> 'author'
						]
					).
					h::{'time.cs-comments-comment-date'}(
						date('dmY', TIME) == date('dmY', $comment['date']) ?
							date($L->_time, $comment['date']) : $L->to_locale(date($L->_datetime, $comment['date'])),
						[
							'datetime'		=> date('c', $comment['date'])
						]
					).
					h::{'a.cs-comments-comment-link'}(
						h::icon('link'),
						[
							'href'	=> '#comment_'.$comment['id']
						]
					).
					(
						$comment['parent'] ? h::{'a.cs-comments-comment-parent'}(
							h::icon('arrowreturnthick-1-n'),
							[
								'href'	=> '#comment_'.$comment['parent']
							]
						) : ''
					).
					(
						$User->id == $comment['user'] || $User->admin() ? h::{'icon.cs-comments-comment-edit.cs-pointer'}('pencil') : ''
					).
					(
						!$comment['comments'] &&
						(
							$User->id == $comment['user'] || $User->admin()
						) ? h::{'icon.cs-comments-comment-delete.cs-pointer'}('trash') : ''
					).
					h::{'div.cs-comments-comment-text'}(
						$comment['text']
					).
					(
						$comment['comments'] ? $this->tree_html($comment['comments']) : ''
					),
					[
						'id'	=> 'comment_'.$comment['id']
					]
				);
			}
		}
		return $content;
	}
	/**
	 * Get comments block with comments tree and comments sending form
	 *
	 * @param int		$item	Item id
	 *
	 * @return string
	 */
	function block ($item) {
		$L	= Language::instance();
		return h::{'section#comments.cs-comments-comments'}(
			$L->comments.':'.
			(
				$this->tree($item) ?: h::{'article.cs-blogs-no-comments'}($L->no_comments_yet)
			)
		).
		h::{'p.cs-comments-add-comment'}($L->add_comment.':').
		(
			User::instance()->user() ? h::{'section.cs-comments-comment-write'}(
				h::{'textarea.cs-comments-comment-write-text.cs-wide-textarea.SEDITOR'}(
					'',
					[
						'data-item'		=> $item,
						'data-parent'	=> 0,
						'data-id'		=> 0,
						'data-module'	=> $this->module
					]
				).
				h::br().
				h::{'button.cs-comments-comment-write-send'}(
					$L->send_comment
				).
				h::{'button.cs-comments-comment-write-edit'}(
					$L->save,
					[
						'style'	=>	'display: none'
					]
				).
				h::{'button.cs-comments-comment-write-cancel'}(
					$L->cancel,
					[
						'style'	=>	'display: none'
					]
				)
			) : h::p($L->register_for_comments_sending)
		);
	}
}