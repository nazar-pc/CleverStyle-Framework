<?php
/**
 * @package   Http server
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2015, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
if ($argc < 2) {
	exit("Supervisor takes command as argument and execute it. If command stops for some reason - supervisor will start it again in 1 second and will do that until alive itself.\nUsage: php supervisor.php 'some-command'\n");
}
while (true) {
	exec($argv[1]);
	sleep(1);
}
