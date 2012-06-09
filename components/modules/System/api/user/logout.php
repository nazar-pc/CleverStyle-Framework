<?php
if (isset($_POST['logout'])) {
	global $User;
	$User->del_session();
}