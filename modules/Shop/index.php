<?php
/**
 * @package  Shop
 * @category modules
 * @author   Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @license  0BSD
 */
namespace cs\modules\Shop;
use
	cs\Config,
	cs\Page;
Page::instance()->config(
	Config::instance()->components['modules']['Shop']['data'],
	'cs.shop.settings'
);
