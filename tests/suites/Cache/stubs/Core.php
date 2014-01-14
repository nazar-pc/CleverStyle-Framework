<?php
/**
 * @package		CleverStyle CMS
 * @subpackage	Tester
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2014, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
namespace	cs\custom;
use			cs\Singleton;
class Core {
	use	Singleton;

	function construct () {
		$this->cache_engine	= 'FileSystem';
		$this->cache_size	= 1;
	}
}
