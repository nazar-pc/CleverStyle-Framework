/**
 * @package		HybridAuth
 * @category	modules
 * @author		HybridAuth authors
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com> (integration with CleverStyle CMS)
 * @copyright	HybridAuth authors
 * @license		MIT License, see license.txt
 */
$(function () {
	var login_field	= $('.cs-header-login-email'),
		auth_list	= $('.cs-hybrid-auth-providers-list'),
		list_items	= $('.cs-hybrid-auth-providers-list > li:not(:first-child)'),
		coordinates	= {
			pageX	: 0,
			pageY	: 0
		};
	if (auth_list.length) {
		login_field.focusin(function () {
			auth_list.fadeIn('medium');
		}).focusout(function () {
			var position	= auth_list.offset();
			if (
				coordinates.pageX > position.left &&
				coordinates.pageX < position.left + auth_list.outerWidth() &&
				coordinates.pageY > position.top &&
				coordinates.pageY < position.top + auth_list.outerHeight()
			) {
				auth_list.on(
					'mouseleave',
					function () {
						auth_list.fadeOut('fast').off('mouseleave');
					}
				);
			} else {
				auth_list.fadeOut('fast');
			}
		});
		list_items.click(function () {
			location.href = cs.base_url+'/HybridAuth/'+$(this).data(('provider'));
		});
		$(document).mousemove(function (e) {
			coordinates = {
				pageX	: e.pageX,
				pageY	: e.pageY
			};
		});
	}
});