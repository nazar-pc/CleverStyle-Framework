/**
 * @package   HybridAuth
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2015, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
Polymer(
	'is'		: 'cs-hybridauth-sign-in'
	behaviors	: [cs.Polymer.behaviors.Language]
	properties	:
		providers	: do (providers = cs.hybridauth.providers) ->
			for provider of providers
				provider	: provider
				name		: providers[provider].name
				icon		: providers[provider].icon
)
