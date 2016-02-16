<?php
/**
 * @package   CleverStyle CMS
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs;

Text::instance()->del(
	Config::instance()->module('System')->db('texts'),
	'System/Config/core',
	'rules'
);
