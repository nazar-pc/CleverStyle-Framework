###*
 * @package    CleverStyle CMS
 * @subpackage System module
 * @category   modules
 * @author     Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright  Copyright (c) 2011-2015, Nazar Mokrynskyi
 * @license    MIT License, see license.txt
###
L	= cs.Language
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
