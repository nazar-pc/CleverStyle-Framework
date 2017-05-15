--FILE--
<?php
namespace cs;
use
	h;

include __DIR__.'/../../unit.php';
Event::instance_replace(False_class::instance());
$Page            = Page::instance();
$Page->post_Body = '';
$Page
	->success(h::div('Success message'))
	->notice(h::div('Success message'))
	->warning(h::div('Success message'));
echo $Page->post_Body;
?>
--EXPECT--
<cs-notify success>
	<div>Success message</div>
</cs-notify>
<cs-notify warning>
	<div>Success message</div>
</cs-notify>
<cs-notify error>
	<div>Success message</div>
</cs-notify>
