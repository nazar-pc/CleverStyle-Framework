<?php
/**
 * @package   Cron
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2011-2015, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs;
use            h;
include __DIR__.'/save.php';
Index::instance()->content(
	h::{'p.cs-text-center'}('Shortname').
	h::{'p input[name=shortname]'}(
		[
			'value' => Config::instance()->module('Disqus')->shortname ?: ''
		]
	)
);
