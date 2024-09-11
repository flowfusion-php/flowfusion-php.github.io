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
                // Prepare arguments alternating between v1 and v2
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
    try {
        fuzz_internal_interface($vars);
        fuzz_internal_interface($vars);
        fuzz_internal_interface($vars);
    } catch (ReflectionException $e) {
        echo("Error: " . $e->getMessage());
    }
    return $result;
}
    
$tests = array(
    array("Europe/Andorra",     PHP_INT_MIN, 17, 17, 1, 24764, 1970),
    array("Asia/Dubai",         17, 17, 17, 1, 1, 1970),
    array("Asia/Kabul",         17, 17, 17, 1, 1, 1970),
    array("America/Antigua",    17, 17, 17, 1, 1, 1970),
    array("America/Anguilla",   17, 17, 17, 1, 1, 1970),
    array("Europe/Tirane",      17, 17, 17, 1, 4849, 1970),
    array("Asia/Yerevan",       17, 17, 17, 1, 24764, 1970),
    array("America/Curacao",    17, 17, 17, 1, 1, 1970),
    array("Africa/Luanda",      17, 17, 17, 1, 1, 1970),
    array("Antarctica/McMurdo", 17, 17, 17, 1, 24743, 1970),
    array("Australia/Adelaide", 17, 17, 17, 1, 1, 1971),
    array("Australia/Darwin",   17, 17, 17, 1, 88, 1971),
    array("Australia/Perth",    17, 17, 17, 1, 1, 1971),
    array("America/Aruba",      17, 17, 17, 1, 88, 1971),
    array("Asia/Baku",          17, 17, 17, 1, 1, 1971),
    array("Europe/Sarajevo",    17, 17, 17, 1, 1, 1971),
    array("America/Barbados",   17, 17, 17, 1, 1, 1971),
    array("Asia/Dacca",         17, 17, 17, 1, 1, 1971),
    array("Europe/Brussels",    17, 17, 17, 1, 1, 1971),
    array("Africa/Ouagadougou", 17, 17, 17, 1, 88, 1971),
    array("Europe/Tirane",      17, 17, 17, 1, 4849, 1970),
    array("America/Buenos_Aires", 17, 17, 17, 1, 1734, 1970),
    array("America/Rosario",    17, 17, 17, 1, 1734, 1970),
    array("Europe/Vienna",      17, 17, 17, 1, 3743, 1970),
    array("Asia/Baku",          17, 17, 17, 1, 9490, 1970),
);
foreach ($tests as $test) {
    date_default_timezone_set($test[0]);
    print "{$test[0]}\n";
    array_shift($test);
    $timestamp = call_user_func_array('mktime', $test);
    print "ts     = ". date("l Y-m-d H:i:s T", $timestamp). "\n";
    $strtotime_tstamp = strtotime("first monday", $timestamp);
    print "result = ".date("l Y-m-d H:i:s T", $strtotime_tstamp)."\n";
    print "wanted = Monday            00:00:00\n\n";
}
$v1=$definedVars[array_rand($definedVars = get_defined_vars())];
die();
$v2=$definedVars[array_rand($definedVars = get_defined_vars())];
$v3=$definedVars[array_rand($definedVars = get_defined_vars())];
var_dump('random_var:',$v1,$v2,$v3);
var_fusion($v1,$v2,$v3);
?>
