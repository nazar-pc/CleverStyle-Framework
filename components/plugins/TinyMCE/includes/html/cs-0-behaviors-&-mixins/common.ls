/**
 * @package   TinyMCE
 * @category  plugins
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2015, Nazar Mokrynskyi
 * @license   GNU Lesser General Public License 2.1, see license.txt
 */
Polymer.cs.behaviors.{}TinyMCE.editor =
	properties	:
		target	:
			observers	: '_tinymce_init'
			type		: Object
	ready : !->
		@target	= @firstElementChild
		@_tinymce_init()
	_tinymce_init : !->
		$ !~>
			tinymce.init(
				$.extend(
					target : @target
					@editor_config
				)
			)
