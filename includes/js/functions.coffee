###*
 * @package		CleverStyle CMS
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2015, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
###
L							= cs.Language
###*
 * Adds method for symbol replacing at specified position
 *
 * @param {int}		index
 * @param {string}	symbol
 *
 * @return {string}
###
String::replaceAt			= (index, symbol) ->
	this.substr(0, index) + symbol + this.substr(index + symbol.length)
###*
 * Supports algorithms sha1, sha224, sha256, sha384, sha512
 *
 * @param {string} algo Chosen algorithm
 * @param {string} data String to be hashed
 * @return {string}
###
cs.hash						= (algo, data) ->
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
cs.setcookie				= (name, value, expires) ->
	name	= cs.cookie_prefix + name
	options	=
		path	: cs.cookie_path
		domain	: cs.cookie_domain
		secure	: cs.protocol == 'https'
	if !value
		return $.removeCookie(
			name
		)
	if expires
		date	= new Date()
		date.setTime(expires * 1000)
		options.expires	= date
	!!$.cookie(
		name
		value
		options
	)
###*
 * Function for getting of cookies, taking into account cookies prefix
 *
 * @param {string}			name
 *
 * @return {bool|string}
###
cs.getcookie				= (name) ->
	name	= cs.cookie_prefix + name
	$.cookie(name)
###*
 * Sign in into system
 *
 * @param {string} login
 * @param {string} password
###
cs.sign_in					= (login, password) ->
	login		= String(login).toLowerCase()
	password	= String(password)
	$.ajax
		url		: 'api/System/user/sign_in'
		cache	: false
		data	:
			login		: cs.hash('sha224', login)
			password	: cs.hash('sha512', cs.hash('sha512', password) + cs.public_key)
		type	: 'post'
		success	: ->
			location.reload()
###*
 * Sign out
###
cs.sign_out					= ->
	$.ajax
		url		: 'api/System/user/sign_out'
		cache	: false
		data	:
			sign_out: true
		type	: 'post'
		success	: ->
			location.reload()
###*
 * Registration in the system
 *
 * @param {string} email
###
cs.registration				= (email) ->
	if !email
		alert(L.please_type_your_email)
		return
	email	= String(email).toLowerCase()
	$.ajax
		url		: 'api/System/user/registration'
		cache	: false
		data	:
			email: email
		type	: 'post'
		success	: (result) ->
			if result == 'reg_confirmation'
				$('<div>' + L.reg_confirmation + '</div>')
					.appendTo('body')
					.cs().modal('show')
					.on(
						'hide.uk.modal'
						->
							$(this).remove()
					)
			else if result == 'reg_success'
				$('<div>' + L.reg_success + '</div>')
					.appendTo('body')
					.cs().modal('show')
					.on(
						'hide.uk.modal'
						->
							location.reload()
					)
###*
 * Password restoring
 *
 * @param {string} email
###
cs.restore_password			= (email) ->
	if !email
		alert(L.please_type_your_email)
		return
	email	= String(email).toLowerCase()
	$.ajax
		url		: 'api/System/user/restore_password'
		cache	: false,
		data	:
			email: cs.hash('sha224', email)
		type	: 'post'
		success	: (result) ->
			if result == 'OK'
				$('<div>' + L.restore_password_confirmation + '</div>')
					.appendTo('body')
					.cs().modal('show')
					.on(
						'hide.uk.modal'
						->
							$(this).remove()
					)
###*
 * Password changing
 *
 * @param {string} current_password
 * @param {string} new_password
 * @param {Function} success
 * @param {Function} error
###
cs.change_password			= (current_password, new_password, success, error) ->
	if !current_password
		alert(L.please_type_current_password)
		return
	else if !new_password
		alert(L.please_type_new_password)
		return
	else if current_password == new_password
		alert(L.current_new_password_equal)
		return
	current_password	= cs.hash('sha512', cs.hash('sha512', String(current_password)) + cs.public_key)
	new_password		= cs.hash('sha512', cs.hash('sha512', String(new_password)) + cs.public_key)
	$.ajax
		url		: 'api/System/user/change_password'
		cache	: false
		data	:
			current_password	: current_password
			new_password		: new_password
		type	: 'post'
		success	: (result) ->
			if result == 'OK'
				if success
					success()
				else
					alert(L.password_changed_successfully)
			else
				if error
					error()
				else
					alert(result)
		error	: ->
			error()
###*
 * Encodes data with MIME base64
 *
 * @param {string} str
###
cs.base64_encode			= (str) -> window.btoa(str)
###*
 * Encodes data with MIME base64
 *
 * @param {string} str
###
cs.base64_decode			= (str) -> window.atob(str)
###*
 * Bitwise XOR operation for 2 strings
 *
 * @param {string} string1
 * @param {string} string2
 *
 * @return {string}
###
cs.xor_string				= (string1, string2) ->
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
cs.async_call				= (functions, timeout) ->
	timeout	= timeout || 0
	for own i of functions
		setTimeout functions[i], timeout
	return
