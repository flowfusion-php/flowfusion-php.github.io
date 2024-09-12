--TEST--
Bug #69751: Change Error message of sprintf/printf for missing/typo position specifier.+Static variable with recursive initializer
--INI--
memory_limit=2G
highlight.html    = #000000
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
    
try {
    sprintf('%$s, %2$s %1$s', "a", "b");
} catch (ValueError $e) {
    echo $e->getMessage(), "\n";
}
try {
    sprintf('%3$s, %2$s %1$s', "a", "b");
} catch (ArgumentCountError $e) {
    echo $e->getMessage(), "\n";
}
try {
    sprintf('%2147483648$s, %2$s %1$s', "a", "b");
} catch (ValueError $e) {
    echo $e->getMessage(), "\n";
}
$fusion = $s;
$v1=$definedVars[array_rand($definedVars = get_defined_vars())];
function foo($i) {
    static $fusion = $i <= 10 ? foo($i + 1) : "Done $i";
    var_dump($a);
    return $i;
}
foo(0);
foo(5);
$v2=$definedVars[array_rand($definedVars = get_defined_vars())];
$v3=$definedVars[array_rand($definedVars = get_defined_vars())];;
var_dump('random_var:',$v1,$v2,$v3);
var_fusion($v1,$v2,$v3);
?>
--EXPECTF--
Argument number specifier must be greater than zero and less than %d
4 arguments are required, 3 given
Argument number specifier must be greater than zero and less than %d
string(7) "Done 11"
string(7) "Done 11"
string(7) "Done 11"
string(7) "Done 11"
string(7) "Done 11"
string(7) "Done 11"
string(7) "Done 11"
string(7) "Done 11"
string(7) "Done 11"
string(7) "Done 11"
string(7) "Done 11"
string(7) "Done 11"
string(7) "Done 11"
