<?php
global $Config, $Index, $L;
$a = &$Index;

$a->content(
	h::{'table.cs-admin-table.cs-left-even.cs-right-odd tr td'}([
		h::info('key_expire'),
		h::{'input.cs-form-element[type=number]'}([
			'name'			=> 'core[key_expire]',
			'value'			=> $Config->core['key_expire'],
			'min'			=> 1
		]).
		$L->seconds
	])
);
unset($a);