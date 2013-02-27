<?php
/**
 * @package		HybridAuth
 * @category	modules
 * @author		HybridAuth authors
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com> (integration with CleverStyle CMS)
 * @copyright	HybridAuth authors
 * @license		MIT License, see license.txt
 */
global $Config, $Index, $L, $Page;
$povs_config			= $Config->module(MODULE)->providers;
$providers				= _json_decode(file_get_contents(MFOLDER.'/../providers.json'));
$Page->css('components/modules/'.MODULE.'/includes/css/admin.css');
$Page->menumore		= h::a(
	[
		$L->providers_list,
		[
			'href'	=> 'admin/'.MODULE,
			'class'	=> !isset($rc[0]) || $rc[0] == 'providers_list' ? 'active' : false
		]
	]
);
$Index->apply_button	= false;
$Index->content(
	h::{'table.cs-fullwidth-table.cs-left-even.cs-right-odd tr td'}(
		h::info('enable_contacts_detection'),
		h::{'input[type=radio]'}([
			'name'		=> 'enable_contacts_detection',
			'checked'	=> $Config->module(MODULE)->enable_contacts_detection,
			'value'		=> [0, 1],
			'in'		=> [$L->off, $L->on]
		])
	).
	h::{'table.cs-hybrid-auth-providers-table.cs-fullwidth-table.cs-center-all'}(
		h::{'tr th.ui-widget-header.ui-corner-all'}([
			$L->provider,
			$L->settings,
			$L->state
		]).
		h::{'tr| td.ui-widget-content.ui-corner-all'}(
			array_map(
				function ($provider, $pdata) use ($L, $povs_config, $Config) {
					$content	= '';
					if (isset($pdata['keys'])) {
						foreach ($pdata['keys'] as $key) {
							$content	.= h::{'tr td'}([
								ucfirst($key),
								h::input([
									'name'	=> 'providers['.$provider.'][keys]['.$key.']',
									'value'	=> isset($povs_config[$provider], $povs_config[$provider]['keys'][$key]) ? $povs_config[$provider]['keys'][$key] : ''
								])
							]);
						}
					}
					return [
						$L->$provider,
						h::{'table.cs-fullwidth-table.cs-left-even.cs-right-odd'}(
							$content.
							(
								isset($pdata['scope']) ? h::{'tr td'}([
									'Scope',
									h::input([
										'name'	=> 'providers['.$provider.'][scope]',
										'value'	=> isset($povs_config[$provider], $povs_config[$provider]['scope']) ? $povs_config[$provider]['scope'] : $pdata['scope']
									])
								]) : ''
							).
							h::{'tr td.cs-left-all[colspan=2]'}(
								isset($pdata['info']) ? str_replace(
									[
										'{base_url}',
										'{module}',
										'{provider}'
									],
									[
										$Config->base_url(),
										MODULE,
										$provider
									],
									$pdata['info']
								) : false
							)
						),
						h::{'input[type=radio]'}([
							'name'		=> 'providers['.$provider.'][enabled]',
							'checked'	=> isset($povs_config[$provider], $povs_config[$provider]['enabled']) ? $povs_config[$provider]['enabled'] : 0,
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