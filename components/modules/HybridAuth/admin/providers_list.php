<?php
/**
 * @package   HybridAuth
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2012-2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs;
use
	cs\Language\Prefix,
	h;

$L    = new Prefix('hybridauth_');
$Page = Page::instance();
if (isset($_POST['providers'], $_POST['enable_contacts_detection'])) {
	if (Config::instance()->module('HybridAuth')->set(
		[
			'providers'                 => $_POST['providers'],
			'enable_contacts_detection' => $_POST['enable_contacts_detection']
		]
	)
	) {
		$Page->success($L->changes_saved);
	} else {
		$Page->warning($L->changes_save_error);
	}
}
$Config           = Config::instance();
$providers_config = $Config->module('HybridAuth')->providers;
$providers        = file_get_json(__DIR__.'/../providers.json');
$Page->content(
	h::{'form[is=cs-form]'}(
		h::{'table.cs-table[right-left] tr td'}(
			h::info('enable_contacts_detection'),
			h::radio(
				[
					'name'    => 'enable_contacts_detection',
					'checked' => $Config->module('HybridAuth')->enable_contacts_detection,
					'value'   => [0, 1],
					'in'      => [$L->off, $L->on]
				]
			)
		).
		h::{'table.cs-table[list][center]'}(
			h::{'tr th'}(
				$L->provider,
				$L->settings,
				$L->state
			).
			h::{'tr| td'}(
				array_map(
					function ($provider, $provider_data) use ($L, $providers_config, $Config) {
						$content = '';
						if (isset($provider_data['keys'])) {
							foreach ($provider_data['keys'] as $key) {
								$content .= h::{'tr td'}(
									[
										ucfirst($key),
										h::{'input[is=cs-input-text]'}(
											[
												'name'  => "providers[$provider][keys][$key]",
												'value' => @$providers_config[$provider]['keys'][$key] ?: ''
											]
										)
									]
								);
							}
						}
						return [
							$L->$provider,
							h::{'table.cs-table[right-left]'}(
								$content.
								(
								isset($provider_data['scope']) ? h::{'tr td'}(
									[
										'Scope',
										h::{'input[is=cs-input-text]'}(
											[
												'name'  => "providers[$provider][scope]",
												'value' => @$providers_config[$provider]['scope'] ?: $provider_data['scope']
											]
										)
									]
								) : ''
								).
								(
								isset($provider_data['trustForwarded'])
									? h::{'tr td input[is=cs-input-text]'}(
									[
										'name'  => "providers[$provider][trustForwarded]",
										'value' => 1,
										'type'  => 'hidden'
									]
								)
									: ''
								).
								h::tr(
									isset($provider_data['info'])
										?
										h::td().
										h::{'td[left]'}(
											str_replace(
												[
													'{base_url}',
													'{provider}'
												],
												[
													$Config->core_url(),
													$provider
												],
												$provider_data['info']
											)
										)
										: false
								) ?: false
							) ?: '',
							h::radio(
								[
									'name'    => "providers[$provider][enabled]",
									'checked' => @$providers_config[$provider]['enabled'] ?: 0,
									'value'   => [0, 1],
									'in'      => [$L->off, $L->on]
								]
							)
						];
					},
					array_keys($providers),
					$providers
				)
			)
		).
		h::{'p.cs-text-center button[is=cs-button][type=submit]'}(
			$L->save,
			[
				'tooltip' => $L->save_info
			]
		)
	)
);
