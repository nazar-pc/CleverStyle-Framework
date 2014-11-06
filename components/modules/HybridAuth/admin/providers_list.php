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
include __DIR__.'/save.php';
$Config					= Config::instance();
$Index					= Index::instance();
$L						= Language::instance();
$providers_config		= $Config->module('HybridAuth')->providers;
$providers				= file_get_json(__DIR__.'/../providers.json');
$Index->apply_button	= false;
$Index->content(
	h::{'cs-table[right-left] cs-table-row cs-table-cell'}(
		h::info('enable_contacts_detection'),
		h::radio([
			'name'		=> 'enable_contacts_detection',
			'checked'	=> $Config->module('HybridAuth')->enable_contacts_detection,
			'value'		=> [0, 1],
			'in'		=> [$L->off, $L->on]
		])
	).
	h::{'cs-table[list][center][with-header] cs-table-row| cs-table-cell'}(
		[
			$L->provider,
			$L->settings,
			$L->state
		],
		array_map(
			function ($provider, $provider_data) use ($L, $providers_config, $Config) {
				$content	= '';
				if (isset($provider_data['keys'])) {
					foreach ($provider_data['keys'] as $key) {
						$content	.= h::{'cs-table-row cs-table-cell'}([
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
					h::{'cs-table[right-left]'}(
						$content.
						(
							isset($provider_data['scope']) ? h::{'cs-table-row cs-table-cell'}([
								'Scope',
								h::input([
									'name'	=> "providers[$provider][scope]",
									'value'	=> @$providers_config[$provider]['scope'] ?: $provider_data['scope']
								])
							]) : ''
						).
						(
							isset($provider_data['trustForwarded'])
								? h::{'cs-table-row cs-table-cell input'}([
									'name'	=> "providers[$provider][trustForwarded]",
									'value'	=> 1,
									'type'	=> 'hidden'
								])
								: ''
						).
						h::{'cs-table-row'}(
							isset($provider_data['info'])
								?
									h::{'cs-table-cell'}().
									h::{'cs-table-cell[left]'}(
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
					h::radio([
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
);
