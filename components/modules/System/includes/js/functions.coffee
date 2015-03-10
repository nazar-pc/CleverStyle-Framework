###*
 * @package		CleverStyle CMS
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2015, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
###
L							= cs.Language
###*
 * Get value by name
 *
 * @param {string}	name
 *
 * @return {string}
###
value_by_name				= (name) ->
	document.getElementsByName(name).item(0).value
###*
 * Cache cleaning
 *
 * @param 			element
 * @param {string}	action
###
cs.admin_cache				= (element, action, partial_path) ->
	$(element).html """
		<div class="uk-progress uk-progress-striped uk-active">
			<div class="uk-progress-bar" style="width:100%"></div>
		</div>
	"""
	$.ajax
		url		: action
		data	:
			partial_path	: partial_path
		type	: 'delete'
		success	: (result) ->
			$(element).html(
				if result
					"""<p class="uk-alert uk-alert-success">#{L.done}</p>"""
				else
					"""<p class="uk-alert uk-alert-danger">#{L.error}</p>"""
			)
	return
###*
 * Send request for db connection testing
 *
 * @param {int}	index
 * @param {int}	mirror_index
###
cs.db_test					= (index, mirror_index) ->
	modal	= $.cs.simple_modal("""<div>
		<h3 class="cs-center">#{L.test_connection}</h3>
		<div class="uk-progress uk-progress-striped uk-active">
			<div class="uk-progress-bar" style="width:100%"></div>
		</div>
	</div>""")
	$.ajax(
		url		: 'api/System/admin/databases_test'
		data	:
			if index != undefined
				index			: index
				mirror_index	: mirror_index
			else
				db	:
					type		: value_by_name('db[type]')
					name		: value_by_name('db[name]')
					user		: value_by_name('db[user]')
					password	: value_by_name('db[password]')
					host		: value_by_name('db[host]')
					charset		: value_by_name('db[charset]')
		type	: 'get'
		success	: (result) ->
			if result
				status = 'success'
			else
				status = 'danger'
			result = if result then L.success else L.failed
			modal
				.find('.uk-progress')
				.replaceWith("""<p class="cs-center uk-alert uk-alert-#{status}" style=text-transform:capitalize;">#{result}</p>""")
		error	: ->
			modal
				.find('.uk-progress')
				.replaceWith("""<p class="cs-center uk-alert uk-alert-danger" style=text-transform:capitalize;">#{L.failed}</p>""")
	)
###*
 * Send request for storage connection testing
 *
 * @param {int}	index
###
cs.storage_test				= (index) ->
	modal	= $.cs.simple_modal("""<div>
		<h3 class="cs-center">#{L.test_connection}</h3>
		<div class="uk-progress uk-progress-striped uk-active">
			<div class="uk-progress-bar" style="width:100%"></div>
		</div>
	</div>""")
	$.ajax(
		url		: 'api/System/admin/storages_test'
		data	:
			if index != undefined
				index	: index
			else
				storage	:
					url			: value_by_name('storage[url]')
					host		: value_by_name('storage[host]')
					connection	: value_by_name('storage[connection]')
					user		: value_by_name('storage[user]')
					password	: value_by_name('storage[password]')
		type	: 'get'
		success	: (result) ->
			if result
				status = 'success'
			else
				status = 'danger'
			result = if result then L.success else L.failed
			modal
				.find('.uk-progress')
				.replaceWith("""<p class="cs-center uk-alert uk-alert-#{status}" style=text-transform:capitalize;">#{result}</p>""")
		error	: ->
			modal
				.find('.uk-progress')
				.replaceWith("""<p class="cs-center uk-alert uk-alert-danger" style=text-transform:capitalize;">#{L.failed}</p>""")
	)
###*
 * Toggling of blocks group in admin page
 *
 * @param {string}	position
###
cs.blocks_toggle			= (position) ->
	container	= $("#cs-#{position}-blocks-items")
	items		= container.children('li:not(:first)')
	if container.data('mode') == 'open'
		items.slideUp('fast')
		container.data('mode', 'close')
	else
		items.slideDown('fast')
		container.data('mode', 'open')
	return
###*
 * For textarea in blocks editing
 *
 * @param item
###
cs.block_switch_textarea	= (item) ->
	$('#cs-block-content-html, #cs-block-content-raw-html').hide()
	switch $(item).val()
		when 'html' then $('#cs-block-content-html').show()
		when 'raw_html' then $('#cs-block-content-raw-html').show()
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
