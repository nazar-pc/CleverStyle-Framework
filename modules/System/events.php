<?php
/**
 * @package    CleverStyle CMS
 * @subpackage System module
 * @category   modules
 * @author     Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright  Copyright (c) 2013-2017, Nazar Mokrynskyi
 * @license    MIT License, see license.txt
 */
namespace cs;
use
	h;

/**
 * Multilingual functionality: redirects and necessary meta-tags
 */
Event::instance()
	->on(
		'System/Session/init/after',
		function () {
			$Config  = Config::instance();
			$Session = Session::instance();
			$user_id = $Session->get_user();
			/**
			 * If not guest - apply some individual settings
			 */
			if ($user_id != User::GUEST_ID) {
				$User     = User::instance();
				$timezone = $User->get('timezone', $user_id);
				if ($timezone && date_default_timezone_get() != $timezone) {
					date_default_timezone_set($timezone);
				}
				$L = Language::instance();
				/**
				 * Change language if configuration is multilingual and this is not page with localized url
				 */
				$language = $User->get('language', $user_id);
				if ($Config->core['multilingual'] && !$L->url_language() && $language) {
					$L->change($language);
				}
			}
			$Request = Request::instance();
			/**
			 * Security check
			 */
			if (
				$Request->header('x-requested-with') !== 'XMLHttpRequest' &&
				$Request->data('session') != $Session->get_id() &&
				$Request->header('origin') != $Config->core_url()
			) {
				$Request->data = [];
			}
			/**
			 * Show notification if there is any
			 */
			$notification = $Session->get_data('system_notification');
			if ($notification) {
				list($content, $type) = $notification;
				Page::instance()->post_Body .= h::{"cs-notify[$type]"}($content);
			}
		}
	)
	->on(
		'System/User/construct/after',
		function () {
			$Config  = Config::instance();
			$Request = Request::instance();
			if (!($Request->regular_path && $Config->core['multilingual'])) {
				return;
			}
			$Request          = Request::instance();
			$relative_address = $Request->path_normalized;
			$Page             = Page::instance();
			$core_url         = $Config->core_url();
			$base_url         = $Config->base_url();
			$Page->Head       .= h::link(
				[
					'hreflang' => 'x-default',
					'href'     => $Request->home_page ? $core_url : "$core_url/$relative_address",
					'rel'      => 'alternate'
				]
			);
			$clangs           = Cache::instance()->get(
				'languages/clangs',
				function () use ($Config) {
					$clangs = [];
					foreach ($Config->core['active_languages'] as $language) {
						$clangs[] = file_get_json_nocomments(LANGUAGES."/$language.json")['clang'];
					}
					return $clangs;
				}
			);
			foreach ($clangs as $clang) {
				$Page->Head .= h::link(
					[
						'hreflang' => $clang,
						'href'     => "$base_url/$clang/$relative_address",
						'rel'      => 'alternate'
					]
				);
			}
		}
	)
	->on(
		'admin/System/Menu',
		function () {
			$Config    = Config::instance();
			$L         = Language::prefix('system_admin_');
			$Menu      = Menu::instance();
			$Request   = Request::instance();
			$structure = $Config->core['simple_admin_mode'] ? file_get_json(__DIR__.'/admin/index_simple.json') : file_get_json(__DIR__.'/admin/index.json');
			foreach ($structure as $section => $items) {
				$Menu->add_section_item(
					'System',
					$L->$section,
					[
						'href'    => "admin/System/$section",
						'primary' => $Request->route_path(0) == $section
					]
				);
				foreach ($items as $item) {
					$Menu->add_item(
						'System',
						$L->$item,
						[
							'href'    => "admin/System/$section/$item",
							'primary' => $Request->route_path(0) == $section && $Request->route_path(1) == $item
						]
					);
				}
			}
		}
	)
	->on(
		'System/Page/requirejs',
		function ($data) {
			$data['paths'] += [
				'jssha'         => DIR.'/assets/js/modules/jsSHA-2.1.0',
				'autosize'      => DIR.'/assets/js/modules/autosize.min',
				'html5sortable' => DIR.'/assets/js/modules/html5sortable-0.5.3.min',
				'sprintf-js'    => DIR.'/assets/js/modules/sprintf-1.1.1.min'
			];
		}
	)
	->on(
		'System/Request/routing_replace/before',
		function ($data) {
			/** @noinspection NotOptimalIfConditionsInspection */
			if (
				(
					strpos($data['rc'], 'bower_components/polymer/') === 0 ||
					strpos($data['rc'], 'bower_components/shadycss/') === 0 ||
					strpos($data['rc'], 'node_modules/@polymer/polymer/') === 0 ||
					strpos($data['rc'], 'node_modules/@webcomponents/shadycss/') === 0
				) &&
				Request::instance()->method == 'GET'
			) {
				$extension = file_extension(explode('?', $data['rc'])[0]);
				if ($extension == 'css' || $extension == 'html') {
					$content_type = "text/$extension";
				} elseif ($extension == 'js') {
					$content_type = 'application/javascript';
				}
				if (isset($content_type)) {
					Response::instance()
						->header('Content-Type', $content_type)
						->header('Cache-Control', 'max-age=2592000, immutable');
					throw new ExitException;
				}
			}
		}
	);
