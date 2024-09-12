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
    
try {
    $a = 1;
    $a %= 0;
} catch (Error $e) { var_dump($a); }
try {
    $a = 1;
    $a >>= -1;
} catch (Error $e) { var_dump($a); }
try {
    $a = 1;
    $a <<= -1;
} catch (Error $e) { var_dump($a); }
set_error_handler(function($type, $msg) { throw new Exception($msg); });
try {
    $a = [];
    $a .= "foo";
} catch (Throwable $e) { var_dump($a); }
try {
    $a = "foo";
    $a .= [];
} catch (Throwable $e) { var_dump($a); }
$x = new stdClass;
try { $x += 1; }
catch (Throwable $e) {}
var_dump($x);
$x = 1;
try { $x += new stdClass; }
catch (Throwable $e) {}
var_dump($x);
$x = "foo";
try { $x += new stdClass; }
catch (Throwable $e) {}
var_dump($x);
$x = new stdClass;
try { $x -= 1; }
catch (Throwable $e) {}
var_dump($x);
$x = 1;
try { $x -= new stdClass; }
catch (Throwable $e) {}
var_dump($x);
$x = "foo";
try { $x -= new stdClass; }
catch (Throwable $e) {}
var_dump($x);
$x = new stdClass;
try { $x *= 1; }
catch (Throwable $e) {}
var_dump($x);
$x = 1;
try { $x *= new stdClass; }
catch (Throwable $e) {}
var_dump($x);
$x = "foo";
try { $x *= new stdClass; }
catch (Throwable $e) {}
var_dump($x);
$x = new stdClass;
try { $x /= 1; }
catch (Throwable $e) {}
var_dump($x);
$x = 1;
try { $x /= new stdClass; }
catch (Throwable $e) {}
var_dump($x);
$x = "foo";
try { $x /= new stdClass; }
catch (Throwable $e) {}
var_dump($x);
$x = new stdClass;
try { $x %= 1; }
catch (Throwable $e) {}
var_dump($x);
$x = 1;
try { $x %= new stdClass; }
catch (Throwable $e) {}
var_dump($x);
$x = "foo";
try { $x %= new stdClass; }
catch (Throwable $e) {}
var_dump($x);
$x = new stdClass;
try { $x **= 1; }
catch (Throwable $e) {}
var_dump($x);
$x = 1;
try { $x **= new stdClass; }
catch (Throwable $e) {}
var_dump($x);
$x = "foo";
try { $x **= new stdClass; }
catch (Throwable $e) {}
var_dump($x);
$x = new stdClass;
try { $x ^= 1; }
catch (Throwable $e) {}
var_dump($x);
$x = 1;
try { $x ^= new stdClass; }
catch (Throwable $e) {}
var_dump($x);
$x = "foo";
try { $x ^= new stdClass; }
catch (Throwable $e) {}
var_dump($x);
$x = new stdClass;
try { $x &= 1; }
catch (Throwable $e) {}
var_dump($x);
$x = 1;
try { $x &= new stdClass; }
catch (Throwable $e) {}
var_dump($x);
$x = "foo";
try { $x &= new stdClass; }
catch (Throwable $e) {}
var_dump($x);
$x = new stdClass;
try { $x |= 1; }
catch (Throwable $e) {}
var_dump($x);
$x = 1;
try { $x |= new stdClass; }
catch (Throwable $e) {}
var_dump($x);
$x = "foo";
try { $x |= new stdClass; }
catch (Throwable $e) {}
var_dump($x);
$x = new stdClass;
try { $x <<= 1; }
catch (Throwable $e) {}
var_dump($x);
$x = 1;
try { $x <<= new stdClass; }
catch (Throwable $e) {}
var_dump($x);
$x = "foo";
try { $x <<= new stdClass; }
catch (Throwable $e) {}
var_dump($x);
$x = new stdClass;
try { $x >>= 1; }
catch (Throwable $e) {}
var_dump($x);
$x = 1;
try { $x >>= new stdClass; }
catch (Throwable $e) {}
var_dump($x);
$x = "foo";
try { $x >>= new stdClass; }
catch (Throwable $e) {}
var_dump($x);
$v1=$definedVars[array_rand($definedVars = get_defined_vars())];
var_dump(version_compare('1.2', '2.1', '??'));
$v2=$definedVars[array_rand($definedVars = get_defined_vars())];
$v3=$definedVars[array_rand($definedVars = get_defined_vars())];
var_dump('random_var:',$v1,$v2,$v3);
var_fusion($v1,$v2,$v3);
?>
