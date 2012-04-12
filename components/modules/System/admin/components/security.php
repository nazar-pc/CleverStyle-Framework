<?php
global $Config, $Index, $L;
$a = &$Index;

$a->content(
	h::{'table.admin_table.left_even.right_odd tr td'}([
		h::info('key_expire'),
		h::{'input.form_element[type=number]'}([
			'name'			=> 'core[key_expire]',
			'value'			=> $Config->core['key_expire'],
			'min'			=> 1
		]).
		$L->seconds
	])
);
unset($a);