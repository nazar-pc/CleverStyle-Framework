/**
 * @package   Content
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2014-2017, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
html_to_node	= (html) ->
	div				= document.createElement('div')
	div.innerHTML	= html
	div.firstChild
# TODO: Refactor into cs-content-editable element
cs.ui.ready
	.then !->
		document.querySelector('body')
			..addEventListener('click', (e) !->
				if !e.target.matches('.cs-content-add')
					return
				cs.Language('content_').ready().then (L) !->
					modal_body	= html_to_node("""<cs-form><form>
						<label>#{L.key}</label>
						<cs-input-text><input type="text" name="key"></cs-input-text>
						<label>#{L.title}</label>
						<cs-input-text><input type="text" name="title"></cs-input-text>
						<label>#{L.content}</label>
						<cs-textarea autosize><textarea class="text cs-margin-bottom"></textarea></cs-textarea>
						<cs-editor class="html cs-margin-bottom" hidden>
							<cs-textarea autosize><textarea></textarea></cs-textarea>
						</cs-editor>
						<label>#{L.type}</label>
						<cs-select>
							<select name="type">
								<option value="text">text</option>
								<option value="html">html</option>
							</select>
						</cs-select>
						<div>
							<cs-button primary><button type="button">#{L.save}</button></cs-button>
						</div>
					</form></cs-form>""")
					key		= modal_body.querySelector('[name=key]')
					title	= modal_body.querySelector('[name=title]')
					content	= modal_body.querySelector('.text')
					type	= modal_body.querySelector('[name=type]')
					type.addEventListener('selected', !->
						if @value == 'text'
							text			= modal_body.querySelector('.text')
							text.value		= content.value
							content.hidden	= true
							content			:= text
							content.hidden	= false
						else
							html									= modal_body.querySelector('.html')
							html.value								= content.value
							html.querySelector('textarea').value	= content.value
							content.hidden							= true
							content									:= html
							content.hidden							= false
					)
					cs.ui.simple_modal(modal_body)
					modal_body.querySelector('button').addEventListener('click', !->
						data	=
							key		: key.value
							title	: title.value
							content	: content.value
							type	: type.selected
						cs.api('post api/Content', data).then(location~reload)
					)
			)
			..addEventListener('click', (e) !->
				if !e.target.matches('.cs-content-edit')
					return
				cs.Language('content_').ready().then (L) !->
					key = e.target.dataset.key
					cs.api("get api/Content/#key").then (data) !->
						modal_body	= html_to_node("""<cs-form><form>
							<label>#{L.key}</label>
							<cs-input-text><input readonly value="#{data.key}"></cs-input-text>
							<label>#{L.title}</label>
							<cs-input-text><input type="text" name="title"></cs-input-text>
							<label>#{L.content}</label>
							<cs-textarea autosize><textarea class="text cs-margin-bottom"></textarea></cs-textarea>
							<cs-editor class="html cs-margin-bottom" hidden>
								<cs-textarea autosize><textarea></textarea></cs-textarea>
							</cs-editor>
							<label>#{L.type}</label>
							<cs-select>
								<select name="type">
									<option value="text">text</option>
									<option value="html">html</option>
								</select>
							</cs-select>
							<div>
								<cs-button primary><button type="button">#{L.save}</button></cs-button>
							</div>
						</form></cs-form>""")
						title	= modal_body.querySelector('[name=title]')
							..value	= data.title
						content	= modal_body.querySelector('.text')
							..value	= data.content
						type	= modal_body.querySelector('[name=type]')
						type.addEventListener('selected', !->
							if @value == 'text'
								text			= modal_body.querySelector('.text')
								text.value		= content.value
								content.hidden	= true
								content			:= text
								content.hidden	= false
							else
								html									= modal_body.querySelector('.html')
								html.value								= content.value
								html.querySelector('textarea').value	= content.value
								content.hidden							= true
								content									:= html
								content.hidden							= false
						)
						type.selected	= data.type
						cs.ui.simple_modal(modal_body)
						modal_body.querySelector('button').addEventListener('click', !->
							data	=
								title	: title.value
								content	: content.value
								type	: type.selected
							cs.api("put api/Content/#key", data).then(location~reload)
						)
			)
			..addEventListener('click', (e) !->
				if !e.target.matches('.cs-content-delete')
					return
				cs.Language('content_').ready().then (L) !->
					key = e.target.dataset.key
					cs.ui.confirm("#{L.delete}?")
						.then -> cs.api("delete api/Content/#key")
						.then(location~reload)
			)
		do !->
			is_admin			= undefined
			mousemove_timeout	= 0
			showed_button		= false
			show_edit_button	= (key, x, y, container) !->
				cs.Language('content_').ready().then (L) !->
					button = html_to_node("""
						<cs-button><button class="cs-content-edit" data-key="#key" style="position: absolute; left: #x; top: #y;">#{L.edit}</button></cs-button>
					""")
					container.appendChild(button)
					container.addEventListener(
						'mouseleave'
						!function callback (e)
							showed_button	:= false
							button.parentNode.removeChild(button)
							e.currentTarget.removeEventListener(e.type, callback)
						{passive : true}
					)
			document.querySelector('body')
				..addEventListener(
					'mousemove'
					(e) !->
						if !e.target.matches('[data-cs-content]')
							return
						if showed_button
							return
						clearTimeout(mousemove_timeout)
						mousemove_timeout := setTimeout (!->
							showed_button	:= true
							if is_admin == undefined
								cs.api('is_admin api/Content')
									.then (result) !->
										is_admin	:= result
										if is_admin
											show_edit_button(e.target.dataset.cs-content, e.pageX, e.pageY, e.target)
									.catch (o) ->
										if o.xhr.status == 404
											clearTimeout(o.timeout)
											Promise.reject()
								return
							if is_admin
								show_edit_button(e.target.dataset.cs-content, e.pageX, e.pageY, e.target)
						), 200
					{passive : true}
				)
	.catch ->
