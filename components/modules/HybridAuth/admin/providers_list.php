<?php
/**
 * @package		HybridAuth
 * @category	modules
 * @author		HybridAuth authors
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com> (integration with CleverStyle CMS)
 * @copyright	HybridAuth authors
 * @license		MIT License, see license.txt
 */
namespace	cs;
use			h;
$Config					= Config::instance();
$Index					= Index::instance();
$L						= Language::instance();
$Page					= Page::instance();
$providers_config		= $Config->module('HybridAuth')->providers;
$providers				= file_get_json(__DIR__.'/../providers.json');
$Page->css('components/modules/HybridAuth/includes/css/admin.css');
$Index->apply_button	= false;
$Index->content(
	h::{'table.cs-table-borderless.cs-left-even.cs-right-odd tr'}(
		h::{'th info'}('enable_contacts_detection').
		h::{'td input[type=radio]'}([
			'name'		=> 'enable_contacts_detection',
			'checked'	=> $Config->module('HybridAuth')->enable_contacts_detection,
			'value'		=> [0, 1],
			'in'		=> [$L->off, $L->on]
		])
	).
	h::{'table.cs-hybrid-auth-providers-table.cs-table.cs-center-all'}(
		h::{'thead tr th'}([
			$L->provider,
			$L->settings,
			$L->state
		]).
		h::{'tbody tr| td'}(
			array_map(
				function ($provider, $provider_data) use ($L, $providers_config, $Config) {
					$content	= '';
					if (isset($provider_data['keys'])) {
						foreach ($provider_data['keys'] as $key) {
							$content	.= h::{'tr td'}([
								ucfirst($key),
								h::input([
									'name'	=> "providers[$provider][keys][$key]",
									'value'	=> @$providers_config[$provider]['keys'][$key] ?: ''
								])
							]);
						}
					}
					return [
						$L->$provider,
						h::{'table.cs-table-borderless.cs-left-even.cs-right-odd'}(
							$content.
							(
								isset($provider_data['scope']) ? h::{'tr td'}([
									'Scope',
									h::input([
										'name'	=> "providers[$provider][scope]",
										'value'	=> @$providers_config[$provider]['scope'] ?: $provider_data['scope']
									])
								]) : ''
							).
							(
								isset($provider_data['trustForwarded'])
									? h::{'tr td.cs-left-all[colspan=2] input'}([
										'name'	=> "providers[$provider][trustForwarded]",
										'value'	=> 1,
										'type'	=> 'hidden'
									])
									: ''
							).
							h::{'tr td.cs-left-all[colspan=2]'}(
								isset($provider_data['info']) ? str_replace(
									[
										'{base_url}',
										'{provider}'
									],
									[
										$Config->base_url(),
										$provider
									],
									$provider_data['info']
								) : false
							) ?: false
						) ?: '',
						h::{'input[type=radio]'}([
							'name'		=> "providers[$provider][enabled]",
							'checked'	=> @$providers_config[$provider]['enabled'] ?: 0,
							'value'		=> [0, 1],
							'in'		=> [$L->off, $L->on]
						])
					];
				},
				array_keys($providers),
				$providers
			)
		)
	)
);
