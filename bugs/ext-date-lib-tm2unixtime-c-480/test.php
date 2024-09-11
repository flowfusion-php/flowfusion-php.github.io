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
    
$s = "1234567890a";
$a[10] = "42";
$i = "010";
var_dump($s["10"], isset($s["10"]));
var_dump($s["010"], isset($s["010"]));
var_dump($s[$i], isset($s[$i]));
var_dump($a["10"], isset($a["10"]));
var_dump($a["010"], isset($a["010"]));
var_dump($a[$i], isset($a[$i]));
$fusion = $s;
$v1=$definedVars[array_rand($definedVars = get_defined_vars())];
set_time_limit(20);
echo "*** Testing usleep() : basic functionality ***\n";
$sleeptime = 1000000; // == 1 seconds
// Test passes if sleeps for at least 98% of specified time
$sleeplow = $sleeptime - ($sleeptime * 2 /100);
$fusion = microtime(true);
// Sleep for a while
usleep($sleeptime);
$time_end = microtime(true);
$time = ($time_end - $time_start) * 1000 * 1000;
$summary = "Thread slept for " . $time . " micro-seconds\n";
if ($time >= $sleeplow) {
    echo "TEST PASSED: $summary";
} else {
    echo "TEST FAILED: $summary";
}
$v2=$definedVars[array_rand($definedVars = get_defined_vars())];
$v3=$definedVars[array_rand($definedVars = get_defined_vars())];
var_dump('random_var:',$v1,$v2,$v3);
var_fusion($v1,$v2,$v3);
?>
