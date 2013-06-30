/**
 * @package		CleverStyle CMS
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2013, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
/**
 * Adds method for symbol replacing at specified position
 *
 * @param index
 * @param symbol
 * @return string
 */
String.prototype.replaceAt = function (index, symbol) {
	return this.substr(0, index)+symbol+this.substr(index+symbol.length);
};
/**
 * Debug window opening
 */
function debug_window () {
	$('#debug').dialog('open');
}
/**
 * Cache cleaning
 *
 * @param element
 * @param action
 */
function admin_cache (element, action) {
	var cache_interval	= setInterval(
		function () {
			progress_update(element);
		},
		100
	);
	$(element).html('').progressbar({value : 1});
	$.ajax({
		url		: action,
		success	: function (result) {
			clearInterval(cache_interval);
			setTimeout(
				function () {
					$(element).progressbar('destroy').html(result);
				},
				100
			);
		}
	});
}
/**
 * Updating of progress bar
 *
 * @param element
 */
function progress_update (element) {
	$(element).progressbar(
		'value',
		$(element).progressbar('value') + 1
	);
	if ($(element).progressbar('value') == 100) {
		$(element).progressbar('value', 1);
	}
}
/**
 * Send request for db connection testing
 *
 * @param url
 * @param added
 */
function db_test (url, added) {
	var test_db	= $('#test_db');
	test_db.html('<div id="test_progress"></div>');
	$($('#test_progress')).progressbar({value: 1});
	test_db.dialog('open');
	var test_interval	= setInterval(
		function () {
			progress_update('#test_progress');
		},
		100
	);
	if (added) {
		$.ajax({
			url		: url,
			success	: function (result) {
				clearInterval(test_interval);
				$('#test_db').html(result);
			}
		});
	} else {
		var db = json_encode({
			type		: document.getElementsByName('db[type]').item(0).value,
			name		: document.getElementsByName('db[name]').item(0).value,
			user		: document.getElementsByName('db[user]').item(0).value,
			password	: document.getElementsByName('db[password]').item(0).value,
			host		: document.getElementsByName('db[host]').item(0).value,
			charset		: document.getElementsByName('db[charset]').item(0).value
		});
		$.ajax({
			url		: url,
			data	: {
				db	: db
			},
			success	: function (result) {
				clearInterval(test_interval);
				$('#test_db').html(result);
			}
		});
	}
}
/**
 * Send request for storage connection testing
 *
 * @param url
 * @param added
 */
function storage_test (url, added) {
	var test_storage	= $('#test_storage');
	test_storage.html('<div id="test_progress"></div>');
	$($('#test_progress')).progressbar({value: 1});
	test_storage.dialog('open');
	var test_interval	= setInterval(
		function () {
			progress_update('#test_progress');
		},
		100
	);
	if (added) {
		$.ajax({
			url		: url,
			success	: function (result) {
				clearInterval(test_interval);
				$('#test_storage').html(result);
			}
		});
	} else {
		var storage = json_encode({
			url			: document.getElementsByName('storage[url]').item(0).value,
			host		: document.getElementsByName('storage[host]').item(0).value,
			connection	: document.getElementsByName('storage[connection]').item(0).value,
			user		: document.getElementsByName('storage[user]').item(0).value,
			password	: document.getElementsByName('storage[password]').item(0).value
		});
		$.ajax({
			url		: url,
			data	: 'storage=' + storage,
			success	: function (result) {
				clearInterval(test_interval);
				$('#test_storage').html(result);
			}
		});
	}
}
/**
 * Toggling of blocks group in admin page
 *
 * @param position
 */
function blocks_toggle (position) {
	var	items		= $('#'+position+'_blocks_items'),
		disabled	= $('#'+position+'_blocks_items > li:not(.ui-state-disabled)');
	if (items.attr('data-mode') == 'open') {
		disabled.slideUp('fast');
		items.attr('data-mode', 'close');
	} else {
		disabled.slideDown('fast');
		items.attr('data-mode', 'open');
	}
}
/**
 * Returns the JSON representation of a value
 *
 * @param obj
 * @return string
 */
function json_encode (obj) {
	return $.toJSON(obj);
}
/**
 * Decodes a JSON string
 *
 * @param str
 * @return {}
 */
function json_decode (str) {
	return $.secureEvalJSON(str);
}
/**
 * Supports algorithms sha1, sha224, sha256, sha384, sha512
 *
 * @param {string} algo Chosen algorithm
 * @param {string} data String to be hashed
 * @return string
 */
function hash (algo, data) {
	switch (algo) {
		case 'sha1':
			algo	= 'SHA-1';
		break;
		case 'sha224':
			algo	= 'SHA-224';
		break;
		case 'sha256':
			algo	= 'SHA-256';
		break;
		case 'sha384':
			algo	= 'SHA-384';
		break;
		case 'sha512':
			algo	= 'SHA-512';
		break;
	}
	return (new jsSHA(data, 'ASCII')).getHash(algo, 'HEX');
}
/**
 * Function for setting cookies taking into account cookies prefix
 *
 * @param name
 * @param value
 * @param expires
 * @return bool
 */
function setcookie (name, value, expires) {
	name = cookie_prefix+name;
	if (expires) {
		var	date = new Date();
		date.setTime(expires * 1000);
		expires	= date;
	}
	return !!$.cookie(
		name,
		value,
		{
			expires	: expires,
			path	: cookie_path,
			domain	: cookie_domain,
			secure	: protocol == 'https'
		}
	);
}
/**
 * Function for getting of cookies, taking into account cookies prefix
 *
 * @param name
 * @return bool|string
 */
function getcookie (name) {
	return $.cookie(name);
}
/**
 * Login into system
 *
 * @param {string} login
 * @param {string} password
 */
function login (login, password) {
	login	= login.toLowerCase();
	$.ajax(
		base_url+'/api/System/user/login',
		{
			cache	: false,
			data	: {
				login: hash('sha224', login)
			},
			success	: function (random_hash) {
				if (random_hash.length == 56) {
					$.ajax(
						base_url+"/api/user/login",
						{
							cache	: false,
							data	: {
								login		: hash('sha224', login),
								auth_hash	: hash(
									'sha512',
									hash('sha224', login)+hash('sha512', hash('sha512', password)+public_key)+navigator.userAgent+random_hash
								)
							},
							success	: function (result) {
								if (result == 'reload') {
									location.reload();
								}
							},
							error	: function (xhr) {
								if (xhr.responseText) {
									alert(json_decode(xhr.responseText).error_description);
								} else {
									alert(L.auth_connection_error);
								}
							}
						}
					);
				} else if (random_hash == 'reload') {
					location.reload();
				}
			},
			error	: function (xhr) {
				if (xhr.responseText) {
					alert(json_decode(xhr.responseText).error_description);
				} else {
					alert(L.auth_connection_error);
				}
			}
		}
	);
}
/**
 * Logout
 */
function logout () {
	$.ajax(
		base_url+'/api/System/user/logout',
		{
			cache	: false,
			data	: {
				logout: true
			},
			success	: function () {
				location.reload();
			},
			error	: function (xhr) {
				if (xhr.responseText) {
					alert(json_decode(xhr.responseText).error_description);
				} else {
					alert(L.auth_connection_error);
				}
			}
		}
	);
}
/**
 * Registration in the system
 *
 * @param {string} email
 */
function registration (email) {
	if (!email) {
		alert(L.please_type_your_email);
		return;
	}
	email	= email.toLowerCase();
	$.ajax(
		base_url+'/api/System/user/registration',
		{
			cache	: false,
			data	: {
				email: email
			},
			success	: function (result) {
				if (result == 'reg_confirmation') {
					$('<div>'+L.reg_confirmation+'</div>')
						.appendTo('body')
						.dialog({
							autoOpen	: true,
							modal		: true,
							draggable	: false,
							resizable	: false,
							close		: function () {
								$(this).remove();
							}
						});
				} else if (result == 'reg_success') {
					$('<div>'+L.reg_success+'</div>')
						.appendTo('body')
						.dialog({
							autoOpen	: true,
							modal		: true,
							draggable	: false,
							resizable	: false,
							close		: function () {
								location.reload();
							}
						});
				}
			},
			error	: function (xhr) {
				if (xhr.responseText) {
					alert(json_decode(xhr.responseText).error_description);
				} else {
					alert(L.reg_connection_error);
				}
			}
		}
	);
}
function restore_password (email) {
	if (!email) {
		alert(L.please_type_your_email);
		return;
	}
	email	= email.toLowerCase();
	$.ajax(
		base_url+'/api/System/user/restore_password',
		{
			cache	: false,
			data	: {
				email: hash('sha224', email)
			},
			success	: function (result) {
				if (result == 'OK') {
					$('<div>'+L.restore_password_confirmation+'</div>')
						.appendTo('body')
						.dialog({
							autoOpen	: true,
							modal		: true,
							draggable	: false,
							resizable	: false,
							close		: function () {
								$(this).remove();
							}
						});
				}
			},
			error	: function (xhr) {
				if (xhr.responseText) {
					alert(json_decode(xhr.responseText).error_description);
				} else {
					alert(L.reg_connection_error);
				}
			}
		}
	);
}
/**
 * Password changing
 *
 * @param {string} current_password
 * @param {string} new_password
 */
function change_password (current_password, new_password) {
	if (!current_password) {
		alert(L.please_type_current_password);
		return;
	} else if (!new_password) {
		alert(L.please_type_new_password);
		return;
	} else if (current_password == new_password) {
		alert(L.current_new_password_equal);
		return;
	}
	current_password	= hash('sha512', hash('sha512', current_password)+public_key);
	new_password		= hash('sha512', hash('sha512', new_password)+public_key);
	$.ajax(
		base_url+'/api/System/user/change_password',
		{
			cache	: false,
			data	: {
				verify_hash		: hash('sha224', current_password+session_id),
				new_password	: xor_string(current_password, new_password)
			},
			success	: function (result) {
				if (result == 'OK') {
					alert(L.password_changed_successfully);
				} else {
					alert(result);
				}
			},
			error	: function (xhr) {
				if (xhr.responseText) {
					alert(json_decode(xhr.responseText).error_description);
				} else {
					alert(L.password_changing_connection_error);
				}
			}
		}
	);
}
/**
 * For textarea in blocks editing
 *
 * @param item
 */
function block_switch_textarea (item) {
	$('#block_content_html, #block_content_raw_html').hide();
	switch ($(item).val()) {
		case 'html':
			$('#block_content_html').show();
		break;
		case 'raw_html':
			$('#block_content_raw_html').show();
		break;
	}
}
function base64_encode (str) {
	return window.btoa(str);
}
function base64_decode (str) {
	return window.atob(str);
}
/**
 * Bitwise XOR operation for 2 strings
 *
 * @param {string} string1
 * @param {string} string2
 *
 * @return string
 */
function xor_string (string1, string2) {
	var	len1	= string1.length,
		len2	= string2.length;
	if (len2 > len1) {
		var tmp	= string1;
		string1	= string2;
		string2	= tmp;
		tmp		= len1;
		len1	= len2;
		len2	= tmp;
	}
	for (var i = 0; i < len1; ++i) {
		var pos	= i % len2;
		string1	= string1.replaceAt(i, String.fromCharCode(string1.charCodeAt(i) ^ string2.charCodeAt(pos)));
	}
	return string1;
}
function async_call (functions, timeout) {
	timeout	= timeout || 0;
	var i;
	for (i in functions) {
		setTimeout(functions[i], timeout);
	}
}