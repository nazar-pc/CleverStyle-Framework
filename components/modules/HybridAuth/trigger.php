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
use			h;
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
		$providers		= $Config->module($module)->providers;
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
		$data['list']	= h::{'ul.cs-hybrid-auth-providers-list li'}(
			[
				$L->or_login_with,
				[
					'class'	=> 'ui-widget-header'
				]
			],
			array_map(
				function ($provider) use ($L) {
					return [
						h::div().
						$L->$provider,
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
		/**
		 *	@var \cs\DB\_Abstract $cdb
		 */
		$cdb			= $db->{$Config->module($module)->db('integration')}();
		$cdb->q(
			[
				"DELETE FROM `[prefix]users_social_integration`
				WHERE `id` = '%s'",
				"DELETE FROM `[prefix]users_social_integration_contacts`
				WHERE `id`		= '%s'"
			],
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
$Core->register_trigger(
	'System/User/get_contacts',
	function ($data) {
		$data['contacts']	= array_unique(array_merge(
			$data['contacts'],
			get_user_contacts($data['user'])
		));
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
/**
 * Returns array of user id, that are contacts of specified user
 *
 * @param int		$user
 *
 * @return int[]
 */
function get_user_contacts ($user) {
	global $Config, $db, $Cache;
	$user	= (int)$user;
	$module	= basename(__DIR__);
	if (
		!$user ||
		$user == 1 ||
		!$Config->module($module)->enable_contacts_detection
	) {
		return [];
	}
	if (!($data = $Cache->{'HybridAuth/contacts/'.$user})) {
		/**
		 *	@var \cs\DB\_Abstract $cdb
		 */
		$cdb									= $db->{$Config->module($module)->db('integration')};
		$data									= $cdb->qfas([
			"SELECT `u`.`id`
			FROM `[prefix]users_social_integration` AS `u`
			INNER JOIN `[prefix]users_social_integration_contacts` AS `c`
			ON
				`u`.`identifier`	= `c`.`identifier` AND
				`u`.`provider`		= `c`.`provider`
			WHERE `c`.`id`	= '%s'
			GROUP BY `u`.`id`",
			$user
		]) ?: [];
		$Cache->{'HybridAuth/contacts/'.$user}	= $data;
	}
	return $data;
}
/**
 * Updates user contacts for specified provider
 *
 * @param \Hybrid_User_Contact[]	$contacts
 * @param string					$provider
 */
function update_user_contacts ($contacts, $provider) {
	global $Config, $db, $User, $Cache;
	$module	= basename(__DIR__);
	$id		= $User->id;
	/**
	 *	@var \cs\DB\_Abstract $cdb
	 */
	$cdb	= $db->{$Config->module($module)->db('integration')}();
	$cdb->q(
		"DELETE FROM `[prefix]users_social_integration_contacts`
		WHERE
			`id`		= '%s' AND
			`provider`	= '%s'",
		$id,
		$provider
	);
	if (!empty($contacts)) {
		$insert	= [];
		$params	= [];
		foreach ($contacts as $contact) {
			$insert[]	= "('%s', '%s', '%s')";
			$params[]	= $id;
			$params[]	= $provider;
			$params[]	= $contact->identifier;
		}
		$insert	= implode(',', $insert);
		$cdb->q(
			"INSERT INTO `[prefix]users_social_integration_contacts`
			(
				`id`,
				`provider`,
				`identifier`
			) VALUES $insert",
			$params
		);
	}
	unset($Cache->{'HybridAuth/contacts/'.$id});
}