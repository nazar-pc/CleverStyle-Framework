<?php
/**
 * @package   CleverStyle CMS
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs\Request;

trait Platform {
	/**
	 * Request to administration section
	 *
	 * @var bool
	 */
	public $admin;
	/**
	 * Request to api section
	 *
	 * @var bool
	 */
	public $api;
	/**
	 * Current module, `System` by default
	 *
	 * @var string
	 */
	public $current_module;
	/**
	 * Home page
	 *
	 * @var bool
	 */
	public $home_page;
	function init_platform () {
		$this->admin          = false;
		$this->api            = false;
		$this->current_module = 'System';
		$this->home_page      = false;
	}
}
