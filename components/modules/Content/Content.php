<?php
/**
 * @package        Content
 * @category       modules
 * @author         Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright      Copyright (c) 2014-2015, Nazar Mokrynskyi
 * @license        MIT License, see license.txt
 */
namespace cs\modules\Content;

use
	cs\Cache\Prefix,
	cs\Config,
	cs\Event,
	cs\Language,
	cs\Text,
	cs\CRUD,
	cs\Singleton;

/**
 * @method static Content instance($check = false)
 */
class Content {
	use
		Singleton,
		CRUD;

	protected $data_model = [
		'key'     => 'text',
		'title'   => 'text',
		'content' => null, //Is set in constructor
		'type'    => 'set:text,html'
	];
	protected $table      = '[prefix]content';
	/**
	 * @var Prefix
	 */
	protected $cache;

	protected function construct () {
		/**
		 * Disable filtering of content
		 */
		$this->data_model['content'] = function ($data) {
			return $data;
		};
		$this->cache                 = new Prefix('Content');
	}
	/**
	 * @inheritdoc
	 */
	protected function cdb () {
		return Config::instance()->module('Content')->db('content');
	}
	/**
	 * Add new content
	 *
	 * @param string $key     Key associated with content, works like id
	 * @param string $title   Content title
	 * @param string $content Content itself
	 * @param string $type    Type of content: <b>text</b> or <b>html</b>. Influences on editor type
	 *
	 * @return bool
	 */
	function add ($key, $title, $content, $type) {
		$key = str_replace(['/', '?', '#', '"', '<', '>'], '_', $key);
		preg_match_all('/"(http[s]?:\/\/.*)"/Uims', $content, $new_files);
		$new_files = isset($new_files[1]) ? $new_files[1] : [];
		$cache_key = md5($key).'/'.Language::instance()->clang;
		if ($new_files) {
			foreach ($new_files as $file) {
				Event::instance()->fire(
					'System/upload_files/add_tag',
					[
						'tag' => "Content/$cache_key",
						'url' => $file
					]
				);
			}
			unset($file);
		}
		unset($new_files);
		$title   = $this->ml_set('Content/title', $key, xap($title));
		$content = $this->ml_set('Content/content', $key, $content);
		return $this->create_simple([
			$key,
			$title,
			$content,
			$type
		]);
	}
	/**
	 * Get content
	 *
	 * @param string|string[] $key
	 *
	 * @return bool|mixed
	 */
	function get ($key) {
		if (is_array($key)) {
			foreach ($key as &$k) {
				$k = $this->get($k);
			}
			return $key;
		}
		$cache_key = md5($key).'/'.Language::instance()->clang;
		return $this->cache->get($cache_key, function () use ($key) {
			$data = $this->read_simple($key);
			if (!$data) {
				return false;
			}
			$data['title']   = $this->ml_process($data['title']);
			$data['content'] = $this->ml_process($data['content']);
			return $data;
		});
	}
	/**
	 * Get keys of all content items
	 *
	 * @return int[]|bool
	 */
	function get_all () {
		return $this->db()->qfas(
			"SELECT `key`
			FROM `$this->table`
			ORDER BY `key` ASC"
		);
	}
	/**
	 * Set content
	 *
	 * @param string $key     Key associated with content, works like id
	 * @param string $title   Content title
	 * @param string $content Content itself
	 * @param string $type    Type of content: <b>text</b> or <b>html</b>. Influences on editor type
	 *
	 * @return bool
	 */
	function set ($key, $title, $content, $type) {
		$data = $this->get($key);
		preg_match_all('/"(http[s]?:\/\/.*)"/Uims', $data['content'], $old_files);
		preg_match_all('/"(http[s]?:\/\/.*)"/Uims', $content, $new_files);
		$old_files = isset($old_files[1]) ? $old_files[1] : [];
		$new_files = isset($new_files[1]) ? $new_files[1] : [];
		$cache_key = md5($key).'/'.Language::instance()->clang;
		if ($old_files || $new_files) {
			foreach (array_diff($old_files, $new_files) as $file) {
				Event::instance()->fire(
					'System/upload_files/del_tag',
					[
						'tag' => "Content/$cache_key",
						'url' => $file
					]
				);
			}
			unset($file);
			foreach (array_diff($new_files, $old_files) as $file) {
				Event::instance()->fire(
					'System/upload_files/add_tag',
					[
						'tag' => "Content/$cache_key",
						'url' => $file
					]
				);
			}
			unset($file);
		}
		unset($old_files, $new_files);
		$title   = $this->ml_set('Content/title', $key, xap($title));
		$content = $this->ml_set('Content/content', $key, $content);
		$result  = $this->update_simple([
			$key,
			$title,
			$content,
			$type
		]);
		if ($result) {
			unset($this->cache->$cache_key);
			return true;
		}
		return false;
	}
	/**
	 * Delete content
	 *
	 * @param string $key
	 *
	 * @return bool
	 */
	function del ($key) {
		if ($this->delete_simple($key)) {
			$this->ml_del('Content/title', $key);
			$this->ml_del('Content/content', $key);
			$cache_key = md5($key);
			Event::instance()->fire(
				'System/upload_files/del_tag',
				[
					'tag' => "Content/$cache_key%"
				]
			);
			unset($this->cache->$cache_key);
			return true;
		}
		return false;
	}
	private function ml_process ($text) {
		return Text::instance()->process($this->cdb(), $text, false, true);
	}
	private function ml_set ($group, $label, $text) {
		return Text::instance()->set($this->cdb(), $group, $label, $text);
	}
	private function ml_del ($group, $label) {
		return Text::instance()->del($this->cdb(), $group, $label);
	}
}
