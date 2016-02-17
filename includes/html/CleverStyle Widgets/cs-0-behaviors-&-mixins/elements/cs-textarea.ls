/**
 * @package   CleverStyle Widgets
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2015-2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
Polymer.cs.behaviors.cs-textarea = [
	Polymer.cs.behaviors.size
	Polymer.cs.behaviors.this
	Polymer.cs.behaviors.tooltip
	Polymer.cs.behaviors.value
	properties	:
		autosize	:
			observer			: '_autosize_changed'
			reflectToAttribute	: true
			type				: Boolean
		initialized	: Boolean
	attached : !->
		@initialized = true
		setTimeout(@~_do_autosizing)
	_autosize_changed : !->
		@_do_autosizing()
	_do_autosizing : !->
		if !@initialized || @autosize == undefined
			return
		# Apply autosizing only if autosize plugin available: https://github.com/jackmoore/autosize
		if window.autosize
			@_do_autosizing_callback(autosize)
		# RequireJS module is also fine
		else if window.require
			require(['autosize'], @~_do_autosizing_callback)
	_do_autosizing_callback : (autosize) !->
		if @autosize
			autosize(@)
			autosize.update(@)
		else
			autosize.destroy(@)
]
