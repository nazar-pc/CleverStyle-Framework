/**
 * @package		CleverStyle CMS
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2012, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
$(function() {
	var	body = $('#body');
	body.css(
		'opacity',
		.0001
	).parent().css({
		'padding'	: 0,
		'overflow'	: 'visible'
	});
	$('link[rel="stylesheet"]').each(function () {
		$.setTemplateLayout($(this).attr('href'));
	});
	$.showPage	= function () {
		body.animate(
			{
				'opacity'	: 1
			},
			100
		);
	};
	$(document).on('DOMNodeInserted', 'iframe', function() {
		setTimeout($.redoTemplateLayout, 100);
	});
	setInterval($.redoTemplateLayout, 1000);
	if (
		($.browser.msie && $.browser.version < 10) ||
		($.browser.opera && $.browser.version < 11.1) ||
		($.browser.webkit && $.browser.version < 534.24) ||
		($.browser.mozilla && $.browser.version < 4)
	) {
		alert('Go away with your old browser! And come back with newer version, than now:)');
	}
	var data = {};
	data[session_id] = session_id;
	$.ajaxSetup({
		data	: data,
		type	: 'post'
	});
	$('input:not(:radio, :checkbox, :submit, :image, :reset, :button, .cs-noui), select:not(.cs-noui)').addClass('cs-form-element');
	$(':radio:not(.cs-noui)').each(function () {
		$(this).parent().buttonset();
	});
	$(':checkbox:not(.cs-noui)').each(function () {
		if ($(this).parent('label')) {
			$(this).parent().buttonset();
		} else {
			$(this).button();
		}
	});
	$('select:not(.cs-noui)').each(function () {
		//$(this).chosen(); //TODO Find some good replacement (or wait for jQuery UI 1.9)
	});
	$(':button:not(.cs-noui), .cs-button, .cs-button-compact').each(function () {
		$(this).button();
	});
	$('.ui-button').disableSelection();
	$('#debug').dialog({
		autoOpen:	false,
		height:		'400',
		hide:		'puff',
		show:		'scale',
		width:		'700'
	});
	$('.cs-dialog').each(function () {
		if ($(this).attr('data-dialog')) {
			$(this).dialog($.secureEvalJSON($(this).attr('data-dialog')));
		} else {
			$(this).dialog();
		}
	});
	$('textarea:not(.cs-noui)').addClass('cs-form-element').not('.noresize, .EDITOR, .EDITORH, .SEDITOR').each(function () {
		$(this).autoResize();
	});
	$('.cs-header-login-slide').click(function () {
		$('.cs-header-anonym-form').slideUp();
		$('.cs-header-login-form').slideDown();
		$('.cs-header-login-email').focus();
	});
	$('.cs-header-registration-slide').click(function () {
		$('.cs-header-anonym-form').slideUp();
		$('.cs-header-register-form').slideDown();
		$('.cs-header-registration-email').focus();
	});
	$('.cs-header-restore-password-slide').click(function () {
		$('.cs-header-login-form, .cs-header-register-form').slideUp();
		$('.cs-header-restore-password-form').slideDown();
		$('.cs-header-restore-password-email').focus();
	});
	$('.cs-header-login-email, .cs-header-user-password').keyup(function (event) {
		if (event.which == 13) {
			$('.cs-header-login-process').click();
		}
	});
	$('.cs-header-registration-email').keyup(function (event) {
		if (event.which == 13) {
			$('.cs-header-register-process').click();
		}
	});
	$('.cs-header-login-process').click(function() {
		login($('.cs-header-login-email').val(), $('.cs-header-user-password').val());
	});
	$('.cs-header-logout-process').click(function() {
		logout();
	});
	$('.cs-header-show-password').click(function() {
		var	password	= $('.cs-header-user-password');
		if (password.prop('type') == 'password') {
			password.prop('type', 'text');
			$(this).addClass('ui-icon-unlocked').removeClass('ui-icon-locked');
		} else {
			password.prop('type', 'password');
			$(this).addClass('ui-icon-locked').removeClass('ui-icon-unlocked');
		}
	});
	$('.cs-show-password').click(function() {
		var pass_input = $(this).parent().next().children('input');
		if (pass_input.prop('type') == 'password') {
			pass_input.prop('type', 'text');
			$(this).addClass('ui-icon-unlocked').removeClass('ui-icon-locked');
		} else {
			pass_input.prop('type', 'password');
			$(this).addClass('ui-icon-locked').removeClass('ui-icon-unlocked');
		}
	});
	$('#current_password').click(function() {
		var	password	= $('.cs-profile-current-password');
		if (password.prop('type') == 'password') {
			password.prop('type', 'text');
			$(this).addClass('ui-icon-unlocked').removeClass('ui-icon-locked');
		} else {
			password.prop('type', 'password');
			$(this).addClass('ui-icon-locked').removeClass('ui-icon-unlocked');
		}
	});
	$('#new_password').click(function() {
		var	password	= $('.cs-profile-new-password');
		if (password.prop('type') == 'password') {
			password.prop('type', 'text');
			$(this).addClass('ui-icon-unlocked').removeClass('ui-icon-locked');
		} else {
			password.prop('type', 'password');
			$(this).addClass('ui-icon-locked').removeClass('ui-icon-unlocked');
		}
	});
	$('.cs-header-register-process').click(function() {
		$('<div title="'+L.rules_agree+'">'+rules_text+'</div>')
			.appendTo('body')
			.dialog({
				autoOpen	: true,
				modal		: true,
				buttons		: [
					{
						text	: L.yes,
						click	: function () {
							$(this).dialog('close');
							registration($('.cs-header-registration-email').val());
						}
					},
					{
						text	: L.no,
						click	: function () {
							$(this).dialog('close');
						}
					}
				]
			});
	});
	$('.cs-header-restore-password-process').click(function() {
		restore_password($('.cs-header-restore-password-email').val());
	});
	$('.cs-profile-change-password').click(function() {
		change_password($('.cs-profile-current-password').val(), $('.cs-profile-new-password').val());
	});
	$('.cs-header-back').click(function() {
		$('.cs-header-anonym-form').slideDown();
		$('.cs-header-register-form, .cs-header-login-form, .cs-header-restore-password-form').slideUp();
	});
	$('#debug_window_tabs').tabs({
		collapsible:	true
	});
	if (in_admin) {
		$('.cs-reload-button').click(function () {
			location.reload();
		});
		$('#change_theme, #change_color_scheme, #change_language').click(function () {
			$('#apply_settings').click();
		});
		$('#change_active_themes').change(function () {
			$(this).find("option[value='"+$('#change_theme').val()+"']").prop('selected', true);
		});
		$('#change_active_languages').change(function () {
			$(this).find("option[value='"+$('#change_language').val()+"']").prop('selected', true);
		});
		$('#system_license_open').click(function () {
			$('#system_license').dialog('open');
		});
		$('#search_users_tabs').tabs({
			collapsible:	true,
			cookie:			{}
		});
		$('#group_permissions_tabs, #user_permissions_tabs, #block_permissions_tabs').tabs();
		$('button.cs-permissions-invert').click(function () {
			$(this).parentsUntil('div').find(':radio:not(:checked)[value!=-1]').prop('checked', true).button('refresh');
		});
		$('button.cs-permissions-allow-all').click(function () {
			$(this).parentsUntil('div').find(':radio:[value=1]').prop('checked', true).button('refresh');
		});
		$('button.cs-permissions-deny-all').click(function () {
			$(this).parentsUntil('div').find(':radio:[value=0]').prop('checked', true).button('refresh');
		});
		$('#columns_settings ul').css({
			'list-style-type'	: 'none',
			'margin'			: 0,
			'padding'			: 0
		}).selectable({
			stop: function() {
				var result = [];
				$(".ui-selected", this).each(function() {
					result.push($(this).text().trim());
				});
				$("#columns").val(result.join(';'));
			}
		}).children('li').css({
			'margin'			: '3px',
			'padding'			: '5px',
			'width'				: 'auto'
		}).addClass('ui-widget-content');
		$('#block_users_search').keyup(function () {
			if (event.which != 13) {
				return;
			}
			$('.cs-block-users-changed').removeClass('cs-block-users-changed').appendTo('#block_users_changed_permissions').each(function () {
				var	id		= $(this).find(':radio:first').attr('name'),
					found	= $('#block_users_search_found');
				found.val(
					found.val()+','+id.substring(6, id.length-1)
				);
			});
			$('#block_users_search_results').load(
				current_base_url+'/'+routing[0]+'/'+routing[1]+'/search_users',
				{
					found_users		: $('#block_users_search_found').val(),
					permission		: $(this).attr('permission'),
					search_phrase	: $(this).val()
				},
				function () {
					$('#block_users_search_results :radio').each(function () {
						$(this).parent().buttonset();
					}).change(function () {
						$(this).parentsUntil('tr').parent().addClass('cs-block-users-changed');
					});
				}
			);
		}).keydown(function () {
			return event.which != 13;
		});
		$('#top_blocks_items, #left_blocks_items, #floating_blocks_items, #right_blocks_items, #bottom_blocks_items').sortable({
			connectWith:	'.cs-blocks-items',
			placeholder:	'ui-state-default',
			items:			'li:not(.ui-state-disabled)',
			cancel:			'.ui-state-disabled',
			stop: function () {
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
		}).disableSelection();
		$('#cs-users-groups-list, #cs-users-groups-list-selected').sortable({
			connectWith:	'#cs-users-groups-list, #cs-users-groups-list-selected',
			placeholder:	'ui-state-default',
			items:			'li:not(.ui-state-disabled)',
			cancel:			'.ui-state-disabled',
			stop: function () {
				$('#cs-users-groups-list li').removeClass('ui-widget-header').addClass('ui-widget-content');
				$('#cs-users-groups-list-selected li').removeClass('ui-widget-content').addClass('ui-widget-header');
				$('#user_groups').val(
					json_encode(
						$('#cs-users-groups-list-selected').sortable('toArray')
					)
				);
			}
		}).disableSelection();
		$('#auto_translation_engine select').change(function () {
			$('#auto_translation_engine_settings').html(base64_decode($(this).children(':selected').data('settings')));
		});
	}
});