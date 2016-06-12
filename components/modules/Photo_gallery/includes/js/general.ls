/**
 * @package   Photo gallery
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2013-2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
L			= cs.Language('photo_gallery_')
add_button	= document.querySelector('.cs-photo-gallery-add-images')
if add_button
	cs.file_upload(
		add_button
		(files) !->
			gallery	= add_button.dataset.gallery
			cs.api('post api/Photo_gallery/images', {files, gallery}).then (result) !->
				if !result.length || !result
					alert L.images_not_supported
					return
				if files.length != result.length
					alert L.some_images_not_supported
				location.href	= 'Photo_gallery/edit_images/' + result.join(',')
		null
		null
		true
	)
images_section	= document.querySelector('.cs-photo-gallery-images')
if images_section
	require(['jquery']).then ([$]) !->
		$('html, body').stop().animate(
			scrollTop	: $('.cs-photo-gallery-images').offset().top - $(document).height() * 0.1
		)
		cs.ui.ready.then !->
			$(images_section).fotorama(
				allowfullscreen	: 'native'
				controlsonstart	: false
				fit				: 'scaledown'
				height			: '80%'
				keyboard		: true
				nav				: 'thumbs'
				trackpad		: true
				width			: '100%'
			)
		$('body').on(
			'click',
			'.cs-photo-gallery-image-delete'
			!->
				cs.ui.confirm(L.sure_to_delete_image)
					.then ~> cs.api('delete api/Photo_gallery/images/' + $(@).data('image'))
					.then(location~reload)
		)
