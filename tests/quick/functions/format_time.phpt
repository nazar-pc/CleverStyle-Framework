--FILE--
<?php
namespace cs;
include __DIR__.'/../../unit.php';

$L = Language::instance_stub(
	[
		'system_time_seconds' => 'seconds',
		'system_time_minutes' => 'minutes',
		'system_time_hours'   => 'hours',
		'system_time_days'    => 'days',
		'system_time_months'  => 'months',
		'system_time_years'   => 'years'
	],
	[
		'time' => function ($in, $type) use (&$L) {
			$types = [
				's' => $L->system_time_seconds,
				'm' => $L->system_time_minutes,
				'h' => $L->system_time_hours,
				'd' => $L->system_time_days,
				'M' => $L->system_time_months,
				'y' => $L->system_time_years
			];
			return $in.' '.$types[$type];
		}
	]
);

var_dump(format_time('not a number'));

var_dump('seconds', format_time(25));
var_dump('minutes', format_time(25 + 60 * 2));
var_dump('hours', format_time(25 + 60 * 2 + 3600));
var_dump('days', format_time(25 + 60 * 2 + 3600 + 3600 * 24 * 2));
var_dump('months', format_time(25 + 60 * 2 + 3600 + 3600 * 24 * 2 + 3600 * 24 * 30 * 3));
var_dump('years', format_time(25 + 60 * 2 + 3600 + 3600 * 24 * 2 + 3600 * 35 + 3600 * 24 * 30 * 15));
?>
--EXPECT--
string(12) "not a number"
string(7) "seconds"
string(10) "25 seconds"
string(7) "minutes"
string(20) "2 minutes 25 seconds"
string(5) "hours"
string(28) "1 hours 2 minutes 25 seconds"
string(4) "days"
string(35) "2 days 1 hours 2 minutes 25 seconds"
string(6) "months"
string(44) "3 months 2 days 1 hours 2 minutes 25 seconds"
string(5) "years"
string(54) "1 years 2 months 28 days 12 hours 2 minutes 25 seconds"
