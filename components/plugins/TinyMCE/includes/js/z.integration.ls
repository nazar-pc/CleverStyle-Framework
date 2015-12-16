/**
 * @package   TinyMCE
 * @category  plugins
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2012-2015, Nazar Mokrynskyi
 * @license   GNU Lesser General Public License 2.1, see license.txt
 */
tinymce.baseURL		= '/components/plugins/TinyMCE/includes/js'
uploader_callback	= undefined
uploader			= cs.file_upload && cs.file_upload(
	null
	(files) !->
		tinymce.uploader_dialog.close()
		if files.length
			uploader_callback(files[0])
		uploader_callback := undefined
	(error) !->
		tinymce.uploader_dialog.close()
		alert(error.message)
	(file) !->
		if !tinymce.uploader_dialog
			progress								= document.createElement('progress', 'cs-progress')
			tinymce.uploader_dialog					= cs.ui.modal(progress)
			tinymce.uploader_dsialog.progress		= progress
			tinymce.uploader_dialog.style.zIndex	= 100000
			tinymce.uploader_dialog.open()
		tinymce.uploader_dialog.progress.value = file.percent || 1
)
base_config =
	doctype					: '<!doctype html>'
	theme					: if cs.tinymce && cs.tinymce.theme != undefined then cs.tinymce.theme else 'modern'
	skin					: if cs.tinymce && cs.tinymce.skin != undefined then cs.tinymce.skin else 'lightgray'
	language				: if cs.Language.clang != undefined then cs.Language.clang else 'en'
	menubar					: false
	plugins					: 'advlist anchor charmap code codesample colorpicker contextmenu fullscreen hr image link lists media nonbreaking noneditable pagebreak paste preview searchreplace tabfocus table textcolor visualblocks visualchars wordcount'
	resize					: 'both'
	toolbar_items_size		: 'small'
	width					: '100%'
	convert_urls			: false
	remove_script_host		: false
	relative_urls			: false
	table_style_by_css		: true
	file_picker_callback	: uploader && (callback) !->
		uploader_callback := callback
		uploader.browse()
	setup					: (editor) !->
		editor.on('change', !->
			editor.save()
			event = document.createEvent('Event')
			event.initEvent('change', false, true)
			editor.getElement().dispatchEvent(event)
		)
	init_instance_callback : (editor) !->
		editor.getElement().tinymce_editor = editor
tinymce
	..editor_config_full = {
		toolbar1 : 'styleselect fontselect fontsizeselect | alignleft aligncenter alignright alignjustify | bold italic underline strikethrough superscript subscript | forecolor backcolor | fullscreen',
		toolbar2 : 'undo redo | bullist numlist outdent indent blockquote codesample | link unlink anchor image media charmap hr nonbreaking pagebreak | visualchars visualblocks | searchreplace | preview code'
	} <<<< base_config
	..editor_config_simple = {
		toolbar : 'undo redo | formatselect | bold italic underline | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent blockquote | link image media | code'
	} <<<< base_config
	..editor_config_inline = {
		inline  : true,
		menubar : false
	} <<<< tinymce.editor_config_full
	..editor_config_simple_inline = {
		inline  : true,
		menubar : false
	} <<<< tinymce.editor_config_simple
# TODO: remove this before release since we have convenient wrappers
do !->
	initialize = (selector, config) ->
		$ !->
			$(selector).prop('required', false)
		tinymce.init(
			{selector : selector} <<<< config
		)
	# Full editor
	initialize('.EDITOR', tinymce.editor_config_full)
	# Simple editor
	initialize('.SIMPLE_EDITOR', tinymce.editor_config_simple)
	# Inline editor
	initialize('.INLINE_EDITOR', tinymce.editor_config_inline)
	# Small inline editor
	initialize('.SIMPLE_INLINE_EDITOR', tinymce.editor_config_simple_inline)
window
	..editor_deinitialization = (textarea) !->
		textarea = $(textarea)[0]
		if textarea.tinymce_editor
			textarea.tinymce_editor.destroy()
			delete textarea.tinymce_editor
	..editor_reinitialization = (textarea) !->
		$textarea = $(textarea)
		editor    = $textarea[0].tinymce_editor
		if editor
			editor.load()
			return
		config =
			if $textarea.hasClass('EDITOR')
				tinymce.editor_config_full
			else if $textarea.hasClass('SIMPLE_EDITOR')
				tinymce.editor_config_simple
			else if $textarea.hasClass('INLINE_EDITOR')
				tinymce.editor_config_inline
			else if $textarea.hasClass('SIMPLE_INLINE_EDITOR')
				tinymce.editor_config_simple_inline
		if config
			tinymce.init(
				{target : $textarea[0]} <<<< config
			)
	..editor_focus = (textarea) !->
		$(textarea)[0].tinymce_editor.focus()
