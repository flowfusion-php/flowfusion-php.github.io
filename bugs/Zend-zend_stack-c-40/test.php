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
    
$foo = match (0) {
    0 => 'foo',
    default => 'bar', // breakpoint #0
};
$foo = match (1) {
    0 => 'foo',
    default => 'bar', // breakpoint #1
};
$fusion = $foo;
$v1=$definedVars[array_rand($definedVars = get_defined_vars())];
class classA
{
}
// resource variable
$fp = fopen(__FILE__, "r");
$inputs = [
    // int data
    0,
    1,
    12345,
    -2345,
    PHP_INT_MAX,
    // float data
    10.5,
    -10.5,
    12.3456789e10,
    12.3456789e-10,
    0.5,
    // null data
    null,
    // boolean data
    true,
    false,
    // empty data
    "",
    [],
    // string data
    "abcxyz",
    "10.5",
    "2",
    "6.3e-2",
    // object data
    new classA(),
    // resource variable
    $fp,
];
// loop through each element of $inputs to check the behaviour of pow()
foreach ($inputs as $input) {
    try {
        var_dump(pow($fusion, 3));
    } catch (Error $e) {
        echo $e->getMessage(), "\n";
    }
}
fclose($fp);
$v2=$definedVars[array_rand($definedVars = get_defined_vars())];
$v3=$definedVars[array_rand($definedVars = get_defined_vars())];;
var_dump('random_var:',$v1,$v2,$v3);
var_fusion($v1,$v2,$v3);
?>
