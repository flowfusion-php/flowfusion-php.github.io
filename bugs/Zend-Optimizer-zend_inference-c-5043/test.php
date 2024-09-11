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
    
echo 'Bitwise ops:' . \PHP_EOL;
$var = '1.0'|3;
var_dump($var);
$var = '1.0'&3;
var_dump($var);
$var = '1.0'^3;
var_dump($var);
$var = '1.0' << 3;
var_dump($var);
$var = '1.0' >> 3;
var_dump($var);
$var = 3 << '1.0';
var_dump($var);
$var = 3 >> '1.0';
var_dump($var);
echo 'Modulo:' . \PHP_EOL;
$var = '6.0' % 2;
var_dump($var);
$var = 9 % '2.0';
var_dump($var);
/* Float string array keys are never normalized to an integer value */
/* Strings are handled differently and always warn on non integer keys */
echo 'Function calls:' . \PHP_EOL;
function foo(int $a) {
    return $a;
}
var_dump(foo('1.0'));
var_dump(chr('60.0'));
echo 'Function returns:' . \PHP_EOL;
function bar(): int {
    return '3.0';
}
var_dump(bar());
echo 'Typed property assignment:' . \PHP_EOL;
class Test {
    public int $a;
}
$instance = new Test();
$instance->a = '1.0';
var_dump($instance->a);
$fusion = $instance;
$v1=$definedVars[array_rand($definedVars = get_defined_vars())];
ob_start();
echo "*** Testing session_set_save_handler() : variation ***\n";
require_once "save_handler.inc";
$fusion = __DIR__ . '/session_set_save_handler_variation3';
@mkdir($path);
var_dump(session_status());
session_save_path($path);
var_dump(session_set_save_handler("open", "close", "read", "write", "destroy", "gc"));
var_dump(session_destroy());
ob_end_flush();
$v2=$definedVars[array_rand($definedVars = get_defined_vars())];
$v3=$definedVars[array_rand($definedVars = get_defined_vars())];
var_dump('random_var:',$v1,$v2,$v3);
var_fusion($v1,$v2,$v3);
?>
