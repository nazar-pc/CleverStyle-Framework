<?php
/**
 * @package		HybridAuth
 * @category	modules
 * @author		HybridAuth authors
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com> (integration with CleverStyle CMS)
 * @copyright	HybridAuth authors
 * @license		MIT License, see license.txt
 */
namespace	cs\modules\HybridAuth;
use			\h;
global $Core;
$Core->register_trigger(
	'System/Page/external_login_list',
	function ($data) {
		global $Config, $User, $Page, $L;
		$module			= basename(__DIR__);
		if (!(
			$Page->interface &&
			isset($Config->components['modules'][$module]) &&
			$Config->components['modules'][$module]['active'] == 1 &&
			$User->guest() &&
			!$User->bot()
		)) {
			return;
		}
		if (!$Config->core['cache_compress_js_css']) {
			$Page->css('components/modules/'.$module.'/includes/css/general.css');
			$Page->js([
				'components/modules/'.$module.'/includes/js/general.js',
				'components/modules/'.$module.'/includes/js/functions.js'
			]);
		} elseif (!(
			file_exists(PCACHE.'/module.'.$module.'.js') && file_exists(PCACHE.'/module.'.$module.'.css')
		)) {
			rebuild_pcache();
		}
		$data['list']	= h::{'ul.cs-hybrid-auth-providers-list li.cs-input-style'}(
			[
				$L->or_login_with,
				[
					'class'	=> 'ui-widget-header'
				]
			],
			array_map(
				function ($provider) {
					return [
						h::div().
						$provider,
						[
							'data-provider'	=> $provider,
							'class'			=> 'ui-widget-content cs-hybrid-auth-'.$provider
						]
					];
				},
				_substr(get_files_list(__DIR__.'/Hybrid/Providers'), 0, -4)
			)
		);
	}
);
$Core->register_trigger(
	'admin/System/components/modules/install/process',
	function ($data) {
		global $User, $Config;
		$module	= basename(__DIR__);
		if ($data['name'] != $module || !$User->admin()) {
			return;
		}
		$Config->module($module)->set(
			[
				'providers'	=> []
			]
		);

	}
);
$Core->register_trigger(
	'admin/System/components/modules/enable',
	function ($data) {
		if ($data['name'] == basename(__DIR__)) {
			rebuild_pcache();
		}

	}
);
$Core->register_trigger(
	'admin/System/components/modules/disable',
	function ($data) {
		if ($data['name'] == basename(__DIR__)) {
			clean_pcache($data);
		}
	}
);
$Core->register_trigger(
	'admin/System/general/optimization/clean_pcache',
	function () {
		clean_pcache();
	}
);
$Core->register_trigger(
	'System/Page/rebuild_cache',
	function () {
		rebuild_pcache();
	}
);
function clean_pcache ($data = null) {
	$module	= basename(__DIR__);
	if ($data['name'] == $module || $data === null) {
		if (file_exists(PCACHE.'/module.'.$module.'.js')) {
			unlink(PCACHE.'/module.'.$module.'.js');
		}
		if (file_exists(PCACHE.'/module.'.$module.'.css')) {
			unlink(PCACHE.'/module.'.$module.'.css');
		}
	}
}
function rebuild_pcache (&$data = null) {
	$module	= basename(__DIR__);
	file_put_contents(
		PCACHE.'/module.'.$module.'.js',
		$key	= gzencode(
			file_get_contents(MODULES.'/'.$module.'/includes/js/functions.js').
			file_get_contents(MODULES.'/'.$module.'/includes/js/general.js'),
			9
		),
		LOCK_EX | FILE_BINARY
	);
	file_put_contents(
		PCACHE.'/module.'.$module.'.css',
		$key	.= gzencode(
			file_get_contents(MODULES.'/'.$module.'/includes/css/general.css'),
			9
		),
		LOCK_EX | FILE_BINARY
	);
	if ($data !== null) {
		$data['key']	.= md5($key);
	}
}