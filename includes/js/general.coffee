###*
 * @package		CleverStyle CMS
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2015, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
###
$ ->
	L	= cs.Language
	$.ajaxSetup
		type	: 'post'
		data	:
			session	: cs.getcookie('session')
		error	: (xhr) ->
			UIkit.notify(
				if xhr.responseText
					JSON.parse(xhr.responseText).error_description
				else
					L.connection_error.toString()
				'warning'
			)
	UIkit.modal.labels.Ok		= L.yes.toString()
	UIkit.modal.labels.Cancel	= L.cancel.toString()
	$('.cs-header-sign-in-slide').click ->
		$('.cs-header-guest-form').removeClass('active')
		$('.cs-header-sign-in-form').addClass('active')
		$('.cs-header-sign-in-email').focus()
	$('.cs-header-registration-slide').click ->
		$('.cs-header-guest-form').removeClass('active')
		$('.cs-header-registration-form').addClass('active')
		$('.cs-header-registration-email').focus()
	$('.cs-header-restore-password-slide').click ->
		$('.cs-header-sign-in-form, .cs-header-registration-form').removeClass('active')
		$('.cs-header-restore-password-form').addClass('active')
		$('.cs-header-restore-password-email').focus()
	$('.cs-header-registration-email').keyup (event) ->
		if event.which == 13
			$('.cs-header-registration-process').click()
	$('.cs-header-sign-in-form').submit ->
		cs.sign_in($('.cs-header-sign-in-email').val(), $('.cs-header-user-password').val())
		return false
	$('.cs-header-sign-out-process').click ->
		cs.sign_out()
	$('.cs-show-password').click ->
		$this	= $(this)
		pass_input = $this
			.parent()
				.next()
					.children('input')
		if pass_input.prop('type') == 'password'
			pass_input.prop('type', 'text')
			$this
				.addClass('uk-icon-unlock-alt')
				.removeClass('uk-icon-lock')
		else
			pass_input.prop('type', 'password')
			$this
				.addClass('uk-icon-lock')
				.removeClass('uk-icon-unlock-alt')
	$('#current_password').click ->
		$this		= $(this)
		password	= $('.cs-profile-current-password')
		if password.prop('type') == 'password'
			password.prop('type', 'text')
			$this
				.addClass('uk-icon-unlock-alt')
				.removeClass('uk-icon-lock')
		else
			password.prop('type', 'password')
			$this
				.addClass('uk-icon-lock')
				.removeClass('uk-icon-unlock-alt')
	$('#new_password').click ->
		$this		= $(this)
		password	= $('.cs-profile-new-password')
		if password.prop('type') == 'password'
			password.prop('type', 'text')
			$this
				.addClass('uk-icon-unlock-alt')
				.removeClass('uk-icon-lock')
		else
			password.prop('type', 'password')
			$this
				.addClass('uk-icon-lock')
				.removeClass('uk-icon-unlock-alt')
	$('.cs-header-registration-process').click ->
		if !cs.rules_text
			cs.registration $('.cs-header-registration-email').val()
			return
		modal	= $("""
				<div title="#{L.rules_agree}">
					<div>
						#{cs.rules_text}
						<p class="cs-right">
							<button class="cs-registration-continue uk-button uk-button-primary">#{L.yes}</button>
						</p>
					</div>
				</div>
			""")
			.appendTo('body')
			.cs().modal('show')
			.on(
				'hide.uk.modal'
				->
					$(this).remove()
			)
		modal
			.find('.cs-registration-continue')
			.click ->
				modal.cs().modal('close').remove()
				cs.registration $('.cs-header-registration-email').val()
	$('.cs-header-restore-password-process').click ->
		cs.restore_password $('.cs-header-restore-password-email').val()
	$('.cs-profile-change-password').click ->
		cs.change_password $('.cs-profile-current-password').val(), $('.cs-profile-new-password').val()
	$('.cs-header-back').click ->
		$('.cs-header-guest-form').addClass('active')
		$('.cs-header-registration-form, .cs-header-sign-in-form, .cs-header-restore-password-form').removeClass('active')
	return
