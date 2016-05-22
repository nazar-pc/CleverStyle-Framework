/**
 * @package   Shop
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2014-2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
$ !->
	L							= cs.Language('shop_')
	set_attribute_types			= [1, 2, 6, 9]	# Attributes types that represents sets: TYPE_INT_SET, TYPE_FLOAT_SET, TYPE_STRING_SET, TYPE_COLOR_SET
	color_set_attribute_type	= [1, 2, 6, 9]	# Attributes types that represents color set: TYPE_COLOR_SET
	string_attribute_types		= [5]			# Attributes types that represents string: TYPE_STRING
	make_modal = (attributes, categories, title, action) ->
		attributes		= do ->
			attributes_ = {}
			for attribute, attribute of attributes
				attributes_[attribute.id] = attribute
			attributes_
		categories		= do ->
			categories_ = {}
			for category, category of categories
				categories_[category.id] = category
			categories_
		categories_list	= do ->
			categories_list_	= {
				'-' : """<option disabled>#{L.none}</option>"""
			}
			keys				= ['-']
			for category, category of categories
				parent_category = parseInt(category.parent)
				while parent_category && parent_category != category
					parent_category = categories[parent_category]
					if parent_category.parent == category.id
						break
					category.title	= parent_category.title + ' :: ' + category.title
					parent_category	= parseInt(parent_category.parent)
				categories_list_[category.title]	= """<option value="#{category.id}">#{category.title}</option>"""
				keys.push(category.title)
			keys.sort()
			for key in keys
				categories_list_[key]
		categories_list	= categories_list.join('')
		modal			= $(cs.ui.simple_modal("""<form>
			<h3 class="cs-text-center">#{title}</h3>
			<p>
				#{L.category}: <select is="cs-select" name="category" required>#{categories_list}</select>
			</p>
			<div></div>
		</form>"""))
		modal.item_data			= {}
		modal.update_item_data	= !->
			item	= modal.item_data
			modal.find('[name=price]').val(item.price)
			modal.find('[name=in_stock]').val(item.in_stock)
			modal.find("[name=soon][value=#{item.soon}]").prop('checked', true)
			modal.find("[name=listed][value=#{item.listed}]").prop('checked', true)
			if item.images
				modal.add_images(item.images)
			if item.videos
				modal.add_videos(item.videos)
			if item.attributes
				for attribute, value of item.attributes
					modal.find("[name='attributes[#{attribute}]']").val(value)
			if item.tags
				modal.find('[name=tags]').val(item.tags.join(', '))
		modal.find('[name=category]').change !->
			modal.find('form').serializeArray().forEach (item) !->
				value	= item.value
				name	= item.name
				switch name
					when 'tags'
						value	= value.split(',').map($.trim)
					when 'images'
						if value
							value	= JSON.parse(value)
				if attribute = name.match(/attributes\[([0-9]+)\]/)
					if !modal.item_data.attributes
						modal.item_data.attributes	= {}
					modal.item_data.attributes[attribute[1]]	= value
				else
					modal.item_data[item.name]	= value
			$this			= $(@)
			category		= categories[$this.val()]
			attributes_list	= do ->
				for attribute in category.attributes
					attribute		= attributes[attribute]
					attribute.type	= parseInt(attribute.type)
					if set_attribute_types.indexOf(attribute.type) != -1
						values = do ->
							for value in attribute.value
								"""<option value="#{value}">#{value}</option>"""
						values = values.join('')
						color	=
							if attribute.type == color_set_attribute_type
								"""<input is="cs-input-text" type="color">"""
							else
								''
						"""<p>
							#{attribute.title}:
							<select is="cs-select" name="attributes[#{attribute.id}]">
								<option value="">#{L.none}</option>
								#{values}
							</select>
							#{color}
						</p>"""
					else if string_attribute_types.indexOf(attribute.type) != -1
						"""<p>
							#{attribute.title}: <input is="cs-input-text" name="attributes[#{attribute.id}]">
						</p>"""
					else
						"""<p>
							#{attribute.title}: <textarea is="cs-textarea" autosize name="attributes[#{attribute.id}]"></textarea>
						</p>"""
			attributes_list	= attributes_list.join('')
			$this.parent().next().html("""
				<p>
					#{L.price}: <input is="cs-input-text" name="price" type="number" value="0" required>
				</p>
				<p>
					#{L.in_stock}: <input is="cs-input-text" name="in_stock" type="number" value="1" step="1">
				</p>
				<p>
					#{L.available_soon}:
					<label is="cs-label-button"><input type="radio" name="soon" value="1"> #{L.yes}</label>
					<label is="cs-label-button"><input type="radio" name="soon" value="0" checked> #{L.no}</label>
				</p>
				<p>
					#{L.listed}:
					<label is="cs-label-button"><input type="radio" name="listed" value="1" checked> #{L.yes}</label>
					<label is="cs-label-button"><input type="radio" name="listed" value="0"> #{L.no}</label>
				</p>
				<p>
					<span class="images" style="display: block"></span>
					<button is="cs-button" tight type="button" class="add-images">#{L.add_images}</button>
					<progress is="cs-progress" hidden></progress>
					<input type="hidden" name="images">
				</p>
				<p>
					<div class="videos"></div>
					<button is="cs-button" type="button" class="add-video">#{L.add_video}</button>
				</p>
				#{attributes_list}
				<p>
					#{L.tags}: <input is="cs-input-text" name="tags" placeholder="shop, high quality, e-commerce">
				</p>
				<p>
					<button is="cs-button" primary type="submit">#{action}</button>
				</p>
			""")
			images_container	= modal.find('.images')
			modal.update_images	= !->
				images	= []
				images_container.find('a').each !->
					images.push $(@).attr('href')
				modal.find('[name=images]').val(
					JSON.stringify(images)
				)
				<-! require(['html5sortable'])
				images_container
					.sortable('destroy')
					.sortable(
						forcePlaceholderSize	: true
						placeholder				: '<button is="cs-button" icon="map-pin" style="vertical-align: top">'
					)
					.on(
						'sortupdate'
						modal.update_images
					)
			modal.add_images	= (images) !->
				images.forEach (image) !->
					images_container.append("""<a href="#{image}" target="_blank" style="display: inline-block; padding: .5em; width: 150px">
						<img src="#{image}">
						<br>
						<button is="cs-button" force-compact type="button" class="remove-image" style="width: 100%">#{L.remove_image}</button>
					</a>""")
				modal.update_images()
			if cs.file_upload
				do !->
					progress	= modal.find('.add-images').next()[0]
					uploader	= cs.file_upload(
						modal.find('.add-images')
						(images) !->
							progress.hidden = true
							modal.add_images(images)
						(error) !->
							progress.hidden = true
							cs.ui.notify(error, 'error')
						(percents) !->
							progress.value	= percents
							progress.hidden	= false
						true
					)
					modal.on('close', uploader~destroy)
			else
				modal.find('.add-images').click !->
					image	= prompt(L.image_url)
					if image
						modal.add_images([image])
			modal.on('click', '.remove-image', ->
				$(@).parent().remove()
				modal.update_images()
				false
			)
			videos_container	= modal.find('.videos')
			modal.update_videos	= !->
				<-! require(['html5sortable'])
				videos_container
					.sortable('destroy')
					.sortable(
						handle					: '.handle'
						forcePlaceholderSize	: true
					)
					.on(
						'sortupdate'
						modal.update_videos
					)
			modal.add_videos	= (videos) !->
				videos.forEach (video) !->
					videos_container.append("""<p>
						<cs-icon icon="sort" class="handle"></cs-icon>
						<select is="cs-select" name="videos[type][]" class="video-type">
							<option value="supported_video">#{L.youtube_vimeo_url}</option>
							<option value="iframe">#{L.iframe_url_or_embed_code}</option>
							<option value="direct_url">#{L.direct_video_url}</option>
						</select>
						<textarea is="cs-textarea" autosize name="videos[video][]" placeholder="#{L.url_or_code}" class="video-video" rows="3"></textarea>
						<input is="cs-input-text" name="videos[poster][]" class="video-poster" placeholder="#{L.video_poster}">
						<button is="cs-button" icon="close" type="button" class="delete-video"></button>
						<progress is="cs-progress" hidden full-width></progress>
					</p>""")
					added_video		= videos_container.children('p:last')
					video_video		= added_video.find('.video-video').val(video.video)
					video_poster	= added_video.find('.video-poster').val(video.poster)
					if cs.file_upload
						do !->
							video_video.after("""
								&nbsp;<button is="cs-button" type="button" icon="upload"></button>
							""")
							progress	= video_video.parent().find('progress')[0]
							uploader	= cs.file_upload(
								video_video.next()
								(video) !->
									progress.hidden = true
									video_video.val(video[0])
								(error) !->
									progress.hidden = true
									cs.ui.notify(error, 'error')
								(percents) !->
									progress.value	= percents
									progress.hidden	= false
							)
							modal.on('close', uploader~destroy)
						do !->
							video_poster.after("""
								&nbsp;<button is="cs-button" type="button" icon="upload"></button>
							""")
							progress	= video_video.parent().find('progress')[0]
							uploader	= cs.file_upload(
								video_poster.next()
								(poster) !->
									progress.hidden = true
									video_poster.val(poster[0])
								(error) !->
									progress.hidden = true
									cs.ui.notify(error, 'error')
								(percents) !->
									progress.value	= percents
									progress.hidden	= false
							)
							modal.on('close', uploader~destroy)
					added_video.find('.video-type').val(video.type).change()
				modal.update_videos()
			modal.find('.add-video').click !->
				modal.add_videos([
					video	: ''
					poster	: ''
					type	: 'supported_video'
				])
			videos_container.on(
				'click'
				'.delete-video'
				!->
					$(@).parent().remove()
			)
			videos_container.on(
				'change'
				'.video-type'
				!->
					$this		= $(@)
					container	= $this.parent()
					switch $this.val()
						when 'supported_video'
							container.find('.video-video').next('button').hide()
							container.find('.video-poster').hide().next('button').hide()
						when 'iframe'
							container.find('.video-video').next('button').hide()
							container.find('.video-poster').show().next('button').show()
						when 'direct_url'
							container.find('.video-video').next('button').show()
							container.find('.video-poster').show().next('button').show()
			)
			modal.update_item_data()
		modal
	$('html')
		.on('mousedown', '.cs-shop-item-add', !->
			Promise.all([
				$.getJSON('api/Shop/admin/attributes')
				$.getJSON('api/Shop/admin/categories')
			]).then ([attributes, categories]) !->
				modal = make_modal(attributes, categories, L.item_addition, L.add)
				modal.find("[name=category]").change()
				modal.find('form').submit ->
					$.ajax(
						url     : 'api/Shop/admin/items'
						type    : 'post'
						data    : $(@).serialize()
						success : !->
							alert(L.added_successfully)
							location.reload()
					)
					false
		)
		.on('mousedown', '.cs-shop-item-edit', !->
			id = $(@).data('id')
			Promise.all([
				$.getJSON('api/Shop/admin/attributes')
				$.getJSON('api/Shop/admin/categories')
				$.getJSON("api/Shop/admin/items/#{id}")
			]).then ([attributes, categories, item]) !->
				modal = make_modal(attributes, categories, L.item_edition, L.edit)
				modal.find('form').submit ->
					$.ajax(
						url     : "api/Shop/admin/items/#{id}"
						type    : 'put'
						data    : $(@).serialize()
						success : !->
							alert(L.edited_successfully)
							location.reload()
					)
					false
				modal.item_data	= item
				modal.find("[name=category]").val(item.category).change()
		)
		.on('mousedown', '.cs-shop-item-delete', !->
			id = $(@).data('id')
			if confirm(L.sure_want_to_delete)
				$.ajax(
					url     : "api/Shop/admin/items/#{id}"
					type    : 'delete'
					success : !->
						alert(L.deleted_successfully)
						location.reload()
				)
		)
