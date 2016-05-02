<?php
/**
 * @package   Disqus
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2013-2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs\modules\Disqus;
use
	h,
	cs\Config,
	cs\Page,
	cs\Request,
	cs\Singleton;

/**
 * @method static $this instance($check = false)
 */
class Comments {
	use	Singleton;

	/**
	 * @var string
	 */
	protected	$shortname;

	protected function construct () {
		$this->shortname	= Config::instance()->module('Disqus')->shortname;
	}
	/**
	 * Count of comments for specified item
	 *
	 * @param int    $item   Item id
	 * @param string $module Module name
	 *
	 * @return string HTML snipped that will be replaced with actual count on frontend
	 */
	function count ($item, $module) {
		if (!$this->shortname) {
			return '';
		}
		$this->count_js();
		Page::instance()->js(
			"disqus_count_items.push('".str_replace("'", "'", "$module/$item")."');",
			'code'
		);
		return h::{'span.cs-disqus-comments-count'}([
			'data-identifier'=> "$module/$item"
		]);
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
		if (!$this->shortname) {
			return '';
		}
		$this->block_js($item, $module);
		return '<div id="disqus_thread"></div>';
	}
	protected function count_js () {
		static	$added	= false;
		if ($added) {
			return;
		}
		$added		= true;
		Page::instance()->js(
			"var disqus_shortname = '$this->shortname';
if (!window.disqus_count_items) { window.disqus_count_items = []; }",
			'code'
		);
	}
	protected function block_js ($item, $module) {
		static	$added	= false;
		if ($added) {
			return;
		}
		$added		= true;
		Page::instance()->js(
			"var disqus_shortname = '$this->shortname', disqus_identifier = '".str_replace("'", "'", "$module/$item")."';",
			'code'
		);
	}
}
