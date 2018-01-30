<?php
/**
 * @package    CleverStyle Framework
 * @subpackage Builder
 * @author     Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @license    0BSD
 */
namespace cs;

require_once __DIR__.'/functions.php';
time_limit_pause();
header('Content-Type: text/html; charset=utf-8');
header('Connection: close');

$Builder = new Builder(DIR, DIR);
$content = '';
$mode    = $_POST['mode'] ?? 'form';
if ($mode == 'core') {
	$content = $Builder->core($_POST['modules'] ?? [], $_POST['themes'] ?? [], @$_POST['suffix']);
} elseif (in_array($mode, ['core', 'module', 'theme'])) {
	foreach ($_POST[$mode.'s'] ?? [] as $component) {
		$content .= $Builder->$mode($component, @$_POST['suffix']).'<br>';
	}
	if (!$content) {
		$content = 'Nothing was selected';
	}
} else {
	$content = form();
}
?>
<!doctype html>
<title>CleverStyle Framework Builder</title>
<meta charset="utf-8">
<link href="build/assets/style.css" rel="stylesheet">
<script src="build/assets/functions.js"></script>
<header>
	<img alt="CleverStyle Framework" src="assets/img/logo.svg" height="128" title="CleverStyle Framework">
	<h1>Builder</h1>
</header>
<section>
	<?=$content?>
</section>
