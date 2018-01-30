/**
 * @package CleverStyle Widgets
 * @author  Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @license 0BSD
 */
csw.behaviors.cs-textarea = [
	csw.behaviors.ready
	csw.behaviors.size
	csw.behaviors.tooltip
	csw.behaviors.value
	properties	:
		autosize	:
			observer			: '_autosize_changed'
			reflectToAttribute	: true
			type				: Boolean
		initialized	: Boolean
	attached : !->
		@initialized = true
		@_when_ready(@~_do_autosizing)
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
		if autosize == undefined
			return
		if autosize
			autosize(@firstElementChild)
			autosize.update(@firstElementChild)
		else
			autosize.destroy(@firstElementChild)
]
