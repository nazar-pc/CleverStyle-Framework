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
		UIkit.modal.confirm(L.please_type_your_email)
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
		UIkit.modal.alert(L.please_type_your_email)
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
		UIkit.modal.alert(L.please_type_current_password)
		return
	else if !new_password
		UIkit.modal.alert(L.please_type_new_password)
		return
	else if current_password == new_password
		UIkit.modal.alert(L.current_new_password_equal)
		return
	else if String(new_password).length < cs.password_min_length
		UIkit.modal.alert(L.password_too_short)
		return
	else if cs.password_check(new_password) < cs.password_min_strength
		UIkit.modal.alert(L.password_too_easy)
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
					UIkit.modal.alert(L.password_changed_successfully)
			else
				if error
					error()
				else
					UIkit.modal.alert(result)
		error	: ->
			error()
###*
 * Check password strength
 *
 * @param	string	password
 * @param	int		min_length
 *
 * @return	int		In range [0..7]<br><br>
 * 					<b>0</b> - short password<br>
 * 					<b>1</b> - numbers<br>
 *  				<b>2</b> - numbers + letters<br>
 * 					<b>3</b> - numbers + letters in different registers<br>
 * 		 			<b>4</b> - numbers + letters in different registers + special symbol on usual keyboard +=/^ and others<br>
 * 					<b>5</b> - numbers + letters in different registers + special symbols (more than one)<br>
 * 					<b>6</b> - as 5, but + special symbol, which can't be found on usual keyboard or non-latin letter<br>
 * 					<b>7</b> - as 5, but + special symbols, which can't be found on usual keyboard or non-latin letter (more than one symbol)<br>
###
cs.password_check			= (password, min_length) ->
	password	= new String(password)
	min_length	= min_length || 4
	password	= password.replace(/\s+/g, ' ')
	$strength	= 0
	if password.length >= min_length
		matches	= password.match(/[~!@#\$%\^&\*\(\)\-_=+\|\\/;:,\.\?\[\]\{\}]/g)
		if matches
			$strength = 4
			if matches.length > 1
				++$strength
		else
			if /[A-Z]+/.test(password)
				++$strength
			if /[a-z]+/.test(password)
				++$strength
			if /[0-9]+/.test(password)
				++$strength
		matches	= password.match(/[^0-9a-z~!@#\$%\^&\*\(\)\-_=+\|\\/;:,\.\?\[\]\{\}]/ig)
		if matches
			++$strength
			if matches.length > 1
				++$strength
	$strength
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
 * Prepare text to be used as value for html attribute value
 *
 * @param {string}|{string}[] string
 *
 * @return {string}|{string}[]
###
cs.prepare_attr_value		= (string) ->
	if string instanceof Array
		for s in string.slice(0)
			cs.prepare_attr_value(s)
	else
		string
			.replace(/&/g, '&amp;')
			.replace(/'/g, '&apos;')
			.replace(/"/g, '&quot;')
			.replace(/</g, '&lt;')
			.replace(/>/g, '&gt;')
###*
 * Asynchronous execution of array of the functions
 *
 * @param {function[]}	functions
 * @param {int}			timeout
###
cs.async_call				= (functions, timeout) ->
	timeout	= timeout || 0
	for own i of functions
		do (func = functions[i]) ->
			setTimeout (->
				requestAnimationFrame(func)
			), timeout
	return
###*
 * Observe for inserted nodes using `MutationObserver` if available and `addEventListener('DOMNodeInserted', ...)` otherwise
 *
 * @param {Node}		timeout
 * @param {function[]}	callback	Will be called with either directly on inserted node(s) or on some of its parent(s) as argument
###
cs.observe_inserts_on		= (target, callback) ->
	if MutationObserver
		(
			new MutationObserver (mutations) ->
				mutations.forEach (mutation) ->
					if mutation.addedNodes.length
						callback(mutation.addedNodes)
		).observe(
			target
			childList	: true
			subtree		: true
		)
	else
		target.addEventListener(
			'DOMNodeInserted'
			->
				callback(target)
			false
		)
