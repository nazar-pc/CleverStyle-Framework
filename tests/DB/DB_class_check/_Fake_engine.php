<?php
/**
 * @package    CleverStyle Framework
 * @subpackage Test
 * @author     Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright  Copyright (c) 2015-2016, Nazar Mokrynskyi
 * @license    MIT License, see license.txt
 */
namespace cs\DB;
class Fake extends _Abstract {
	static $connected_fake;
	function __construct ($database, $user = '', $password = '', $host = 'localhost', $charset = 'utf8', $prefix = '') {
		var_dump('Fake engine called with arguments:');
		var_dump(func_get_args());
		$connected_fake  = self::$connected_fake;
		$this->connected = $connected_fake();
		var_dump('Connection: '.($this->connected ? 'succeed' : 'failed'));
	}
	protected function q_internal ($query) { }
	protected function q_multi_internal ($query) { }
	function n ($query_result) { }
	function f ($query_result, $single_column = false, $array = false, $indexed = false) { }
	function id () { }
	function affected () { }
	function free ($query_result) { }
	protected function s_internal ($string, $single_quotes_around) { }
	function server () { }
	function __destruct () { }
	function columns ($table, $like = false) { }
	function tables ($like = false) { }
}
