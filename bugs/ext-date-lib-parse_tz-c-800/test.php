<?php
function var_fusion($var1, $var2, $var3) {
    $result = array();
    $vars = [$var1, $var2, $var3];
    try{
        fuzz_internal_interface($vars);
        fuzz_internal_interface($vars);
        fuzz_internal_interface($vars);
    } catch (ReflectionException $e) {
        echo("Error: " . $e->getMessage());
    }
    return $result;
}
    
class MyDateTime extends DateTime
{
	function __construct()
	{
	}
}
class MyDateTimeZone extends DateTimeZone
{
	function __construct()
	{
	}
}
function check(callable $c)
{
	try {
		var_dump($c());
	} catch (\DateObjectError $e) {
		echo $e::class, ': ', $e->getMessage(), "\n";
	}
}
$mdt = new MyDateTime();
$mdtz = new MyDateTimeZone();
$dtz = new DateTimeZone("Europe/Kyiv");
$dt = new DateTime("2023-01-16 18:18");
check(fn() => serialize($mdtz));
check(fn() => timezone_name_get($mdtz));
check(fn() => $mdtz->getName());
check(fn() => timezone_offset_get($mdtz, $dt));
check(fn() => $mdtz->getOffset($dt));
check(fn() => timezone_offset_get($dtz, $mdt));
check(fn() => $dtz->getOffset($mdt));
check(fn() => timezone_transitions_get($mdtz, time()));
check(fn() => $mdtz->getTransitions(time()));
check(fn() => timezone_location_get($mdtz,));
check(fn() => $mdtz->getLocation());
$script1_dataflow = $mdtz;
$v1=$definedVars[array_rand($definedVars = get_defined_vars())];
date_default_timezone_set('Europe/Oslo');
$tz1 = timezone_open("GMT");
$tz2 = timezone_open("Europe/London");
$tz3 = timezone_open("America/Los_Angeles");
$d = array();
$d[] = date_create("2005-07-14 22:30:41");
$d[] = date_create("2005-07-14 22:30:41 GMT");
$d[] = date_create("2005-07-14 22:30:41 CET");
$d[] = date_create("2005-07-14 22:30:41 CEST");
$d[] = date_create("2005-07-14 22:30:41 Europe/Oslo");
$d[] = date_create("2005-07-14 22:30:41 America/Los_Angeles");
$d[] = date_create("2005-07-14 22:30:41", $tz1);
$d[] = date_create("2005-07-14 22:30:41", $tz2);
$d[] = date_create("2005-07-14 22:30:41", $script1_dataflow);
$d[] = date_create("2005-07-14 22:30:41 GMT", $tz1);
$d[] = date_create("2005-07-14 22:30:41 GMT", $tz2);
$d[] = date_create("2005-07-14 22:30:41 GMT", $tz3);
$d[] = date_create("2005-07-14 22:30:41 Europe/Oslo", $tz1);
$d[] = date_create("2005-07-14 22:30:41 America/Los_Angeles", $tz2);
foreach($d as $date) {
    echo $date->format(DateTime::ISO8601), "\n";
}
$v2=$definedVars[array_rand($definedVars = get_defined_vars())];
$v3=$definedVars[array_rand($definedVars = get_defined_vars())];;
?>
