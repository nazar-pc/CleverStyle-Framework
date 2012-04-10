$(function() {
	if (
		($.browser.msie && $.browser.version < 9) ||
		($.browser.opera && $.browser.version < 11.1) ||
		($.browser.webkit && $.browser.version < 534.24) ||
		($.browser.mozilla && $.browser.version < 4)
	) {
		alert('Go away with your old browser! And come back with newer version, than now:)');
	}
	$(":radio").each(function () {
		if (!$(this).hasClass('noui')) {
			$(this).parent().buttonset();
		}
	});
	$(":checkbox").each(function () {
		if (!$(this).hasClass('noui')) {
			if ($(this).parent('label')) {
				$(this).parent().buttonset();
			} else {
				$(this).button();
			}
		}
	});
	$("select").each(function () {
		if (!$(this).hasClass('noui')) {
			//$(this).chosen(); //TODO Find some good replacement (or wait for jQuery UI 1.9)
		}
	});
	$(":button").each(function (index) {
		if (!$(this).hasClass('noui')) {
			$(this).button();
		}
	});
	$('.ui-button').disableSelection();
	$('#debug').dialog({
		autoOpen:	false,
		height:		'400',
		hide:		'puff',
		show:		'scale',
		width:		'700'
	});
	$('.dialog').each(function () {
		if ($(this).attr('data-dialog')) {
			$(this).dialog($.secureEvalJSON($(this).attr('data-dialog')));
		} else {
			$(this).dialog();
		}
	});
	$('#admin_form *').change(function(){
		save = true;
	});
	$('#admin_form:reset').change(function(){
		save = false;
	});
	$('textarea').each(function () {
		if (!$(this).is('.EDITOR, .EDITORH, .SEDITOR, .noresize')) {
			$(this).autoResize();
		}
	});
	$('#login_slide').click(function () {
		$('#anonym_header_form').slideUp();
		$('#login_header_form').slideDown();
		$('#user_login').focus();
	});
	$('#registration_slide').click(function () {
		$('#anonym_header_form').slideUp();
		$('#register_header_form').slideDown();
		$('#register').focus();
	});
	$('#login_list').change(function() {
		$('#user_login').val(this.value);
		$('#user_login').focus();
		if (this.value) {
			$('#user_password, #show_password').hide();
		} else {
			$('#user_password, #show_password').show();
		}
	});
	$('#user_login, #user_password').keyup(function (event) {
		if (event.which == 13) {
			$('#login_process').mousedown();
		}
	});
	$('#register').keyup(function (event) {
		if (event.which == 13) {
			$('#register_process').mousedown();
		}
	});
	$('#register_list').change(function() {
		$('#register').val(this.value);
		$('#register').focus();
	});
	$('#login_process').mousedown(function() {
		login($('#user_login').val(), $('#user_password').val());
	});
	$('#show_password').mousedown(function() {
		if ($('#user_password').prop('type') == 'password') {
			$('#user_password').prop('type', 'text');
			$(this).addClass('ui-icon-unlocked').removeClass('ui-icon-locked');
		} else {
			$('#user_password').prop('type', 'password');
			$(this).addClass('ui-icon-locked').removeClass('ui-icon-unlocked');
		}
	});
	$('#register_process').mousedown(function() {
		$('<div title="'+rules_agree+'">'+rules_text+'</div>')
			.appendTo('body')
			.dialog({
				autoOpen	: true,
				modal		: true,
				buttons		: [
					{
						text	: yes,
						click	: function () {
							$(this).dialog('close');
							registration($('#register').val());
						}
					},
					{
						text	: no,
						click	: function () {
							$(this).dialog('close');
						}
					}
				]
			});
	});
	$('.restore_password').mousedown(function() {
		//TODO Restore password processing
	});
	$('.header_back').click(function() {
		$('#anonym_header_form').slideDown();
		$('#register_header_form').slideUp();
		$('#login_header_form').slideUp();
	});
	$('.reload_button').click(function () {
		location.reload();
	});
	$('.blocks_items_title+a, .blocks_items_title+a+a').click(function () {
		menuadmin(this.href, true); return false;
	});
	$('#change_theme, #change_color_scheme, #change_language').click(function () {
		$('#apply_settings').click();
	});
	$('#change_active_themes').change(function () {
		$(this).find('option[value=\''+$('#change_theme').val()+'\']').prop('selected', true);
	});
	$('#change_active_languages').change(function () {
		$(this).find('option[value=\''+$('#change_language').val()+'\']').prop('selected', true);
	});
	$('#system_readme_open').mousedown(function () {
		$('#system_readme').dialog('open');
	});
	$('#system_license_open').mousedown(function () {
		$('#system_license').dialog('open');
	});
	$('#debug_objects_toggle').click(function () {
		$('#debug_objects').toggle(500);
		if($(this).hasClass('open')){
			var add = '<span class="ui-icon ui-icon-triangle-1-e" style="display: inline-block;"></span>';
			$(this).removeClass('open');
		} else {
			var add = '<span class="ui-icon ui-icon-triangle-1-se" style="display: inline-block;"></span>';
			$(this).addClass('open');
		}
		$(this).html(add+objects);
	});
	$('#debug_user_toggle').click(function () {
		$('#debug_user').toggle(500);
		if($(this).hasClass('open')){
			var add = '<span class="ui-icon ui-icon-triangle-1-e" style="display: inline-block;"></span>';
			$(this).removeClass('open');
		} else {
			var add = '<span class="ui-icon ui-icon-triangle-1-se" style="display: inline-block;"></span>';
			$(this).addClass('open');
		}
		$(this).html(add+user_data);
	});
	$('#debug_queries_toggle').click(function () {
		$('#debug_queries').toggle(500);
		if($(this).hasClass('open')) {
			var add = '<span class="ui-icon ui-icon-triangle-1-e" style="display: inline-block;"></span>';
			$(this).removeClass('open');
		} else {
			var add = '<span class="ui-icon ui-icon-triangle-1-se" style="display: inline-block;"></span>';
			$(this).addClass('open');
		}
		$(this).html(add+queries);
	});
	$('#debug_cookies_toggle').click(function () {
		$('#debug_cookies').toggle(500);
		if($(this).hasClass('open')) {
			var add = '<span class="ui-icon ui-icon-triangle-1-e" style="display: inline-block;"></span>';
			$(this).removeClass('open');
		}else{
			var add = '<span class="ui-icon ui-icon-triangle-1-se" style="display: inline-block;"></span>';
			$(this).addClass('open');
		}
		$(this).html(add+cookies);
	});
	$('#search_users_tabs').tabs({
		collapsible:	true,
		cookie:			{}
	});
	$('#group_permissions_tabs').tabs();
	$('button.permissions_group_invert').mousedown(function () {
		$(this).parentsUntil('div').find(':radio:not(:checked)[value!=-1]').prop('checked', true).button('refresh');
	});
	$('button.permissions_group_allow_all').mousedown(function () {
		$(this).parentsUntil('div').find(':radio:[value=1]').prop('checked', true).button('refresh');
	});
	$('button.permissions_group_deny_all').mousedown(function () {
		$(this).parentsUntil('div').find(':radio:[value=0]').prop('checked', true).button('refresh');
	});
	$('#columns_settings ol').css({
		'list-style-type'	: 'none',
		'margin'			: 0,
		'padding'			: 0
	}).selectable({
		stop: function() {
			var result = new Array();
			$(".ui-selected", this).each(function() {
				result.push($(this).text().trim());
			});
			$("#columns").val(result.join(';'));
		}
	}).children('li').css({//TODO Serialization of selected items and accounting last search (page changing)
		'margin'			: '3px',
		'padding'			: '5px',
		'width'				: 'auto'
	}).addClass('ui-widget-content');
	if (in_admin && module == 'System' && routing[0] == 'components' && routing[1] == 'blocks' && routing[2] != 'settings') {
		$('#apply_settings, #save_settings').click(
			function () {
				$('#position').val(
					json_encode({
						top:		$('#top_blocks_items').sortable('toArray'),
						left:		$('#left_blocks_items').sortable('toArray'),
						floating:	$('#floating_blocks_items').sortable('toArray'),
						right:		$('#right_blocks_items').sortable('toArray'),
						bottom:		$('#bottom_blocks_items').sortable('toArray')
					})
				);
			}
		);
		$('#top_blocks_items, #left_blocks_items, #floating_blocks_items, #right_blocks_items, #bottom_blocks_items').sortable({
			connectWith:	'.blocks_items',
			placeholder:	'ui-state-default',
			items:			'li:not(.ui-state-disabled)',
			cancel:			'.ui-state-disabled',
			update:			function () {save = true;}
		}).disableSelection();
	}
});