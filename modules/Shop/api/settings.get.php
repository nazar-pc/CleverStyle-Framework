<?php
/**
 * @package  Shop
 * @category modules
 * @author   Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @license  0BSD
 */
namespace cs;
Page::instance()->json(
	Config::instance()->components['modules']['Shop']['data']
);
