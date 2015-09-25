###*
 * @package    CleverStyle CMS
 * @subpackage System module
 * @category   modules
 * @author     Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright  Copyright (c) 2011-2015, Nazar Mokrynskyi
 * @license    MIT License, see license.txt
###
L	= cs.Language
###*
 * Cache cleaning
 *
 * @param 			element
 * @param {string}	action
###
cs.admin_cache				= (element, action, partial_path) ->
	$(element).html """
		<progress is="cs-progress" infinite></progress>
	"""
	$.ajax
		url		: action
		data	:
			partial_path	: partial_path
		type	: 'delete'
		success	: (result) ->
			$(element).html(
				if result
					"""<p class="cs-block-success cs-text-success">#{L.done}</p>"""
				else
					"""<p class="cs-block-errorcs-text-error">#{L.error}</p>"""
			)
	return
cs.test_email_sending		= () ->
	email = prompt(L.email)
	if email
		$.ajax(
			url		: 'api/System/admin/email_sending_test'
			data	:
				email	: email
			type	: 'get'
			success	: ->
				alert(L.done)
			error	: ->
				alert(L.test_email_sending_failed)
		)
