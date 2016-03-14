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
	public $admin_path;
	/**
	 * Request to api section
	 *
	 * @var bool
	 */
	public $api_path;
	/**
	 * Current module
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
		$this->admin_path     = false;
		$this->api_path       = false;
		$this->current_module = '';
		$this->home_page      = false;
	}
}
