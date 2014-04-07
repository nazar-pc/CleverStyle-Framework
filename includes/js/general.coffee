###*
 * @package		CleverStyle CMS
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2014, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
###
$ ->
	L	= cs.Language
	cs.async_call [
		->
			window.session_id	= cs.getcookie('session')
			$.ajaxSetup
				type	: 'post'
				data	:
					session	: session_id
				error	: (xhr) ->
					if xhr.responseText
						alert(cs.json_decode(xhr.responseText).error_description)
					else
						alert(L.connection_error)
		->
			L[key]		= (do (translation) ->
				result	= ->
					vsprintf translation, Array::slice.call(arguments)
				result.toString	= ->
					translation
				result
			) for own key, translation of L
			L.get		= (key) ->
				L[key].toString()
			L.format	= (key) ->
				L[key] arguments[1]
			return
		->
			$('form:not(.cs-no-ui)').addClass('uk-form')
		->
			$('input:radio:not(.cs-no-ui)').cs().radio()
		->
			$('input:checkbox:not(.cs-no-ui)').cs().checkbox()
		->
			$('.cs-table').addClass('uk-table uk-table-condensed uk-table-hover')
		->
			$(':button:not(.cs-no-ui), .cs-button, .cs-button-compact')
				.addClass('uk-button')
				.disableSelection()
		->
			$('textarea:not(.cs-no-ui)')
				.not('.cs-no-resize, .EDITOR, .SIMPLE_EDITOR')
				.autosize
					append	: "\n"
		->
			$('.SIMPLEST_INLINE_EDITOR')
				.prop('contenteditable', true)
		->
			$('[data-title]').cs().tooltip()
		->
			$('.cs-tabs').cs().tabs()
		->
			$('.cs-header-sign-in-slide').click ->
				$('.cs-header-guest-form').hide('medium')
				$('.cs-header-sign-in-form').show('medium')
				$('.cs-header-sign-in-email').focus()
			$('.cs-header-registration-slide').click ->
				$('.cs-header-guest-form').hide('medium')
				$('.cs-header-registration-form').show('medium')
				$('.cs-header-registration-email').focus()
			$('.cs-header-restore-password-slide').click ->
				$('.cs-header-sign-in-form, .cs-header-registration-form').hide('medium')
				$('.cs-header-restore-password-form').show('medium')
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
						'uk.modal.hide'
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
				$('.cs-header-guest-form').show('medium')
				$('.cs-header-registration-form, .cs-header-sign-in-form, .cs-header-restore-password-form').hide('medium')
		->
			if cs.in_admin
				$('.cs-reload-button').click ->
					location.reload()
				$('#change_theme, #change_color_scheme, #change_language').click ->
					$('#apply_settings').click()
				$('#change_active_languages').change ->
					$(this)
						.find("option[value='" + $('#change_language').val() + "']")
						.prop('selected', true)
				$('#cs-system-license-open').click ->
					$('#cs-system-license').cs().modal('show')
				$('.cs-permissions-invert').click ->
					$(this)
						.parentsUntil('div')
						.find(':radio:not(:checked)[value!=-1]')
						.prop('checked', true)
						.change()
				$('.cs-permissions-allow-all').click ->
					$(this)
						.parentsUntil('div')
						.find(':radio[value=1]')
						.prop('checked', true)
						.change()
				$('.cs-permissions-deny-all').click ->
					$(this)
						.parentsUntil('div')
						.find(':radio[value=0]')
						.prop('checked', true)
						.change()
				$('#cs-users-search-columns').selectable
					stop: ->
						result	= []
						li		= $(this).children('li')
						li
							.filter('.uk-button-primary:not(.ui-selected)')
							.removeClass('uk-button-primary')
						li
							.filter('.ui-selected')
							.addClass('uk-button-primary')
							.each ->
								result.push $(this).text().trim()
						$('#cs-users-search-selected-columns').val(result.join(';'))
				$('#block_users_search')
					.keyup (event) ->
						if event.which != 13
							return
						$('.cs-block-users-changed')
							.removeClass('cs-block-users-changed')
							.appendTo('#cs-block-users-changed-permissions')
							.each ->
								id		= $(this).find(':radio:first').attr('name')
								found	= $('#cs-block-users-search-found')
								found.val(
									found.val() + ',' + id.substring(6, id.length-1)
								)
						$.ajax
							url		: "#{cs.current_base_url}/#{cs.route[0]}/#{cs.route[1]}/search_users"
							data	:
								found_users		: $('#cs-block-users-search-found').val(),
								permission		: $(this).attr('permission'),
								search_phrase	: $(this).val()
							,
							success	: (result) ->
								$('#block_users_search_results')
									.html(result)
									.find(':radio')
									.cs().radio()
									.change ->
										$(this)
											.parentsUntil('tr')
											.parent()
											.addClass('cs-block-users-changed')
					.keydown (event) ->
						event.which != 13
				$('#cs-top-blocks-items, #cs-left-blocks-items, #cs-floating-blocks-items, #cs-right-blocks-items, #cs-bottom-blocks-items')
					.disableSelection()
					.sortable
						connectWith	: '.cs-blocks-items'
						items		: 'li:not(:first)'
						cancel		: ':first'
						stop		: ->
							$('#cs-blocks-position').val(
								cs.json_encode(
									top			: $('#cs-top-blocks-items').sortable('toArray')
									left		: $('#cs-left-blocks-items').sortable('toArray')
									floating	: $('#cs-floating-blocks-items').sortable('toArray')
									right		: $('#cs-right-blocks-items').sortable('toArray')
									bottom		: $('#cs-bottom-blocks-items').sortable('toArray')
								)
							)
				$('#cs-users-groups-list, #cs-users-groups-list-selected')
					.disableSelection()
					.sortable
						connectWith	: '#cs-users-groups-list, #cs-users-groups-list-selected'
						items		: 'li:not(:first)'
						cancel		: ':first'
						stop		: ->
							$('#cs-users-groups-list')
								.find('.uk-alert-success')
								.removeClass('uk-alert-success')
								.addClass('uk-alert-warning')
							selected	= $('#cs-users-groups-list-selected')
							selected
								.find('.uk-alert-warning')
								.removeClass('uk-alert-warning')
								.addClass('uk-alert-success')
							$('#cs-user-groups').val(
								cs.json_encode(
									selected.sortable('toArray')
								)
							)
				$('#auto_translation_engine')
					.find('select')
					.change ->
						$('#auto_translation_engine_settings').html(
							cs.base64_decode(
								$(this).children(':selected').data('settings')
							)
						)
		->
			if cookie = cs.getcookie('setcookie')
				for own i of cookie
					$.post(cookie[i])
				cs.setcookie('setcookie', '')
	]
	return
