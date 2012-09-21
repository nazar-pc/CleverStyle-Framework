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
			$Config->core['allow_user_registration'] &&
			$Page->interface &&
			isset($Config->components['modules'][$module]) &&
			$Config->components['modules'][$module]['active'] == 1 &&
			$User->guest() &&
			!$User->bot()
		)) {
			return;
		}
		$providers		= $Config->module($module)->get('providers');
		foreach ($providers as $provider => $pdata) {
			if (!$pdata['enabled']) {
				unset($providers[$provider]);
			}
		}
		unset($provider, $pdata);
		if (!count($providers)) {
			return;
		}
		if (!$Config->core['cache_compress_js_css']) {
			$Page->css('components/modules/'.$module.'/includes/css/general.css');
			$Page->js('components/modules/'.$module.'/includes/js/general.js');
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
				array_keys($providers)
			)
		);
	}
);
$Core->register_trigger(
	'System/User/registration/confirmation/after',
	function ($data) {
		global $Config;
		$module			= basename(__DIR__);
		if (!(
			isset($Config->components['modules'][$module]) &&
			$Config->components['modules'][$module]['active'] == 1
		)) {
			return;
		}
		if ($referer = _getcookie('HybridAuth_referer')) {
			header('Refresh: 5; url='.$referer);
			_setcookie('HybridAuth_referer', '');
		}
	}
);
$Core->register_trigger(
	'System/User/registration/after',
	function ($data) {
		global $Config, $User, $db;
		$module			= basename(__DIR__);
		if (!(
			isset($Config->components['modules'][$module]) &&
			$Config->components['modules'][$module]['active'] == 1 &&
			$data = $User->get_session_data('HybridAuth')
		)) {
			return;
		}
		$db->{$Config->module($module)->db('integration')}()->q(
			"INSERT INTO `[prefix]users_social_integration`
				(
					`id`,
					`provider`,
					`identifier`
				) VALUES (
					'%s',
					'%s',
					'%s'
				)",
			$data['id'],
			$data['provider'],
			$data['identifier']
		);
		$User->del_session_data('HybridAuth');
	}
);
$Core->register_trigger(
	'System/User/del_user/after',
	function ($data) {
		global $Config, $db;
		$module			= basename(__DIR__);
		if (!(
			isset($Config->components['modules'][$module]) &&
			$Config->components['modules'][$module]['active'] == 1
		)) {
			return;
		}
		$db->{$Config->module($module)->db('integration')}()->q(
			"DELETE FROM `[prefix]users_social_integration`
			WHERE `id` = '%s'",
			$data['id']
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
	global $Page;
	$module	= basename(__DIR__);
	$key	= [];
	file_put_contents(
		PCACHE.'/module.'.$module.'.js',
		$key[]	= gzencode(
			file_get_contents(MODULES.'/'.$module.'/includes/js/general.js'),
			9
		),
		LOCK_EX | FILE_BINARY
	);
	$css	= file_get_contents(MODULES.'/'.$module.'/includes/css/general.css');
	file_put_contents(
		PCACHE.'/module.'.$module.'.css',
		$key[]	= gzencode(
			$Page->css_includes_processing(
				$css,
				MODULES.'/'.$module.'/includes/css/general.css'
			),
			9
		),
		LOCK_EX | FILE_BINARY
	);
	if ($data !== null) {
		$data['key']	.= md5(implode('', $key));
	}
}