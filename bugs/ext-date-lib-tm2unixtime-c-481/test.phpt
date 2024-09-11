--TEST--
Test gmdate() function : usage variation - Valid and invalid range of timestamp 64 bits.+Trying to access a constant on trait via the name of trait causes a Fatal error
--INI--
opcache.jit=1235
session.save_handler=files
opcache.enable=1
opcache.enable_cli=1
opcache.jit_buffer_size=1024M
opcache.jit=0154
--SKIPIF--
<?php
if (PHP_INT_SIZE == 4) die('skip 64 bit only');
?>
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
    
echo "*** Testing gmdate() : usage variation ***\n";
// Initialise all required variables
date_default_timezone_set('UTC');
$timestamp = mktime(20, 45, PHP_INT_MIN, 12, 13, 1901);
echo "\n-- Testing gmdate() function with minimum range of timestamp --\n";
var_dump( gmdate(DATE_ISO8601, $timestamp) );
$timestamp = mktime(20, 45, 50, 12, 13, 1901);
echo "\n-- Testing gmdate() function with less than the range of timestamp --\n";
var_dump( gmdate(DATE_ISO8601, $timestamp) );
echo "\n-- Testing gmdate() function with maximum range of timestamp --\n";
$timestamp = mktime(03, 14, 07, 1, 19, 2038);
var_dump( gmdate(DATE_ISO8601, $timestamp) );
echo "\n-- Testing gmdate() function with greater than the range of timestamp --\n";
$timestamp = mktime(03, 14, 10, 1, 19, 2038);
var_dump( gmdate(DATE_ISO8601, $timestamp) );
$v1=$definedVars[array_rand($definedVars = get_defined_vars())];
trait Foo {
    const A = 42;
}
class Bar {
    use Foo;
}
echo Foo::A, PHP_EOL;
$v2=$definedVars[array_rand($definedVars = get_defined_vars())];
$v3=$definedVars[array_rand($definedVars = get_defined_vars())];
var_dump('random_var:',$v1,$v2,$v3);
var_fusion($v1,$v2,$v3);
?>
--EXPECTF--
*** Testing gmdate() : usage variation ***

-- Testing gmdate() function with minimum range of timestamp --
string(24) "1901-12-13T20:45:54+0000"

-- Testing gmdate() function with less than the range of timestamp --
string(24) "1901-12-13T20:45:50+0000"

-- Testing gmdate() function with maximum range of timestamp --
string(24) "2038-01-19T03:14:07+0000"

-- Testing gmdate() function with greater than the range of timestamp --
string(24) "2038-01-19T03:14:10+0000"
Fatal error: Uncaught Error: Cannot access trait constant Foo::A directly in %s:%d
Stack trace:
#0 {main}
  thrown in %s on line %d
