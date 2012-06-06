<?php
global $Config, $Cache, $User;
$rc		= &$Config->routing['current'];
$ajax	= $Config->server['ajax'];
if (isset($rc[2])) {
	switch ($rc[2]) {
		case 'del':
			if ($User->is('system')) {
				unset($Cache->$_POST['data']['item']);
			}
		break;
		case 'clean_cache':
			global $Page, $L;
			if ($Cache->clean()) {
				if (!isset($rc[3])) {
					global $Core;
					time_limit_pause();
					$Core->api_request(MODULE.'/admin/cache/clean_cache/api');
					time_limit_pause(false);
				}
				$Cache->disable();
				$Page->content($ajax ? h::{'p.ui-state-highlight.ui-corner-all.cs-state-messages'}($L->done) : 1);
			} else {
				$Page->content($ajax ? h::{'p.ui-state-error.ui-corner-all.cs-state-messages'}($L->error) : 0);
			}
		break;
		case 'clean_pcache':
			global $Page, $L;
			if (clean_pcache()) {
				if (!isset($rc[3])) {
					global $Core;
					time_limit_pause();
					$Core->api_request(MODULE.'/admin/cache/clean_pcache/api');
					time_limit_pause(false);
				}
				$Page->content($ajax ? h::{'p.ui-state-highlight.ui-corner-all.cs-state-messages'}($L->done) : 1);
			} else {
				$Page->content($ajax ? h::{'p.ui-state-error.ui-corner-all.cs-state-messages'}($L->error) : 0);
			}
		break;
	}
}