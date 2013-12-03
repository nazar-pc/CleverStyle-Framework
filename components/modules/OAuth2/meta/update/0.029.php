<?php
/**
 * @package		OAuth2
 * @category	modules
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) Nazar Mokrynskyi, 2013
 * @license		MIT License, see license.txt
 */
namespace	cs;
Config::instance()->module('OAuth2')->set([
	'expiration'				=> 3600,
	'automatic_prolongation'	=> 1
]);