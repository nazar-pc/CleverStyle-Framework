<?php
/**
 * @package		CleverStyle CMS
 * @subpackage	System module
 * @category	modules
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2013, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
global $Config, $Cache, $User, $Page, $L;
$rc		= $Config->route;
$ajax	= $Config->server['ajax'];
if (isset($rc[2])) {
	switch ($rc[2]) {
		case 'del':
			if ($User->system()) {
				unset($Cache->$_POST['data']['item']);
			}
		break;
		case 'clean_cache':
			if ($Cache->clean()) {
				if (!isset($rc[3])) {
					global $Core;
					time_limit_pause();
					$Core->api_request(MODULE.'/admin/cache/clean_cache/api');
					time_limit_pause(false);
				}
				$Cache->disable();
				$Page->content($ajax ? _json_encode(h::{'p.ui-state-highlight.ui-corner-all.cs-state-messages'}($L->done)) : 1);
			} else {
				$Page->content($ajax ? _json_encode(h::{'p.ui-state-error.ui-corner-all.cs-state-messages'}($L->error)) : 0);
			}
		break;
		case 'clean_pcache':
			if (clean_pcache()) {
				if (!isset($rc[3])) {
					global $Core;
					time_limit_pause();
					$Core->api_request(MODULE.'/admin/cache/clean_pcache/api');
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