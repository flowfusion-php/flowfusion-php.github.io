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
    
date_default_timezone_set('Asia/Calcutta');
$date1 = date_create("2009-03-27");
$date2 = date_create("2009-03-01");
print "\$date1 at init: " . $date1->format("Y-m-d") . "\n";
print "\$date2 at init: " . $date2->format("Y-m-d") . "\n";
$diff = $date1->diff($date2);
print "\$date1 after first diff: " . $date1->format("Y-m-d") . "\n";
print "\$diff->days after first diff: " . $diff->days . "\n";
$date1 = $date1->sub(new DateInterval("P2D"));
print "\$date1 after sub: " . $date1->format("Y-m-d") . "\n";
$diff = $date1->diff($date2);
print "\$date1 after second diff (called at \$date1): " .
$date1->format("Y-m-d") . "\n";
print "\$diff->days after second diff: " . $diff->days . "\n";
$diff = $date2->diff($date1);
print "\$date1 after third diff (called at \$date2): " .
$date1->format("Y-m-d") . "\n";
print "\$diff->days after third diff: " . $diff->days . "\n";
$v1=$definedVars[array_rand($definedVars = get_defined_vars())];
echo date(DATE_ATOM."\n".DATE_RFC2822."\nc\nr\no\ny\nY\nU\n\n", PHP_INT_MIN);
echo date(DATE_ATOM."\n".DATE_RFC2822."\nc\nr\no\ny\nY\nU\n\n", 67767976233532799);
echo date(DATE_ATOM."\n".DATE_RFC2822."\nc\nr\no\ny\nY\nU\n\n", 67767976233532800);
echo date(DATE_ATOM."\n".DATE_RFC2822."\nc\nr\no\ny\nY\nU\n\n", PHP_INT_MAX);
$v2=$definedVars[array_rand($definedVars = get_defined_vars())];
$v3=$definedVars[array_rand($definedVars = get_defined_vars())];
var_dump('random_var:',$v1,$v2,$v3);
var_fusion($v1,$v2,$v3);
?>
