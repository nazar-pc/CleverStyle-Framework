/**
 * @package   CleverStyle CMS
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
# Some default computed bindings methods
Polymer.Base._addFeature(
	# if(condition, then [, otherwise [, prefix [, postfix]]])
	if : (condition, then_, otherwise = '', prefix = '', postfix = '') ->
		'' + prefix + (if condition then then_ else otherwise) + postfix

	# join(array [, separator = ','])
	join : (array, separator) ->
		array.join(if separator != undefined then separator else ',')

	# concat(thing [, another [, ...]])
	concat : (thing, another) ->
		Array.prototype.slice.call(arguments).join('')

	# and(x, y [, z [,...]])
	and : (x, y, z) ->
		!!Array.prototype.slice.call(arguments).reduce (x, y) -> x && y

	# or(x, y [, z [,...]])
	or : (x, y, z) ->
		!!Array.prototype.slice.call(arguments).reduce (x, y) -> x || y

	# xor(x, y [, z [,...]])
	xor : (x, y, z) ->
		Array.prototype.slice.call(arguments).reduce (x, y) -> !x != !y

	# equal(a, b, strict = false)
	equal : (a, b, strict) ->
		if strict then a == b else a ~= b
)
