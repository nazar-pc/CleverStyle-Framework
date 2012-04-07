<?php
global $Config, $Cache, $User;
$rc		= &$Config->routing['current'];
$ajax	= $Config->server['ajax'];
if ($User->is('admin') && isset($rc[2])) {
	switch ($rc[2]) {
		case 'del':
			if ($User->is('system')) {
				unset($Cache->$_POST['data']['item']);
			}
		break;
		case 'flush_cache':
			global $Page, $L;
			if (($ajax || $User->is('system')) && flush_cache()) {
				if (!isset($rc[3]) && $Config->server['mirrors']['count'] > 1) {
					global $Core;
					foreach ($Config->server['mirrors']['http'] as $url) {
						if (!($url == $Config->server['host'] && $Config->server['protocol'] == 'http')) {
							$Core->send('http://'.$url.'/api/'.MODULE.'/admin/cache/flush_cache/1');
						}
					}
					foreach ($Config->server['mirrors']['https'] as $url) {
						if (!($url != $Config->server['host'] && $Config->server['protocol'] == 'https')) {
							$Core->send('https://'.$url.'/api/'.MODULE.'/admin/cache/flush_cache/1');
						}
					}
					unset($url);
				}
				$Cache->disable();
				$Page->Content = $ajax ? h::{'p.ui-state-highlight.ui-corner-all.for_state_messages'}($L->done) : 1;
			} else {
				$Page->Content = $ajax ? h::{'p.ui-state-error.ui-corner-all.for_state_messages'}($L->error) : 0;
			}
		break;
		case 'flush_pcache':
			global $Page, $L;
			if (($ajax || $User->is('system')) && flush_pcache()) {
				time_limit_pause();
				if (!isset($rc[3]) && $Config->server['mirrors']['count'] > 1) {
					global $Core;
					foreach ($Config->server['mirrors']['http'] as $url) {
						$Core->send('http://'.$url.'/api/'.MODULE.'/admin/cache/flush_pcache/1');
					}
					foreach ($Config->server['mirrors']['https'] as $url) {
						$Core->send('https://'.$url.'/api/'.MODULE.'/admin/cache/flush_pcache/1');
					}
					unset($url);
				}
				time_limit_pause(false);
				$Page->Content = $ajax ? h::{'p.ui-state-highlight.ui-corner-all.for_state_messages'}($L->done) : 1;
			} else {
				$Page->Content = $ajax ? h::{'p.ui-state-error.ui-corner-all.for_state_messages'}($L->error) : 0;
			}
	}
}