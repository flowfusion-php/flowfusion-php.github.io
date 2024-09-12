--TEST--
DateTime uninitialised exceptions+date_create() function [1]
--INI--
date.timezone=Europe/London
sendmail_path={MAIL:{PWD}/gh7902.eml}
session.trans_sid_hosts=php.net
--FILE--
<?php
function fuzz_internal_interface($vars) {
    $result = array();
    // Get all loaded extensions
    $extensions = get_loaded_extensions();
    // Initialize an array to hold all internal and extension functions
    $allInternalFunctions = array();
    // Get all defined functions
    $definedFunctions = get_defined_functions();
    $internalFunctions = $definedFunctions['internal'];
    $allInternalFunctions = array_merge($allInternalFunctions, $internalFunctions);
    // Iterate over each extension to get its functions
    foreach ($extensions as $extension) {
        $functions = get_extension_funcs($extension);
        if ($functions !== false) {
            $allInternalFunctions = array_merge($allInternalFunctions, $functions);
        }
    }
    // Filter out POSIX-related functions
    $allInternalFunctions = array_filter($allInternalFunctions, function($func) {
        return strpos($func, 'posix_') !== 0;
    });
    foreach ($vars as $i => $v1) {
        foreach ($vars as $j => $v2) {
            try {
                // Pick a random internal function
                $randomFunction = $allInternalFunctions[array_rand($allInternalFunctions)];
                // Get reflection of the function to determine the number of parameters
                $reflection = new ReflectionFunction($randomFunction);
                $numParams = $reflection->getNumberOfParameters();
                // Prepare arguments
                $args = [];
                for ($k = 0; $k < $numParams; $k++) {
                    $args[] = ($k % 2 == 0) ? $v1 : $v2;
                }
                // Print out the function being called and arguments
                echo "Calling function: $randomFunction with arguments: ";
                echo implode(', ', $args) . "
";
                // Call the function with prepared arguments
                $result[$randomFunction][] = $reflection->invokeArgs($args);
            } catch (\Throwable $e) {
                // Handle any exceptions or errors
                echo "Error calling function $randomFunction: " . $e->getMessage() . "
";
            }
        }
    }
    return $result;
}
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
var_dump('random_var:',$v1,$v2,$v3);
var_fusion($v1,$v2,$v3);
?>
--EXPECTF--
DateObjectError: Object of type MyDateTimeZone (inheriting DateTimeZone) has not been correctly initialized by calling parent::__construct() in its constructor
DateObjectError: Object of type MyDateTimeZone (inheriting DateTimeZone) has not been correctly initialized by calling parent::__construct() in its constructor
DateObjectError: Object of type MyDateTimeZone (inheriting DateTimeZone) has not been correctly initialized by calling parent::__construct() in its constructor
DateObjectError: Object of type MyDateTimeZone (inheriting DateTimeZone) has not been correctly initialized by calling parent::__construct() in its constructor
DateObjectError: Object of type MyDateTimeZone (inheriting DateTimeZone) has not been correctly initialized by calling parent::__construct() in its constructor
DateObjectError: Object of type MyDateTime (inheriting DateTime) has not been correctly initialized by calling parent::__construct() in its constructor
DateObjectError: Object of type MyDateTime (inheriting DateTime) has not been correctly initialized by calling parent::__construct() in its constructor
DateObjectError: Object of type MyDateTimeZone (inheriting DateTimeZone) has not been correctly initialized by calling parent::__construct() in its constructor
DateObjectError: Object of type MyDateTimeZone (inheriting DateTimeZone) has not been correctly initialized by calling parent::__construct() in its constructor
DateObjectError: Object of type MyDateTimeZone (inheriting DateTimeZone) has not been correctly initialized by calling parent::__construct() in its constructor
DateObjectError: Object of type MyDateTimeZone (inheriting DateTimeZone) has not been correctly initialized by calling parent::__construct() in its constructor
2005-07-14T22:30:41+0200
2005-07-14T22:30:41+0000
2005-07-14T22:30:41+0100
2005-07-14T22:30:41+0200
2005-07-14T22:30:41+0200
2005-07-14T22:30:41-0700
2005-07-14T22:30:41+0000
2005-07-14T22:30:41+0100
2005-07-14T22:30:41-0700
2005-07-14T22:30:41+0000
2005-07-14T22:30:41+0000
2005-07-14T22:30:41+0000
2005-07-14T22:30:41+0200
2005-07-14T22:30:41-0700
