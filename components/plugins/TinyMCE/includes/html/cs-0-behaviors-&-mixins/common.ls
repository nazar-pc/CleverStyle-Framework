/**
 * @package   TinyMCE
 * @category  plugins
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2015-2016, Nazar Mokrynskyi
 * @license   GNU Lesser General Public License 2.1, see license.txt
 */
load_promise	= null
load_tinymce	= ->
	if load_promise
		return load_promise
	load_promise := $
		.ajax(
			url			: '/components/plugins/TinyMCE/includes/js/tinymce.min.js'
			dataType	: 'script'
			cache		: true
		)
		.then !->
			uploader_callback	= undefined
			button				= document.createElement('button')
			uploader			= cs.file_upload?(
				button
				(files) !->
					tinymce.uploader_dialog.close()
					if files.length
						uploader_callback(files[0])
					uploader_callback := undefined
				(error) !->
					tinymce.uploader_dialog.close()
					cs.ui.notify(error, 'error')
				(file) !->
					if !tinymce.uploader_dialog
						progress								= document.createElement('progress', 'cs-progress')
						tinymce.uploader_dialog					= cs.ui.modal(progress)
						tinymce.uploader_dialog.progress		= progress
						tinymce.uploader_dialog.style.zIndex	= 100000
						tinymce.uploader_dialog.open()
					tinymce.uploader_dialog.progress.value = file.percent || 1
			)
			base_config			=
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
					button.click()
			tinymce
				..Env.experimentalShadowDom				= true
				..ui.Control.prototype.getContainerElm	= -> document.children[0]
				..baseURL								= '/components/plugins/TinyMCE/includes/js'
				..editor_config_full					= {
					toolbar1 : 'styleselect fontselect fontsizeselect | alignleft aligncenter alignright alignjustify | bold italic underline strikethrough superscript subscript | forecolor backcolor | fullscreen',
					toolbar2 : 'undo redo | bullist numlist outdent indent blockquote codesample | link unlink anchor image media charmap hr nonbreaking pagebreak | visualchars visualblocks | searchreplace | preview code'
				} <<<< base_config
				..editor_config_simple					= {
					toolbar : 'undo redo | formatselect | bold italic underline | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent blockquote | link image media | code'
				} <<<< base_config
				..editor_config_inline					= {
					inline  : true,
					menubar : false
				} <<<< tinymce.editor_config_full
				..editor_config_simple_inline			= {
					inline  : true,
					menubar : false
				} <<<< tinymce.editor_config_simple
Polymer.cs.behaviors.{}TinyMCE.editor =
	listeners	:
		tap	: '_style_fix'
	properties	:
		value	:
			notify		: true
			observer	: '_value_changed'
			type		: String
		loaded	: false
	ready : !->
		@querySelector('textarea')?.hidden = true
		# Hack: we need to wait until all Web Components are loaded
		Promise.all([load_tinymce(), cs.ui.ready]).then(@~_initialize_editor)
	_initialize_editor : !->
		# TinyMCE takes some time to initialize, if we'll re-attach it right from start we might end up with two instances instead of one, so lets check if
		# initialization already started
		if @_init_started
			return
		@loaded			= true
		@_init_started	= true
		@_detached		= false
		if @_tinymce_editor
			# Hack: load content first since it might be changed from outside and on destroying TinyMCE will put its current content back
			@_tinymce_editor.load()
			@_tinymce_editor.remove()
			delete @_tinymce_editor
		tinymce.init(
			{
				target					: @firstElementChild
				init_instance_callback	: (editor) !~>
					@_tinymce_editor	= editor
					@_init_started		= false
					# There is a chance that `value` property of editor element was changed, in this case we need to re-initialize it as well
					if @value != undefined && @value != editor.getContent()
						editor.setContent(@value)
						editor.save()
					else
						# In case if something was changed during initialization
						editor.load()
					# Forward focus from plain textarea to editor
					target					= editor.targetElm
					target._original_focus	= target.focus
					target.focus			= editor~focus
					editor.on('remove', !->
						target.focus = target._original_focus
					)
					@_editor_change_callback_init(editor)
			} <<<< tinymce[@editor_config]
		)
	detached : !->
		if !@_tinymce_editor
			return
		@_detached = true
		# Hack for quick moving element from one place to another, postpone removal a bit, otherwise we'll encounter some bugs if element is attached somewhere
		# else
		setTimeout !~>
			if @_detached
				@_tinymce_editor.remove()
				delete @_tinymce_editor
	_style_fix : !->
		# Hack: Polymer styling should be fixed for dynamically created elements
		Array::forEach.call(
			document.querySelectorAll('body > [class^=mce-]')
			(node) !~>
				@scopeSubtree(node, true)
		)
	_editor_change_callback_init : (editor) !->
		editor.once('change', !~>
			@_editor_change_callback(editor)
		)
	_editor_change_callback : (editor) !->
		editor.save()
		@value	= editor.getContent()
		event	= document.createEvent('Event')
		event.initEvent('change', false, true)
		editor.getElement().dispatchEvent(event)
		@_editor_change_callback_init(editor)
	_value_changed : !->
		if @_tinymce_editor && @value != @_tinymce_editor.getContent()
			@_tinymce_editor.setContent(@value || '')
			@_tinymce_editor.save()
