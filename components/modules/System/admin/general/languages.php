<?php
/**
 * @package        CleverStyle CMS
 * @subpackage     System module
 * @category       modules
 * @author         Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright      Copyright (c) 2011-2014, Nazar Mokrynskyi
 * @license        MIT License, see license.txt
 */
namespace cs\modules\System;

use
	h,
	cs\Config,
	cs\Index,
	cs\Language;

$Config = Config::instance();
$L      = Language::instance();
$Config->reload_languages();
Index::instance()->content(
	h::{'cs-table[right-left] cs-table-row| cs-table-cell'}(
		core_select($Config->core['active_languages'], 'language', 'change_language', 'current_language'),
		core_select($Config->core['languages'], 'active_languages', 'change_active_languages', null, true),
		[
			h::info('multilingual'),
			h::radio([
				'name'    => 'core[multilingual]',
				'checked' => $Config->core['multilingual'],
				'value'   => [0, 1],
				'in'      => [$L->off, $L->on]
			])
		]
	)
);
