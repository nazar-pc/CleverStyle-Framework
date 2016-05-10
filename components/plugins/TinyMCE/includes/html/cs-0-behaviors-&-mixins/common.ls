/**
 * @package   TinyMCE
 * @category  plugins
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2015-2016, Nazar Mokrynskyi
 * @license   GNU Lesser General Public License 2.1, see license.txt
 */
Polymer.cs.behaviors.{}TinyMCE.editor =
	listeners	:
		tap	: '_style_fix'
	properties	:
		value	:
			notify		: true
			observer	: '_value_changed'
			type		: String
	ready : !->
		# Hack: we need to wait until all Web Components are loaded
		cs.ui.ready.then(@~_initialize_editor)
	_initialize_editor : !->
		# TinyMCE takes some time to initialize, if we'll re-attach it right from start we might end up with two instances instead of one, so lets check if
		# initialization already started
		if @_init_started
			return
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
			} <<<< @editor_config
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
