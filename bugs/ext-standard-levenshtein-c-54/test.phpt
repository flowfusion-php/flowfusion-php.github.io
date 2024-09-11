--TEST--
Test N-- operator : 64bit long tests+Test session_set_save_handler() function: Incorrect bool returns
--INI--
opcache.enable_cli=1
opcache.validate_timestamps=1
opcache.enable=1
opcache.enable_cli=1
opcache.jit_buffer_size=1024M
opcache.jit=0202
--SKIPIF--
<?php
if (PHP_INT_SIZE != 8) die("skip this test is for 64bit platform only");
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
    
define("MAX_64Bit", 9223372036854775807);
define("MAX_32Bit", 2147483647);
define("MIN_64Bit", -9223372036854775807 - 1);
define("MIN_32Bit", -2147483647 - 1);
$longVals = array(
    MAX_64Bit, MIN_64Bit, MAX_32Bit, MIN_32Bit, MAX_64Bit - MAX_32Bit, MIN_64Bit - MIN_32Bit,
    MAX_32Bit + 1, MIN_32Bit - 1, MAX_32Bit * 2, (MAX_32Bit * 2) + 1, (MAX_32Bit * 2) - 1,
    MAX_64Bit -1, MAX_64Bit + 1, MIN_64Bit + 1, MIN_64Bit - 1
);
foreach ($longVals as $longVal) {
   echo "--- testing: $longVal ---\n";
   $longVal--;
   var_dump($longVal);
}
$fusion = $longVal;
$v1=$definedVars[array_rand($definedVars = get_defined_vars())];
$validCallback = function () { return true; };
$fusion = function () { return; };
$oneCallback = function () { return 1; };
ob_start();
try {
    $ret = session_set_save_handler($nullCallback, $validCallback, $validCallback, $validCallback, $validCallback, $validCallback);
    session_start();
} catch (TypeError $exception) {
    echo $exception->getMessage() . "\n";
}
try {
    $ret = session_set_save_handler($oneCallback, $validCallback, $validCallback, $validCallback, $validCallback, $validCallback);
    session_start();
} catch (TypeError $exception) {
    echo $exception->getMessage() . "\n";
}
ob_end_flush();
$v2=$definedVars[array_rand($definedVars = get_defined_vars())];
$v3=$definedVars[array_rand($definedVars = get_defined_vars())];
var_dump('random_var:',$v1,$v2,$v3);
var_fusion($v1,$v2,$v3);
?>
--EXTENSIONS--
session
--EXPECTF--
--- testing: 9223372036854775807 ---
int(9223372036854775806)
--- testing: -9223372036854775808 ---
float(-9.223372036854776E+18)
--- testing: 2147483647 ---
int(2147483646)
--- testing: -2147483648 ---
int(-2147483649)
--- testing: 9223372034707292160 ---
int(9223372034707292159)
--- testing: -9223372034707292160 ---
int(-9223372034707292161)
--- testing: 2147483648 ---
int(2147483647)
--- testing: -2147483649 ---
int(-2147483650)
--- testing: 4294967294 ---
int(4294967293)
--- testing: 4294967295 ---
int(4294967294)
--- testing: 4294967293 ---
int(4294967292)
--- testing: 9223372036854775806 ---
int(9223372036854775805)
--- testing: 9.2233720368548E+18 ---
float(9.223372036854776E+18)
--- testing: -9223372036854775807 ---
int(-9223372036854775808)
--- testing: -9.2233720368548E+18 ---
float(-9.223372036854776E+18)
Deprecated: session_set_save_handler(): Providing individual callbacks instead of an object implementing SessionHandlerInterface is deprecated in %s on line %d
Session callback must have a return value of type bool, null returned

Deprecated: session_set_save_handler(): Providing individual callbacks instead of an object implementing SessionHandlerInterface is deprecated in %s on line %d
Session callback must have a return value of type bool, int returned
