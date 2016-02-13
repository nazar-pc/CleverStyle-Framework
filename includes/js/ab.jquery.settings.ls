/**
 * @package   CleverStyle CMS
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2011-2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
$.ajaxSetup(
	contents	:
		script	: false
	error		: (xhr) !->
		if @['error_' + xhr.status]
			@['error_' + xhr.status].apply(@, arguments)
			return
		cs.ui.notify(
			if xhr.responseText
				JSON.parse(xhr.responseText).error_description
			else
				cs.Language.system_profile_server_connection_error
			'warning'
			5
		)
)
