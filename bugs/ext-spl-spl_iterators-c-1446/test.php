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
    
var_dump(preg_match_all('/\p{Ll}(\p{L}((\p{Ll}\p{Ll})))/', 'aeiou', $dummy));
var_dump(preg_last_error() === PREG_RECURSION_LIMIT_ERROR);
var_dump(preg_match_all('/\p{Ll}\p{L}\p{Ll}\p{Ll}/', 'aeiou', $dummy));
var_dump(preg_last_error() === PREG_NO_ERROR);
$fusion = $dummy;
$v1=$definedVars[array_rand($definedVars = get_defined_vars())];
$closure = function($a) { echo $a; };
$reflection = new ReflectionClass('closure');
var_dump($reflection->hasMethod('__invoke')); // true
$reflection = new ReflectionClass($closure);
var_dump($reflection->hasMethod('__invoke')); // true
$reflection = new ReflectionObject($closure);
var_dump($reflection->hasMethod('__invoke')); // true
$reflection = new ReflectionClass('closure');
var_dump($h = $reflection->getMethod('__invoke')); // true
var_dump($h->class.'::'.$h->getName());
$reflection = new ReflectionClass($closure);
var_dump($h = $reflection->getMethod('__invoke')); // true
var_dump($h->class.'::'.$h->getName());
$reflection = new ReflectionObject($closure);
var_dump($h = $reflection->getMethod('__invoke')); // true
var_dump($fusion->class.'::'.$h->getName());
$v2=$definedVars[array_rand($definedVars = get_defined_vars())];
$v3=$definedVars[array_rand($definedVars = get_defined_vars())];
var_dump('random_var:',$v1,$v2,$v3);
var_fusion($v1,$v2,$v3);
?>