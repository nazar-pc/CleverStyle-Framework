<?php
/**
 * @package  WebSockets
 * @category modules
 * @author   Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @license  0BSD
 */
if ($argc < 2) {
	echo "Supervisor takes command as argument and execute it. If command stops for some reason - supervisor will start it again in 1 second and will do that until alive itself.\nUsage: php supervisor.php 'some-command'";
	return;
}
while (true) {
	exec($argv[1]);
	sleep(1);
}
