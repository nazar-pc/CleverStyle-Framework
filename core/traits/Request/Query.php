<?php
/**
 * @package   CleverStyle CMS
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs\Request;

trait Query {
	/**
	 * Query array, similar to `$_GET`
	 *
	 * @var array
	 */
	public $query;
	/**
	 * @param array $query Typically `$_GET`
	 */
	function init_query ($query = []) {
		$this->query = $query;
	}
}
