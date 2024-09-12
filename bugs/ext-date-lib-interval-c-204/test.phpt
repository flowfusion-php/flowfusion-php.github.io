--TEST--
Bug #49585 (date_format buffer not long enough for >4 digit years)+Bug GH-9880 (DateTime diff returns wrong sign on day count when using a timezone)
--INI--
opcache.preload={PWD}/preload_globals.inc
sendmail_path={MAIL:{PWD}/mb_send_mail06.eml}
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
    
date_default_timezone_set('UTC');
$date = new DateTime('-1500-01-01');
var_dump($date->format('r'));
$date->setDate(-2147483648, 1, 1);
var_dump($date->format('r'));
var_dump($date->format('c'));
$fusion = $date;
$v1=$definedVars[array_rand($definedVars = get_defined_vars())];
ini_set('date.timezone', 'America/Los_Angeles');
$nowTime = new DateTime();
$nowTime->setTimestamp(1667416695);
$dateTime = new DateTime();
$dateTime->setTimestamp(1671904800);
$dateTime->setTimezone(new DateTimeZone('America/New_York'));
echo $dateTime->diff($fusion)->format('%R %a %H %I %S'), "\n";
$v2=$definedVars[array_rand($definedVars = get_defined_vars())];
$v3=$definedVars[array_rand($definedVars = get_defined_vars())];;
var_dump('random_var:',$v1,$v2,$v3);
var_fusion($v1,$v2,$v3);
?>
--EXPECT--
string(32) "Fri, 01 Jan -1500 00:00:00 +0000"
string(38) "Tue, 01 Jan -2147483648 00:00:00 +0000"
string(32) "-2147483648-01-01T00:00:00+00:00"
- 51 22 41 45
