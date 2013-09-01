###*
 * @package		CleverStyle CMS
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2013, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
###
w							= window
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
 * Adds method for symbol replacing at specified position
 *
 * @param {int}		index
 * @param {string}	symbol
 *
 * @return {string}
###
String.prototype.replaceAt	= (index, symbol) ->
	this.substr(0, index) + symbol + this.substr(index + symbol.length)
###*
 * Debug window opening
###
w.debug_window				= -> $('#cs-debug').cs_modal('show')
###*
 * Cache cleaning
 *
 * @param 			element
 * @param {string}	action
###
w.admin_cache				= (element, action) ->
	$(element).html('<div class="uk-progress uk-progress-striped uk-active"><div class="uk-progress-bar" style="width:100%"></div></div>')
	$.ajax
		url		: action,
		success	: (result) ->
			$(element).html(result)
	return
###*
 * Send request for db connection testing
 *
 * @param {string}	url
 * @param {bool}	added
###
w.db_test					= (url, added) ->
	db_test	= $('#cs-db-test')
	db_test.find('h3 + *').replaceWith('<div class="uk-progress uk-progress-striped uk-active"><div class="uk-progress-bar" style="width:100%"></div></div>')
	db_test.cs_modal('show')
	if added
		$.ajax({
			url		: url,
			success	: (result) ->
				db_test.find('h3 + *').replaceWith(result)
			error	: ->
				db_test.find('h3 + *').replaceWith('<p class="cs-test-result">' + L.failed + '</p>')
		})
	else
		db = json_encode(
			type		: value_by_name('db[type]')
			name		: value_by_name('db[name]')
			user		: value_by_name('db[user]')
			password	: value_by_name('db[password]')
			host		: value_by_name('db[host]')
			charset		: value_by_name('db[charset]')
		)
		$.ajax(
			url		: url
			data	:
				db	: db
			success	: (result) ->
				db_test
					.find('h3 + *')
					.replaceWith(result)
			error	: ->
				db_test
					.find('h3 + *')
					.replaceWith('<p class="cs-test-result">' + L.failed + '</p>')
		)
###*
 * Send request for storage connection testing
 *
 * @param {string}	url
 * @param {bool}	added
###
w.storage_test				= (url, added) ->
	storage_test	= $('#cs-storage-test')
	storage_test
		.find('h3 + *')
		.replaceWith('<div class="uk-progress uk-progress-striped uk-active"><div class="uk-progress-bar" style="width:100%"></div></div>')
	storage_test.cs_modal('show')
	if added
		$.ajax(
			url		: url
			success	: (result) ->
				storage_test
					.find('h3 + *')
					.replaceWith(result)
			error	: ->
				storage_test
					.find('h3 + *')
					.replaceWith('<p class="cs-test-result">' + L.failed + '</p>')
		)
	else
		storage = json_encode(
			url			: value_by_name('storage[url]')
			host		: value_by_name('storage[host]')
			connection	: value_by_name('storage[connection]')
			user		: value_by_name('storage[user]')
			password	: value_by_name('storage[password]')
		)
		$.ajax(
			url		: url
			data	:
				storage	: storage
			success	: (result) ->
				storage_test
					.find('h3 + *')
					.replaceWith(result)
			error	: ->
				storage_test
					.find('h3 + *')
					.replaceWith('<p class="cs-test-result">' + L.failed + '</p>')
		)
###*
 * Toggling of blocks group in admin page
 *
 * @param {string}	position
###
w.blocks_toggle				= (position) ->
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
 * Returns the JSON representation of a value
 *
 * @param {object} obj
 *
 * @return {string}
###
w.json_encode				= (obj) -> $.toJSON(obj)
###*
 * Decodes a JSON string
 *
 * @param {string}	str
 * @return {object}
###
w.json_decode				= (str) -> $.secureEvalJSON(str)
###*
 * Supports algorithms sha1, sha224, sha256, sha384, sha512
 *
 * @param {string} algo Chosen algorithm
 * @param {string} data String to be hashed
 * @return {string}
###
w.hash						= (algo, data) ->
	algo = switch algo
		when 'sha1' then 'SHA-1'
		when 'sha224' then 'SHA-224'
		when 'sha256' then 'SHA-256'
		when 'sha384' then 'SHA-384'
		when 'sha512' then 'SHA-512'
		else algo
	(new jsSHA(data, 'ASCII')).getHash(algo, 'HEX')
###*
 * Function for setting cookies taking into account cookies prefix
 *
 * @param {string}	name
 * @param {string}	value
 * @param {int}		expires
 *
 * @return {bool}
###
w.setcookie					= (name, value, expires) ->
	name	= cookie_prefix + name;
	if expires
		date	= new Date()
		date.setTime(expires * 1000)
		expires	= date
	!!$.cookie(
		name
		value
			path	: cookie_path
			domain	: cookie_domain
			secure	: protocol == 'https'
	)
###*
 * Function for getting of cookies, taking into account cookies prefix
 *
 * @param {string}			name
 *
 * @return {bool|string}
###
w.getcookie					= (name) -> $.cookie(name)
###*
 * Login into system
 *
 * @param {string} login
 * @param {string} password
###
w.login						= (login, password) ->
	login	= login.toLowerCase()
	$.ajax(
		base_url + '/api/System/user/login'
			cache	: false
			data	:
				login: hash('sha224', login)
			type	: 'post'
			success	: (random_hash) ->
				if random_hash.length == 56
					$.ajax(
						base_url + '/api/user/login'
							cache	: false
							data	:
								login		: hash('sha224', login)
								auth_hash	: hash(
									'sha512',
									hash('sha224', login) + hash('sha512', hash('sha512', password) + public_key) + navigator.userAgent + random_hash
								)
							type	: 'post'
							success	: (result) ->
								if result == 'reload'
									location.reload()
							error	: (xhr) ->
								if xhr.responseText
									alert(json_decode(xhr.responseText).error_description)
								else
									alert(L.auth_connection_error)
					)
				else if random_hash == 'reload'
					location.reload()
			error	: (xhr) ->
				if xhr.responseText
					alert(json_decode(xhr.responseText).error_description)
				else
					alert(L.auth_connection_error)
	)
###*
 * Logout
###
w.logout					= ->
	$.ajax(
		base_url + '/api/System/user/logout'
			cache	: false
			data	:
				logout: true
			type	: 'post'
			success	: ->
				location.reload()
			error	: (xhr) ->
				if xhr.responseText
					alert(json_decode(xhr.responseText).error_description)
				else
					alert(L.auth_connection_error)
	)
###*
 * Registration in the system
 *
 * @param {string} email
###
w.registration				= (email) ->
	if !email
		alert(L.please_type_your_email)
		return
	email	= email.toLowerCase()
	$.ajax(
		base_url + '/api/System/user/registration'
			cache	: false
			data	:
				email: email
			type	: 'post'
			success	: (result) ->
				if result == 'reg_confirmation'
					$('<div>' + L.reg_confirmation + '</div>')
						.appendTo('body')
						.cs_modal('show')
						.on(
							'uk.modal.hide'
							->
								$(this).remove()
						)
				else if result == 'reg_success'
					$('<div>' + L.reg_success + '</div>')
						.appendTo('body')
						.cs_modal('show')
						.on(
							'uk.modal.hide'
							->
								location.reload()
						)
			error	: (xhr) ->
				if xhr.responseText
					alert(json_decode(xhr.responseText).error_description)
				else
					alert(L.reg_connection_error)
	)
###*
 * Password restoring
 *
 * @param {string} email
###
w.restore_password			= (email) ->
	if !email
		alert(L.please_type_your_email)
		return
	email	= email.toLowerCase()
	$.ajax(
		base_url + '/api/System/user/restore_password',
		{
			cache	: false,
			data	: {
				email: hash('sha224', email)
			},
			type	: 'post',
			success	: (result) ->
				if result == 'OK'
					$('<div>' + L.restore_password_confirmation + '</div>')
						.appendTo('body')
						.cs_modal('show')
						.on(
							'uk.modal.hide'
							->
								$(this).remove()
						)
			error	: (xhr) ->
				if xhr.responseText
					alert(json_decode(xhr.responseText).error_description)
				else
					alert(L.reg_connection_error)
		}
	)
###*
 * Password changing
 *
 * @param {string} current_password
 * @param {string} new_password
###
w.change_password			= (current_password, new_password) ->
	if !current_password
		alert(L.please_type_current_password)
		return
	else if !new_password
		alert(L.please_type_new_password)
		return
	else if current_password == new_password
		alert(L.current_new_password_equal)
		return
	current_password	= hash('sha512', hash('sha512', current_password) + public_key)
	new_password		= hash('sha512', hash('sha512', new_password) + public_key)
	$.ajax(
		base_url + '/api/System/user/change_password',
		{
			cache	: false,
			data	: {
				verify_hash		: hash('sha224', current_password + session_id),
				new_password	: xor_string(current_password, new_password)
			},
			type	: 'post',
			success	: (result) ->
				if result == 'OK'
					alert(L.password_changed_successfully)
				else
					alert(result)
			error	: (xhr) ->
				if xhr.responseText
					alert(json_decode(xhr.responseText).error_description)
				else
					alert(L.password_changing_connection_error)
		}
	)
###*
 * For textarea in blocks editing
 *
 * @param item
###
w.block_switch_textarea		= (item) ->
	$('#cs-block-content-html, #cs-block-content-raw-html').hide()
	switch $(item).val()
		when 'html' then $('#cs-block-content-html').show()
		when 'raw_html' then $('#cs-block-content-raw-html').show()
	return
###*
 * Encodes data with MIME base64
 *
 * @param {string} str
###
w.base64_encode				= (str) -> window.btoa(str)
###*
 * Encodes data with MIME base64
 *
 * @param {string} str
###
w.base64_decode				= (str) -> window.atob(str)
###*
 * Bitwise XOR operation for 2 strings
 *
 * @param {string} string1
 * @param {string} string2
 *
 * @return {string}
###
w.xor_string				= (string1, string2) ->
	len1	= string1.length
	len2	= string2.length
	if len2 > len1
		[string1, string2, len1, len2]	= [string2, string1, len2, len1]
	for j in [0...len1]
		pos	= j % len2
		string1	= string1.replaceAt(j, String.fromCharCode(string1.charCodeAt(j) ^ string2.charCodeAt(pos)))
	string1
###*
 * Asynchronous execution of array of the functions
 *
 * @param {function[]}	functions
 * @param {int}			timeout
###
w.async_call				= (functions, timeout) ->
	timeout	= timeout || 0;
	for own i of functions
		setTimeout functions[i], timeout
	return