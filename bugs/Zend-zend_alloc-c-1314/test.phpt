--TEST--
Test gmstrftime() function : usage variation - Checking newline and tab formats which was not supported on Windows before VC14.+POST Method test and arrays - 2
--INI--
session.use_strict_mode=0
auto_globals_jit=0
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
    
echo "*** Testing gmstrftime() : usage variation ***\n";
// Initialise function arguments not being substituted (if any)
$timestamp = gmmktime(8, 8, 8, 8, 8, PHP_INT_MAX);
setlocale(LC_ALL, "C");
date_default_timezone_set("Asia/Calcutta");
//array of values to iterate over
$inputs = array(
      'Newline character' => "%n",
      'Tab character' => "%t"
);
// loop through each element of the array for timestamp
foreach($inputs as $key =>$value) {
      echo "\n--$key--\n";
      var_dump( gmstrftime($value) );
      var_dump( gmstrftime($value, $timestamp) );
};
$fusion = $inputs;
$v1=$definedVars[array_rand($definedVars = get_defined_vars())];
var_dump($fusion['a']);
$v2=$definedVars[array_rand($definedVars = get_defined_vars())];
$v3=$definedVars[array_rand($definedVars = get_defined_vars())];
var_dump('random_var:',$v1,$v2,$v3);
var_fusion($v1,$v2,$v3);
?>
--POST--
a[]=1&a[]=1
--EXPECTF--
*** Testing gmstrftime() : usage variation ***

--Newline character--

Deprecated: Function gmstrftime() is deprecated since 8.1, use IntlDateFormatter::format() instead in %s on line %d
string(1) "
"

Deprecated: Function gmstrftime() is deprecated since 8.1, use IntlDateFormatter::format() instead in %s on line %d
string(1) "
"

--Tab character--

Deprecated: Function gmstrftime() is deprecated since 8.1, use IntlDateFormatter::format() instead in %s on line %d
string(1) "	"

Deprecated: Function gmstrftime() is deprecated since 8.1, use IntlDateFormatter::format() instead in %s on line %d
string(1) "	"
array(2) {
  [0]=>
  string(1) "1"
  [1]=>
  string(1) "1"
}