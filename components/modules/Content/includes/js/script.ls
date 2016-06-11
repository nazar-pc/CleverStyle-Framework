/**
 * @package   Content
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2014-2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
($) <-! require(['jquery'])
Promise.all([
	cs.api('is_admin api/Content').catch (o) ->
		if o.xhr.status != 404
			clearTimeout(o.timeout)
			Promise.reject()
	cs.ui.ready
])
	.then ([is_admin]) !->
		L	= cs.Language('content_')
		$('body')
			.on(
				'click'
				'.cs-content-add'
				!->
					modal_body	= $("""<form is="cs-form">
						<label>#{L.key}</label>
						<input is="cs-input-text" type="text" name="key">
						<label>#{L.title}</label>
						<input is="cs-input-text" type="text" name="title">
						<label>#{L.content}</label>
						<textarea is="cs-textarea" autosize class="text cs-margin-bottom"></textarea>
						<cs-editor class="html">
							<textarea is="cs-textarea" autosize class="cs-margin-bottom"></textarea>
						</cs-editor>
						<label>#{L.type}</label>
						<select is="cs-select" name="type">
							<option value="text">text</option>
							<option value="html">html</option>
						</select>
						<div>
							<button is="cs-button" type="button" primary>#{L.save}</button>
						</div>
					</form>""")
					modal_body.appendTo(document.body)
					key		= modal_body.find('[name=key]')
					title	= modal_body.find('[name=title]')
					content	= modal_body.find('.text')
					modal_body.find('.html').hide()
					type	= modal_body.find('[name=type]')
					type.change !->
						if type.val() == 'text'
							modal_body.find('.html').hide()
							content	:= modal_body.find('.text').show().val(content.val())
						else
							modal_body.find('.text').hide()
							content	:= modal_body.find('.html').val(content.val()).show().children('textarea').val(content.val())
					cs.ui.simple_modal(modal_body)
					modal_body.find('button').click !->
						data	=
							key		: key.val()
							title	: title.val()
							content	: content.val()
							type	: type.val()
						cs.api('post api/Content', data).then(location~reload)
			)
			.on(
				'click'
				'.cs-content-edit'
				!->
					key = $(@).data('key')
					cs.api("get api/Content/#key").then (data) !->
						modal_body	= $("""<form is="cs-form">
							<label>#{L.key}</label>
							<input is="cs-input-text" readonly value="#{data.key}">
							<label>#{L.title}</label>
							<input is="cs-input-text" type="text" name="title">
							<label>#{L.content}</label>
							<textarea is="cs-textarea" autosize class="text cs-margin-bottom"></textarea>
							<cs-editor class="html">
								<textarea is="cs-textarea" autosize class="cs-margin-bottom"></textarea>
							</cs-editor>
							<label>#{L.type}</label>
							<select is="cs-select" name="type">
								<option value="text">text</option>
								<option value="html">html</option>
							</select>
							<div>
								<button is="cs-button" type="button" primary>#{L.save}</button>
							</div>
						</form>""")
						title	= modal_body.find('[name=title]').val(data.title)
						content	= modal_body.find('.' + data.type).val(data.content)
						modal_body.find('.text, .html').not('.' + data.type).hide()
						type	= modal_body.find('[name=type]').val(data.type)
						type.change !->
							if type.val() == 'text'
								modal_body.find('.html').hide()
								content	:= modal_body.find('.text').show().val(content.val())
							else
								modal_body.find('.text').hide()
								content	:= modal_body.find('.html').val(content.val()).show().children('textarea').val(content.val())
						cs.ui.simple_modal(modal_body)
						modal_body.find('button').click !->
							data	=
								title	: title.val()
								content	: content.val()
								type	: type.val()
							cs.api("put api/Content/#key", data).then(location~reload)
			)
			.on(
				'click'
				'.cs-content-delete'
				!->
					if !confirm("#{L.delete}?")
						return
					key = $(@).data('key')
					cs.api("delete api/Content/#key").then(location~reload)
			)
		do !->
			mousemove_timeout	= 0
			showed_button		= false
			show_edit_button	= (key, x, y, container) !->
				button = $("""<button is="cs-button" class="cs-content-edit" data-key="#key">#{L.edit}</button>""")
					.css('position', 'absolute')
					.offset(
						top		: y
						left	: x
					)
					.appendTo(container)
				container.mouseleave !->
					showed_button	:= false
					button.remove()
			$('body')
				.on(
					'mousemove'
					'[data-cs-content]'
					(e) !->
						if showed_button
							return
						$this = $(@)
						clearTimeout(mousemove_timeout)
						mousemove_timeout := setTimeout (!->
							showed_button	:= true
							show_edit_button($this.data('cs-content'), e.pageX, e.pageY, $this)
						), 200
				)
				.on(
					'mouseleave'
					'[data-cs-content]'
					!->
						clearTimeout(mousemove_timeout)
				)
	.catch ->
