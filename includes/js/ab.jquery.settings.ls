/**
 * @package   CleverStyle CMS
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2011-2015, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
$.ajaxSetup(
	type		: 'post'
	contents	:
		script	: false
	error		: (xhr) !->
		cs.ui.notify(
			if xhr.responseText
				JSON.parse(xhr.responseText).error_description
			else
				cs.Language.connection_error.toString()
			'warning'
			5
		)
)
