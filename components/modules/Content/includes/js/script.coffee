###*
 * @package        Content
 * @category       modules
 * @author         Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright      Copyright (c) 2014, Nazar Mokrynskyi
 * @license        MIT License, see license.txt
###
$(document)
	.on(
		'click'
		'.cs-content-add'
		->
			L	= cs.Language
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
					<textarea name="content"></textarea>
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
			key		= modal_body.find('[name=key]')
			title	= modal_body.find('[name=title]')
			content	= modal_body.find('[name=content]')
			type	= modal_body.find('[name=type]')
			modal_body
				.appendTo('body')
				.cs().modal('show')
				.on 'uk.modal.hide', ->
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
			L	= cs.Language
			key = $(@).val()
			$.ajax(
				url		: 'api/Content'
				data	:
					key	: key
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
							<textarea name="content"></textarea>
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
					content	= modal_body.find('[name=content]').val(data.content)
					type	= modal_body.find('[name=type]').val(data.type)
					modal_body
						.appendTo('body')
						.cs().modal('show')
						.on 'uk.modal.hide', ->
							$(@).remove()
					modal_body.find('button').click ->
						$.ajax(
							url		: 'api/Content'
							data	:
								key		: key
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
			L	= cs.Language
			if !confirm("#{L.content_delete}?")
				return
			key = $(@).val()
			$.ajax(
				url		: 'api/Content'
				data	:
					key	: key
				type	: 'delete'
				success	: ->
					location.reload()
			)
	)
