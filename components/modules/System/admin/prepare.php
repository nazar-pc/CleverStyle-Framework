<?php
function system_input_core ($item, $type = 'text', $info_item = null, $disabled = false, $min = false, $max = false, $post_text = '') {
	global $Config;
	if ($type != 'radio') {
		return [
			h::info($info_item ?: $item),
			h::input([
				'name'		=> "core[$item]",
				'value'		=> $Config->core[$item],
				'class'		=> 'cs-form-element',
				'min'		=> $min,
				'max'		=> $max,
				'type'		=> $type,
				($disabled ? 'disabled' : '')
			]).
			$post_text
		];
	} else {
		global $L;
		return [
			h::info($info_item ?: $item),
			h::input([
				'name'		=> "core[$item]",
				'checked'	=> $Config->core[$item],
				'value'		=> [0, 1],
				'in'		=> [$L->off, $L->on],
				'type'		=> $type
			])
		];
	}
}
function system_textarea_core ($item, $wide = true, $editor = null, $info_item = null) {
	global $Config;
	return [
		h::info($info_item ?: $item),
		h::textarea(
			$Config->core[$item],
			[
				'name'	=> "core[$item]",
				'class'	=> 'cs-form-element'.($wide ? ' cs-wide-textarea' : '').($editor ? ' '.$editor : '')
			]
		)
	];
}
function system_select_core ($items_array, $item, $id = null, $info_item = null, $multiple = false, $size = 5) {
	global $Config;
	return [
		h::info($info_item ?: $item),
		h::select(
			$items_array,
			[
				'name'		=> "core[$item]".($multiple ? '[]' : ''),
				'selected'	=> $Config->core[$item],
				'size'		=> $size,
				'id'		=> $id ?: false,
				'class'		=> 'cs-form-element',
				$multiple ? 'multiple' : false
			]
		)
	];
}