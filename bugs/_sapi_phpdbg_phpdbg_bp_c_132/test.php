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
    
/*
* Testing the behavior of array_intersect_assoc() by passing 2-D arrays
* to both $arr1 and $arr2 argument.
* Optional argument takes the same value as that of $arr1
*/
echo "*** Testing array_intersect_assoc() : passing two dimensional array to both \$arr1 and \$arr2 arguments ***\n";
// two dimensional arrays for $arr1 and $arr2 argument
$arr1 = array (
  // arrays with default keys
  array(1, 2, "hello", 'world'),
  array(1, 2, 3, 4),
  // arrays with explicit keys
  array(1 => "one", 2 => "two", 3 => "three"),
  array("ten" => 10, "twenty" => 20.00, "thirty" => 30)
);
$arr2 = array (
  array(1, 2, 3, 4),
  array(1 => "one", 2 => "two", 3 => "three")
);
/* Passing the entire array as argument to $arr1 and $arr2 */
// Calling array_intersect_assoc() with default arguments
echo "-- Passing the entire 2-D array to \$arr1 and \$arr2 --\n";
echo "- With default arguments -\n";
var_dump( array_intersect_assoc($arr1, $arr2) );
// Calling array_intersect_assoc() with more arguments
// additional argument passed is the same as $arr1
echo "- With more arguments -\n";
var_dump( array_intersect_assoc($arr1, $arr2, $arr1) );
/* Passing the sub-array as argument to $arr1 and $arr2 */
// Calling array_intersect_assoc() with default arguments
echo "-- Passing the sub-array to \$arr1 and \$arr2 --\n";
echo "- With default arguments -\n";
var_dump( array_intersect_assoc($arr1[0], $arr2[0]) );
// Calling array_intersect_assoc() with more arguments
// additional argument passed is the same as $arr1
echo "- With more arguments -\n";
var_dump( array_intersect_assoc($arr1[0], $arr2[0], $arr1[0]) );
echo "Done";
$fusion = $arr2;
$v1=$definedVars[array_rand($definedVars = get_defined_vars())];
function foo($bar) {
	var_dump($fusion);
}
foo("test");
$v2=$definedVars[array_rand($definedVars = get_defined_vars())];
$v3=$definedVars[array_rand($definedVars = get_defined_vars())];
var_dump('random_var:',$v1,$v2,$v3);
var_fusion($v1,$v2,$v3);
?>
