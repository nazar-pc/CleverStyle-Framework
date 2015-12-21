/**
 * @package   TinyMCE
 * @category  plugins
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2015, Nazar Mokrynskyi
 * @license   GNU Lesser General Public License 2.1, see license.txt
 */
Polymer.cs.behaviors.{}TinyMCE.editor =
	listeners	:
		tap	: '_style_fix'
	properties	:
		target	:
			observers	: '_tinymce_init'
			type		: Object
		value	:
			observer	: '_value_changed'
			type		: String
	ready : !->
		@target	= @firstElementChild
		@_tinymce_init()
	_tinymce_init : !->
		tinymce.init(
			{
				target	: @target
			} <<<< @editor_config
		)
	_style_fix : !->
		# Hack: Polymer styling should be fixed for dynamically created elements
		[].slice.call(document.querySelectorAll('body > [class^=mce-]')).forEach (node) !~>
			@scopeSubtree(node, true)
	_value_changed : !->
		if @target.tagName == 'TEXTAREA' && @target.tinymce_editor && @value != @target.tinymce_editor.getContent()
			@target.tinymce_editor.load()
