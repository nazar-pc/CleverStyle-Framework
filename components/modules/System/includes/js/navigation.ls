/**
 * @package    CleverStyle Framework
 * @subpackage System module
 * @category   modules
 * @author     Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright  Copyright (c) 2015-2016, Nazar Mokrynskyi
 * @license    MIT License, see license.txt
 */
<-! $
url_map = {
	"admin/System/components/modules"   : "cs-system-admin-modules-list",
	"admin/System/components/plugins"   : "cs-system-admin-plugins-list",
	"admin/System/components/blocks"    : "cs-system-admin-blocks-list",
	"admin/System/components/databases" : "cs-system-admin-databases-list",
	"admin/System/components/storages"  : "cs-system-admin-storages-list",
	"admin/System/general/site_info"    : "cs-system-admin-site-info",
	"admin/System/general/system"       : "cs-system-admin-system",
	"admin/System/general/optimization" : "cs-system-admin-optimization",
	"admin/System/general/appearance"   : "cs-system-admin-themes",
	"admin/System/general/languages"    : "cs-system-admin-languages",
	"admin/System/general/about_server" : "cs-system-admin-about-server",
	"admin/System/users/general"        : "cs-system-admin-users-general",
	"admin/System/users/users"          : "cs-system-admin-users-list",
	"admin/System/users/groups"         : "cs-system-admin-groups-list",
	"admin/System/users/permissions"    : "cs-system-admin-permissions-list",
	"admin/System/users/security"       : "cs-system-admin-security",
	"admin/System/users/mail"           : "cs-system-admin-mail"
}
$buttons	= $('body > header > nav > button')
$links		= $('body > header > nav a')
	.mousedown (e) !->
		# We are interested in left click only
		if e.which == 1
			e.preventDefault()
			href	= @getAttribute('href')
			go(href)
			history.pushState({}, document.title, href)
	.click (e) ->
		# Ignore left click
		e.which != 1
title_format	= document.title
L				= cs.Language('system_admin_')
!function go (href)
	href_splitted	= href.split('/')
	document.title	= sprintf(
		title_format
		L[href_splitted[2]]
		L[href_splitted[3]]
	)
	$('#main_content > div').html('<' + url_map[href] + '/>')
	$links.prop('primary', false)
	$buttons.prop('primary', false)
	$links.filter("[href='#href']").prop('primary', true)
		.parent()
			.parent()
				.prev()
					.prop('primary', true)
!function popstate (e)
	if location.href.indexOf('admin/System/') != -1
		go(
			location.href.match(/admin\/System\/\w+\/\w+/)[0]
		)
	else
		href = location.href.split('?')[0]
		if href == document.baseURI + 'admin' || href == document.baseURI + 'admin/System'
			go('admin/System/components/modules')
addEventListener('popstate', popstate)
popstate()
