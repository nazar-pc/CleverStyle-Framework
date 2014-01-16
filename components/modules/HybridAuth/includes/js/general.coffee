###*
 * @package		HybridAuth
 * @category	modules
 * @author		HybridAuth authors
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com> (integration with CleverStyle CMS)
 * @copyright	HybridAuth authors
 * @license		MIT License, see license.txt
###
$ ->
	auth_list	= $('.cs-hybrid-auth-providers-list')
	if auth_list.length
		login_field	= $('.cs-header-sign-in-email')
		list_items	= auth_list.children(':not(:first-child)')
		auth_list.addClass('uk-nav uk-nav-dropdown')
		login_field
			.wrap('<span/>')
			.parent()
				.append(
					$('<div/>')
						.append(auth_list.detach())
						.addClass('uk-dropdown uk-dropdown-small')
				)
				.addClass('uk-button-dropdown')
				.attr('data-uk-dropdown', '')
		list_items.click ->
			location.href = cs.base_url+'/HybridAuth/'+$(this).data('provider')
	return
