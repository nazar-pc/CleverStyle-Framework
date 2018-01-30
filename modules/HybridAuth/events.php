<?php
/**
 * @package  HybridAuth
 * @category modules
 * @author   Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @license  0BSD
 */
namespace cs\modules\HybridAuth;
use
	cs\Config,
	cs\DB,
	cs\Event,
	cs\Language\Prefix,
	cs\Page,
	cs\Request,
	cs\Response;

require_once __DIR__.'/functions.php';

Event::instance()
	->on(
		'System/Page/render/before',
		function () {
			if (!Config::instance()->module('HybridAuth')->enabled()) {
				return;
			}
			$Config        = Config::instance();
			$L             = new Prefix('hybridauth_');
			$icons_mapping = function ($provider) {
				switch ($provider) {
					case 'Facebook':
						return 'facebook-f';
					case 'Google':
						return 'google-plus-g';
					case 'Vkontakte':
						return 'vk';
					default:
						return strtolower($provider);
				}
			};
			$providers     = [];
			foreach ($Config->module('HybridAuth')->providers as $provider => $provider_settings) {
				if ($provider_settings['enabled']) {
					$providers[$provider] = [
						'name' => $L->$provider,
						'icon' => $icons_mapping($provider)
					];
				}
			}
			if ($providers) {
				Page::instance()->config($providers, 'cs.hybridauth.providers');
			}
		}
	)
	->on(
		'System/User/registration/confirmation/after',
		function () {
			if (!Config::instance()->module('HybridAuth')->enabled()) {
				return;
			}
			$redirect_to = Request::instance()->cookie('HybridAuth_referer');
			if ($redirect_to) {
				$Response = Response::instance();
				$Response->header('refresh', "5; url=$redirect_to");
				$Response->cookie('HybridAuth_referer', '');
			}
		}
	)
	->on(
		'System/User/del/after',
		function ($data) {
			/**
			 * @var \cs\DB\_Abstract $cdb
			 */
			$cdb = DB::instance()->db_prime(Config::instance()->module('HybridAuth')->db('integration'));
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
	)
	->on(
		'admin/System/modules/install/after',
		function ($data) {
			if ($data['name'] != 'HybridAuth') {
				return;
			}
			Config::instance()->module('HybridAuth')->providers = [];
		}
	);
