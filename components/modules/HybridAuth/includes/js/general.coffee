###*
 * @package		HybridAuth
 * @category	modules
 * @author		HybridAuth authors
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com> (integration with CleverStyle CMS)
 * @copyright	HybridAuth authors
 * @license		MIT License, see license.txt
###
$ ->
	dropdown = document.querySelector('.cs-hybrid-auth-providers-list')
	if dropdown
		login_field		= $('.cs-header-sign-in-email')
		timeout			= null
		login_field.focus ->
			dropdown.target	= @
			timeout			= setTimeout (->
				dropdown.open()
			), 1000
		login_field.blur ->
			clearTimeout(timeout)
			dropdown.open()
	return
