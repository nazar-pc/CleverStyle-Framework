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
	cs\Singleton;

class Disqus {
	use	Singleton;

	/**
	 * @var string
	 */
	protected	$module;
	/**
	 * @var string
	 */
	protected	$shortname;

	protected function construct () {
		$this->module		= current_module();
		$this->shortname	= Config::instance()->module('Disqus')->shortname;
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
	 * Count of comments for specified item
	 *
	 * @param int	$item	Item id
	 *
	 * @return int
	 */
	function count ($item) {
		if (!$this->shortname) {
			return '';
		}
		$this->count_js();
		Page::instance()->js(
			"disqus_count_items.push('".str_replace("'", "\'", "$this->module/$item")."');",
			'code'
		);
		return h::{'span.cs-disqus-comments-count'}([
			'data-identifier'=> "$this->module/$item"
		]);
	}
	/**
	 * Get comments block with comments tree and comments sending form
	 *
	 * @param int		$item	Item id
	 *
	 * @return string
	 */
	function block ($item) {
		if (!$this->shortname) {
			return '';
		}
		$this->block_js($item);
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
	protected function block_js ($item) {
		static	$added	= false;
		if ($added) {
			return;
		}
		$added		= true;
		Page::instance()->js(
			"var disqus_shortname = '$this->shortname', disqus_identifier = '".str_replace("'", "\'", "$this->module/$item")."';",
			'code'
		);
	}
}
