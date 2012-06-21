<?php
global $Config, $L, $User, $Page, $Mail;
$id = $User->get_id(hash('sha224', $Config->routing['current'][2]));
$Page->content(
	h::table(
		h::tr(
			//$User->search_users()
		)
	)
);