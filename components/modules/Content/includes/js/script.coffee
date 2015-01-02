###*
 * @package        Content
 * @category       modules
 * @author         Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright      Copyright (c) 2014-2015, Nazar Mokrynskyi
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
				modal_body	= $("""<div><div class="uk-form">
					<p>
						<label>#{L.content_key}:</label>
						<input type="text" name="key">
					</p>
					<p>
						<label>#{L.content_title}:</label>
						<input type="text" name="title">
					</p>
					<p>
						<label>#{L.content_content}:</label>
						<textarea class="text"></textarea>
						<textarea class="html EDITOR" id="cs-content-html-content"></textarea>
					</p>
					<p>
						<label>#{L.content_type}:</label>
						<select name="type">
							<option value="text">text</option>
							<option value="html" id="cs-content-html-content">html</option>
						</select>
					</p>
					<p class="cs-right">
						<button class="uk-button">Save</button>
					</p>
				</div></div>""")
				key		= modal_body.find('[name=key]')
				title	= modal_body.find('[name=title]')
				content	= modal_body.find('.text')
				modal_body.find('.html').hide()
				type	= modal_body.find('[name=type]')
				type.change ->
					if type.val() == 'text'
						typeof window.editor_deinitialization == 'function' && editor_deinitialization('cs-content-html-content')
						modal_body.find('.html').hide()
						content	= modal_body.find('.text').show().val(content.val())
					else
						modal_body.find('.text').hide()
						content	= modal_body.find('.html').show().val(content.val())
						typeof window.editor_reinitialization == 'function' && editor_reinitialization('cs-content-html-content')
				modal_body
					.appendTo('body')
					.cs().modal('show')
					.on 'hide.uk.modal', ->
						$(@).remove()
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
						modal_body	= $("""<div><div class="uk-form">
							<p>#{L.content_key}: #{data.key}</p>
							<p>
								<label>#{L.content_title}:</label>
								<input type="text" name="title">
							</p>
							<p>
								<label>#{L.content_content}:</label>
								<textarea class="text"></textarea>
								<textarea class="html EDITOR" id="cs-content-html-content"></textarea>
							</p>
							<p>
								<label>#{L.content_type}:</label>
								<select name="type">
									<option value="text">text</option>
									<option value="html">html</option>
								</select>
							</p>
							<p class="cs-right">
								<button class="uk-button">Save</button>
							</p>
						</div></div>""")
						title	= modal_body.find('[name=title]').val(data.title)
						content	= modal_body.find('textarea').val(data.content)
						modal_body.find("textarea:not(.#{data.type})").hide()
						type	= modal_body.find('[name=type]').val(data.type)
						type.change ->
							if type.val() == 'text'
								typeof window.editor_deinitialization == 'function' && editor_deinitialization('cs-content-html-content')
								modal_body.find('.html').hide()
								content	= modal_body.find('.text').show().val(content.val())
							else
								console.log ''
								modal_body.find('.text').hide()
								content	= modal_body.find('.html').show().val(content.val())
								typeof window.editor_reinitialization == 'function' && editor_reinitialization('cs-content-html-content')
						modal_body
							.appendTo('body')
							.cs().modal('show')
							.on 'hide.uk.modal', ->
								$(@).remove()
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
			button = $("""<button class="uk-button cs-content-edit" data-key="#{key}">#{L.content_edit}</button>""")
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
