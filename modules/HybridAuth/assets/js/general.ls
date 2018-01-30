/**
 * @package  HybridAuth
 * @category modules
 * @author   Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @license  0BSD
 */
cs.Event.on(
	'cs-system-sign-in'
	(target) !->
		if cs.hybridauth?.providers
			target.shadowRoot.appendChild(
				document.createElement('cs-hybridauth-sign-in')
			)
)
