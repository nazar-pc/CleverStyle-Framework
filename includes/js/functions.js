var	save = false;
function menuadmin (item, direct_link) {
	var url = direct_link ? item : current_base_url+'/'+item;
	if (!save) {
		document.location.href = url;
	} else {
		if (confirm(save_before)) {
			$('#admin_form').attr('action', url);
			$('#save_settings').click();
		} else {
			if (confirm(continue_transfer)) {
				document.location.href = url;
			}
		}
	}
}
function debug_window () {
	$('#debug').dialog('open');
}
function admin_cache (element, action) {
	var cache_interval	= setInterval(function () {progress(element)}, 100);
	$(element).html('').progressbar(
		{value: 1}
	).load(
		action, function () {
			clearInterval(cache_interval);
			setTimeout(
				function () {
					$(element).progressbar('destroy');
				},
				100
			);
		}
	);
}
function progress (element) {
	$(element).progressbar('value', $(element).progressbar('value')+1);
	if ($(element).progressbar('value') == 100) {
		$(element).progressbar('value', 1);
	}
}
function db_test (url, added) {
	$('#test_db').html('<div id="test_progress"></div>');
	$($('#test_progress')).progressbar({value: 1});
	$('#test_db').dialog('open');
	var test_interval	= setInterval(function () {progress('#test_progress')}, 100);
	if (added) {
		$.ajax({
			url:		url,
			type:		'POST',
			success:	function(result) {
				clearInterval(test_interval);
				$('#test_db').html(result);
			}
		});
	} else {
		var db = json_encode({
			type:		document.getElementsByName('db[type]').item(0).value,
			name:		document.getElementsByName('db[name]').item(0).value,
			user:		document.getElementsByName('db[user]').item(0).value,
			password:	document.getElementsByName('db[password]').item(0).value,
			host:		document.getElementsByName('db[host]').item(0).value,
			codepage:	document.getElementsByName('db[codepage]').item(0).value
		});
		$.ajax({
			url:		url,
			type:		'POST',
			data:		'db=' + db,
			success:	function(result) {
				clearInterval(test_interval);
				$('#test_db').html(result);
			}
		});
	}
}
function storage_test (url, added) {
	$('#test_storage').html('<div id="test_progress"></div>');
	$($('#test_progress')).progressbar({value: 1});
	$('#test_storage').dialog('open');
	var test_interval	= setInterval(function () {progress('#test_progress')}, 100);
	if (added) {
		$.ajax({
			url:		url,
			type:		'POST',
			success:	function(result) {
				clearInterval(test_interval);
				$('#test_storage').html(result);
			}
		});
	} else {
		var storage = json_encode({
			url:		document.getElementsByName('storage[url]').item(0).value,
			host:		document.getElementsByName('storage[host]').item(0).value,
			connection:	document.getElementsByName('storage[connection]').item(0).value,
			user:		document.getElementsByName('storage[user]').item(0).value,
			password:	document.getElementsByName('storage[password]').item(0).value
		});
		$.ajax({
			url:		url,
			type:		'POST',
			data:		'storage=' + storage,
			success:	function(result) {
				clearInterval(test_interval);
				$('#test_storage').html(result);
			}
		});
	}
}
function blocks_toggle (position) {
	if ($('#'+position+'_blocks_items').attr('data-mode') == 'open') {
		$('#'+position+'_blocks_items > li:not(.ui-state-disabled)').slideUp('fast');
		$('#'+position+'_blocks_items').attr('data-mode', 'close');
	} else {
		$('#'+position+'_blocks_items > li:not(.ui-state-disabled)').slideDown('fast');
		$('#'+position+'_blocks_items').attr('data-mode', 'open');
	}
}
//Для удобства и простоты - обертки для функций JavaScript с названиями аналогичных функций в PHP
function json_encode (obj) {
	return $.toJSON(obj);
}
function json_decode (str) {
	return $.secureEvalJSON(str);
}
//Поддерживает алгоритмы sha224, sha256, sha384, sha512
function hash (algo, data) {
	return (new jsSHA(data)).getHash(algo);
}
function setcookie (name, value, expires, path, domain, secure) {
	return $.cookie(name, value, {expires: expires, path: path ? path : '/', domain: domain, secure: secure});
}
function getcookie (name) {
	return $.cookie(name);
}
/**
 * Login into system
 * @param login
 * @param password
 */
function login (login, password) {
	$.ajax(
		base_url+"/api/user/login",
		{
			type: 'post',
			cache: false,
			data: {
				login: hash('sha224', login)
			},
			success: function(random_hash) {
				if (random_hash.length == 56) {
					$.ajax(
						base_url+"/api/user/login",
						{
							type: 'post',
							cache: false,
							data: {
								auth_hash: hash('sha512', hash('sha224', login)+hash('sha512', password)+navigator.userAgent+random_hash),
								login: hash('sha224', login)
							},
							success: function(result) {
								if (result == 'reload') {
									location.reload();
								} else {
									alert(result);
								}
							},
							error: function() {
								alert(auth_error_connection);
							}
						}
					);
				} else if (random_hash == 'reload') {
					location.reload();
				} else {
					alert(random_hash);
				}
			},
			error: function() {
				alert(auth_error_connection);
			}
		}
	);
}
/**
 * Registration in the system
 * @param email
 */
function registration (email) {
	if (!email) {
		alert(please_type_your_email);
		return;
	}
	$.ajax(
		base_url+"/api/user/registration",
		{
			type: 'post',
			cache: false,
			data: {
				email: email
			},
			success: function(result) {
				if (result == 'reg_confirmation') {
					$('<div>'+reg_confirmation+'</div>')
						.appendTo('body')
						.dialog({
							autoOpen	: true,
							modal		: true,
							draggable	: false,
							resizable	: false,
							close		: function () { $(this).remove(); }
						});
				} else if (result == 'reg_success') {
					$('<div>'+reg_success+'</div>')
						.appendTo('body')
						.dialog({
							autoOpen	: true,
							modal		: true,
							draggable	: false,
							resizable	: false,
							close		: function () { location.reload(); }
						});
				} else {
					alert(result);
				}
			},
			error: function() {
				alert(reg_error_connection);
			}
		}
	);
}