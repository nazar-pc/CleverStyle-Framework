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
use
	h,
	cs\Config,
	cs\DB,
	cs\Event,
	cs\Language,
	cs\Page,
	cs\User;
Event::instance()->on(
	'System/Page/external_sign_in_list',
	function ($data) {
		$Config			= Config::instance();
		$Page			= Page::instance();
		$User			= User::instance();
		if (!(
			$Page->interface &&
			$Config->core['allow_user_registration'] &&
			$User->guest() &&
			!$User->bot()
		)) {
			return;
		}
		$providers		= $Config->module('HybridAuth')->providers;
		foreach ($providers as $provider => $pdata) {
			if (!$pdata['enabled']) {
				unset($providers[$provider]);
			}
		}
		unset($provider, $pdata);
		if (!count($providers)) {
			return;
		}
		$L				= Language::instance();
		$icon_mapper	= function ($provider) {
			switch ($provider) {
				case 'Facebook':
					return 'facebook';
				case 'Foursquare':
					return 'foursquare';
				case 'GitHub':
					return 'github';
				case 'Google':
					return 'google-plus';
				case 'Instagram':
					return 'instagram';
				case 'LinkedIn':
					return 'linkedin';
				case 'Tumblr':
					return 'tumbrl';
				case 'Twitter':
					return 'twitter';
				case 'Vkontakte':
					return 'vk';
				default:
					return false;
			}
		};
		$data['list']	= h::{'ul.cs-hybrid-auth-providers-list li'}(
			[
				$L->or_sign_in_with,
				[
					'class'	=> 'uk-nav-header'
				]
			],
			array_map(
				function ($provider) use ($L, $icon_mapper) {
					return [
						h::a(h::icon($icon_mapper($provider)).$L->$provider),
						[
							'data-provider'	=> $provider,
							'class'			=> "cs-hybrid-auth-$provider"
						]
					];
				},
				array_keys($providers)
			)
		);
	}
);
Event::instance()->on(
	'System/User/registration/confirmation/after',
	function () {
		if ($referer = _getcookie('HybridAuth_referer')) {
			_header("Refresh: 5; url=$referer");
			_setcookie('HybridAuth_referer', '');
		}
	}
);
Event::instance()->on(
	'System/User/del/after',
	function ($data) {
		/**
		 *	@var \cs\DB\_Abstract $cdb
		 */
		$cdb			= DB::instance()->{Config::instance()->module('HybridAuth')->db('integration')}();
		$cdb->q(
			[
				"DELETE FROM `[prefix]users_social_integration`
				WHERE `id` = '%s'",
				"DELETE FROM `[prefix]users_social_integration_contacts`
				WHERE `id` = '%s'"
			],
			$data['id']
		);
	}
);
Event::instance()->on(
	'System/User/get_contacts',
	function ($data) {
		$data['contacts']	= array_unique(array_merge(
			$data['contacts'],
			get_user_contacts($data['id'])
		));
	}
);
