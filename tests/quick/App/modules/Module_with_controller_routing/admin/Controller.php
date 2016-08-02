<?php
namespace cs\modules\Module_with_controller_routing\admin;

class Controller {
	public static function index () {
		return __METHOD__;
	}
	public static function level10 () {
		var_dump(__METHOD__);
	}
	public static function level10_level21 () {
		var_dump(__METHOD__);
	}
	public static function level10_level21_level30 () {
		var_dump(__METHOD__);
	}
}
