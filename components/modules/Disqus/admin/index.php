<?php
/**
 * @package   Cron
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2011-2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs;
use
	h;
include __DIR__.'/save.php';
$Index                        = Index::instance();
$Index->form_attributes['is'] = 'cs-form';
$Index->content(
	h::label('Shortname').
	h::{'input[is=cs-input-text][name=shortname]'}(
		[
			'value' => Config::instance()->module('Disqus')->shortname ?: ''
		]
	).
	h::br()
);
