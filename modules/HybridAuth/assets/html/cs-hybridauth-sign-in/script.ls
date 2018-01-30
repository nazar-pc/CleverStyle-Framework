/**
 * @package  HybridAuth
 * @category modules
 * @author   Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @license  0BSD
 */
Polymer(
	is			: 'cs-hybridauth-sign-in'
	behaviors	: [
		cs.Polymer.behaviors.Language('hybridauth_')
	]
	properties	:
		providers	: Array
	ready : !->
		providers = cs.hybridauth.providers
		@providers	=
			for provider of providers
				provider	: provider
				name		: providers[provider].name
				icon		: providers[provider].icon
)
