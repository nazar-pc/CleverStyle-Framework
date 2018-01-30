/**
 * @package  Photo gallery
 * @category modules
 * @author   Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @license  0BSD
 */
$ <-! require(['jquery'], _)
<-! $
add_button	= document.querySelector('.cs-photo-gallery-add-images')
if add_button
	cs.file_upload(
		add_button
		(files) !->
			gallery	= add_button.dataset.gallery
			Promise.all([
				cs.api('post api/Photo_gallery/images', {files, gallery})
				cs.Language('photo_gallery_').ready()
			]).then ([result, L]) !->
				if !result.length || !result
					cs.ui.alert(L.images_not_supported)
					return
				href	= 'Photo_gallery/edit_images/' + result.join(',')
				if files.length != result.length
					cs.ui.alert(L.some_images_not_supported).then !->
						location.href	= href
				else
					location.href	= href
		null
		null
		true
	)
images_section	= document.querySelector('.cs-photo-gallery-images')
if images_section
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
			cs.Language('photo_gallery_').ready()
				.then (L) -> cs.ui.confirm(L.sure_to_delete_image)
				.then ~> cs.api('delete api/Photo_gallery/images/' + $(@).data('image'))
				.then(location~reload)
	)
