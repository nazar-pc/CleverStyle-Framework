/**
 * @package   CleverStyle Framework
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2014-2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
addEventListener('DOMContentLoaded', !->
	if document.body.hasAttribute('unresolved')
		document.body.setAttribute('unresolved-transition', '')
)
document.addEventListener('WebComponentsReady', !->
	# Since we may use some CSS variables and mixins, lets update styles to make sure we didn't leave any styles not applied
	Polymer.updateStyles()
	if document.body.hasAttribute('cs-unresolved')
		document.body.setAttribute('cs-unresolved-transition', '')
		document.body.removeAttribute('cs-unresolved')
	setTimeout (!->
		document.body.removeAttribute('unresolved-transition')
		document.body.removeAttribute('cs-unresolved-transition')
	), 250
)
if !window.WebComponents?.flags
	addEventListener('load', !->
		setTimeout !->
			document.dispatchEvent(new CustomEvent(
				'WebComponentsReady'
				bubbles	: true
			))
	)
# If there is native Shadow DOM support - lets store cookie so that we can skip loading Shadow DOM polyfill
if document.cookie.indexOf('shadow_dom=1') == -1
	value	=
		if 'registerElement' of document && 'import' of document.createElement('link') && 'content' of document.createElement('template')
			1
		else
			0
	date	= new Date()
	date.setTime(date.getTime() + (30d * 24h * 3600s * 1000ms))
	document.cookie = "shadow_dom=#value; path=/; expires=" + date.toGMTString()
