<?php
/**
 * @package		CleverStyle CMS
 * @subpackage	System module
 * @category	modules
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2013, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
namespace	cs;
use			h;
$Cache	= Cache::instance();
$Config	= Config::instance();
$L		= Language::instance();
$Page	= Page::instance();
$rc		= $Config->route;
$ajax	= $Config->server['ajax'];
if (isset($rc[2])) {
	switch ($rc[2]) {
		case 'clean_cache':
			if ($Cache->clean()) {
				$Cache->disable();
				$Page->content($ajax ? _json_encode(h::{'p.ui-state-highlight.ui-corner-all.cs-state-messages'}($L->done)) : 1);
			} else {
				$Page->content($ajax ? _json_encode(h::{'p.ui-state-error.ui-corner-all.cs-state-messages'}($L->error)) : 0);
			}
		break;
		case 'clean_pcache':
			if (clean_pcache()) {
				if (!isset($rc[3])) {
					time_limit_pause();
					Core::instance()->api_request('System/admin/cache/clean_pcache/api');
					time_limit_pause(false);
				}
				$Page->content($ajax ? _json_encode(h::{'p.ui-state-highlight.ui-corner-all.cs-state-messages'}($L->done)) : 1);
			} else {
				$Page->content($ajax ? _json_encode(h::{'p.ui-state-error.ui-corner-all.cs-state-messages'}($L->error)) : 0);
			}
		break;
	}
} else {
	$Page->content($ajax ? _json_encode(h::{'p.ui-state-error.ui-corner-all.cs-state-messages'}($L->error)) : 0);
}