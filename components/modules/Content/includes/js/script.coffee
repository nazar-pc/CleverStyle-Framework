###*
 * @package        Content
 * @category       modules
 * @author         Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright      Copyright (c) 2014-2016, Nazar Mokrynskyi
 * @license        MIT License, see license.txt
###
if !cs.is_admin
	return
$ ->
	L	= cs.Language
	$('body')
		.on(
			'click'
			'.cs-content-add'
			->
				modal_body	= $("""<form is="cs-form">
					<label>#{L.content_key}</label>
					<input is="cs-input-text" type="text" name="key">
					<label>#{L.content_title}</label>
					<input is="cs-input-text" type="text" name="title">
					<label>#{L.content_content}</label>
					<textarea is="cs-textarea" autosize class="text cs-margin-bottom"></textarea>
					<cs-editor class="html">
						<textarea is="cs-textarea" autosize class="cs-margin-bottom"></textarea>
					</cs-editor>
					<label>#{L.content_type}</label>
					<select is="cs-select" name="type">
						<option value="text">text</option>
						<option value="html">html</option>
					</select>
					<div>
						<button is="cs-button" type="button" primary>#{L.content_save}</button>
					</div>
				</form>""")
				modal_body.appendTo(document.body)
				key		= modal_body.find('[name=key]')
				title	= modal_body.find('[name=title]')
				content	= modal_body.find('.text')
				modal_body.find('.html').hide()
				type	= modal_body.find('[name=type]')
				type.change ->
					if type.val() == 'text'
						modal_body.find('.html').hide()
						content	= modal_body.find('.text').show().val(content.val())
					else
						modal_body.find('.text').hide()
						content	= modal_body.find('.html').val(content.val()).show().children('textarea').val(content.val())
				cs.ui.simple_modal(modal_body)
				modal_body.find('button').click ->
					$.ajax(
						url		: 'api/Content'
						data	:
							key		: key.val()
							title	: title.val()
							content	: content.val()
							type	: type.val()
						type	: 'post'
						success	: ->
							location.reload()
					)
		)
		.on(
			'click'
			'.cs-content-edit'
			->
				key = $(@).data('key')
				$.ajax(
					url		: "api/Content/#{key}"
					type	: 'get'
					success	: (data) ->
						modal_body	= $("""<form is="cs-form">
							<label>#{L.content_key}</label>
							<input is="cs-input-text" readonly value="#{data.key}">
							<label>#{L.content_title}</label>
							<input is="cs-input-text" type="text" name="title">
							<label>#{L.content_content}</label>
							<textarea is="cs-textarea" autosize class="text cs-margin-bottom"></textarea>
							<cs-editor class="html">
								<textarea is="cs-textarea" autosize class="cs-margin-bottom"></textarea>
							</cs-editor>
							<label>#{L.content_type}</label>
							<select is="cs-select" name="type">
								<option value="text">text</option>
								<option value="html">html</option>
							</select>
							<div>
								<button is="cs-button" type="button" primary>#{L.content_save}</button>
							</div>
						</form>""")
						title	= modal_body.find('[name=title]').val(data.title)
						content	= modal_body.find('.' + data.type).val(data.content)
						modal_body.find('.text, .html').not('.' + data.type).hide()
						type	= modal_body.find('[name=type]').val(data.type)
						type.change ->
							if type.val() == 'text'
								modal_body.find('.html').hide()
								content	= modal_body.find('.text').show().val(content.val())
							else
								modal_body.find('.text').hide()
								content	= modal_body.find('.html').val(content.val()).show().children('textarea').val(content.val())
						cs.ui.simple_modal(modal_body)
						modal_body.find('button').click ->
							$.ajax(
								url		: "api/Content/#{key}"
								data	:
									title	: title.val()
									content	: content.val()
									type	: type.val()
								type	: 'put'
								success	: ->
									location.reload()
							)
				)
		)
		.on(
			'click'
			'.cs-content-delete'
			->
				if !confirm("#{L.content_delete}?")
					return
				key = $(@).data('key')
				$.ajax(
					url		: "api/Content/#{key}"
					type	: 'delete'
					success	: ->
						location.reload()
				)
		)
	do ->
		mousemove_timeout	= 0
		showed_button		= false
		show_edit_button	= (key, x, y, container) ->
			button = $("""<button is="cs-button" class="cs-content-edit" data-key="#{key}">#{L.content_edit}</button>""")
				.css('position', 'absolute')
				.offset(
					top		: y
					left	: x
				)
				.appendTo(container)
			container.mouseleave ->
				showed_button	= false
				button.remove()
		$('body')
			.on(
				'mousemove'
				'[data-cs-content]'
				(e) ->
					if showed_button
						return
					$this = $(@)
					clearTimeout(mousemove_timeout)
					mousemove_timeout = setTimeout (->
						showed_button	= true
						show_edit_button($this.data('cs-content'), e.pageX, e.pageY, $this)
					), 200
					return
			)
			.on(
				'mouseleave'
				'[data-cs-content]'
				->
					clearTimeout(mousemove_timeout)
		)
