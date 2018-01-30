<?php
/**
 * @package  Content
 * @category modules
 * @author   Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @license  0BSD
 */
namespace cs\modules\Content;
use
	cs\Page,
	cs\User;

Page::instance()->json(
	(bool)User::instance()->admin()
);
