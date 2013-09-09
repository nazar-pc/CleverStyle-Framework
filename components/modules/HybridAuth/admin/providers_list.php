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
$providers				= _json_decode(file_get_contents(MFOLDER.'/../providers.json'));
$Page->css('components/modules/HybridAuth/includes/css/admin.css');
$Page->main_sub_menu	= h::{'li.uk-active a'}(
	$L->providers_list,
	[
		'href'	=> 'admin/HybridAuth'
	]
);
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
				function ($provider, $pdata) use ($L, $providers_config, $Config) {
					$content	= '';
					if (isset($pdata['keys'])) {
						foreach ($pdata['keys'] as $key) {
							$content	.= h::{'tr td'}([
								ucfirst($key),
								h::input([
									'name'	=> 'providers['.$provider.'][keys]['.$key.']',
									'value'	=> isset($providers_config[$provider], $providers_config[$provider]['keys'][$key]) ? $providers_config[$provider]['keys'][$key] : ''
								])
							]);
						}
					}
					return [
						$L->$provider,
						h::{'table.cs-table-borderless.cs-left-even.cs-right-odd'}(
							$content.
							(
								isset($pdata['scope']) ? h::{'tr td'}([
									'Scope',
									h::input([
										'name'	=> 'providers['.$provider.'][scope]',
										'value'	=> isset($providers_config[$provider], $providers_config[$provider]['scope']) ? $providers_config[$provider]['scope'] : $pdata['scope']
									])
								]) : ''
							).
							h::{'tr td.cs-left-all[colspan=2]'}(
								isset($pdata['info']) ? str_replace(
									[
										'{base_url}',
										'{provider}'
									],
									[
										$Config->base_url(),
										$provider
									],
									$pdata['info']
								) : false
							)
						),
						h::{'input[type=radio]'}([
							'name'		=> 'providers['.$provider.'][enabled]',
							'checked'	=> isset($providers_config[$provider], $providers_config[$provider]['enabled']) ? $providers_config[$provider]['enabled'] : 0,
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