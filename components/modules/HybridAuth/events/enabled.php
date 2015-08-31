<?php
/**
 * @package        HybridAuth
 * @category       modules
 * @author         HybridAuth authors
 * @author         Nazar Mokrynskyi <nazar@mokrynskyi.com> (integration with CleverStyle CMS)
 * @copyright      HybridAuth authors
 * @license        MIT License, see license.txt
 */
namespace cs\modules\HybridAuth;
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
		$Config = Config::instance();
		$Page   = Page::instance();
		$User   = User::instance();
		if (!(
			$Page->interface &&
			$Config->core['allow_user_registration'] &&
			$User->guest() &&
			!$User->bot()
		)
		) {
			return;
		}
		$providers = $Config->module('HybridAuth')->providers;
		foreach ($providers as $provider => $pdata) {
			if (!$pdata['enabled']) {
				unset($providers[$provider]);
			}
		}
		unset($provider, $pdata);
		if (!count($providers)) {
			return;
		}
		$L            = Language::instance();
		$icon_mapper  = function ($provider) {
			switch ($provider) {
				case 'Google':
					return 'google-plus';
				case 'Vkontakte':
					return 'vk';
				default:
					return strtolower($provider);
			}
		};
		$data['list'] = h::{'nav.cs-hybrid-auth-providers-list[is=cs-nav-dropdown] nav[is=cs-nav-button-group][vertical]'}(
			h::p(
				$L->or_sign_in_with
			).
			h::{'a[is=cs-link-button]'}(
				array_map(
					function ($provider) use ($L, $icon_mapper) {
						return [
							$L->$provider,
							[
								'href' => "/HybridAuth/$provider",
								'icon' => $icon_mapper($provider)
							]
						];
					},
					array_keys($providers)
				)
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
		 * @var \cs\DB\_Abstract $cdb
		 */
		$cdb = DB::instance()->{Config::instance()->module('HybridAuth')->db('integration')}();
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
		$data['contacts'] = array_unique(
			array_merge(
				$data['contacts'],
				Social_integration::instance()->get_contacts($data['id'])
			)
		);
	}
);
