/**
 * @package   HybridAuth
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2015-2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
cs.Event.on(
	'cs-system-sign-in'
	(target) !->
		if cs.hybridauth.providers
			target.shadowRoot.appendChild(
				document.createElement('cs-hybridauth-sign-in')
			)
)
