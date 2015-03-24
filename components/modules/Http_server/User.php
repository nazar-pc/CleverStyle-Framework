<?php
/**
 * @package   Http server
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2015, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs\custom;
use
	cs\User as User_original;
/**
 * @inheritdoc
 */
class User extends User_original {
	function construct () {
		$this->memory_cache = false;
		parent::construct();
	}
}
