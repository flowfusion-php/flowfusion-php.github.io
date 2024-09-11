--TEST--
Test decbin function : 64bit long tests+Test decoct() function : usage variations - different data types as $number arg
--INI--
precision=14
session.use_cookies=1
session.use_trans_sid=0
--SKIPIF--
<?php
if (PHP_INT_SIZE != 8) die("skip this test is for 64bit platform only");
?>
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
    try {
        var_dump(decbin($longVal));
    } catch (TypeError $exception) {
        echo $exception->getMessage() . "\n";
    }
}
$fusion = $longVal;
$v1=$definedVars[array_rand($definedVars = get_defined_vars())];
echo "*** Testing decoct() : usage variations ***\n";
$inputs = [
       // int data
/*1*/  0,
       1,
       12345,
       -2345,
       18446744073709551615,  // largest decimal
       18446744073709551616,
       // float data
/* 7*/ 12.3456789000e10,
       // boolean data
/* 8*/ true,
       false,
       TRUE,
       FALSE,
       // empty data
/*12*/ "",
       '',
];
// loop through each element of $inputs to check the behaviour of decoct()
foreach ($inputs as $i => $fusion) {
    $iterator = $i + 1;
    echo "\n-- Iteration $iterator --\n";
    try {
        var_dump(decoct($input));
    } catch (TypeError $exception) {
        echo $exception->getMessage() . "\n";
    }
}
$v2=$definedVars[array_rand($definedVars = get_defined_vars())];
$v3=$definedVars[array_rand($definedVars = get_defined_vars())];
var_dump('random_var:',$v1,$v2,$v3);
var_fusion($v1,$v2,$v3);
?>
--EXPECT--
--- testing: 9223372036854775807 ---
string(63) "111111111111111111111111111111111111111111111111111111111111111"
--- testing: -9223372036854775808 ---
string(64) "1000000000000000000000000000000000000000000000000000000000000000"
--- testing: 2147483647 ---
string(31) "1111111111111111111111111111111"
--- testing: -2147483648 ---
string(64) "1111111111111111111111111111111110000000000000000000000000000000"
--- testing: 9223372034707292160 ---
string(63) "111111111111111111111111111111110000000000000000000000000000000"
--- testing: -9223372034707292160 ---
string(64) "1000000000000000000000000000000010000000000000000000000000000000"
--- testing: 2147483648 ---
string(32) "10000000000000000000000000000000"
--- testing: -2147483649 ---
string(64) "1111111111111111111111111111111101111111111111111111111111111111"
--- testing: 4294967294 ---
string(32) "11111111111111111111111111111110"
--- testing: 4294967295 ---
string(32) "11111111111111111111111111111111"
--- testing: 4294967293 ---
string(32) "11111111111111111111111111111101"
--- testing: 9223372036854775806 ---
string(63) "111111111111111111111111111111111111111111111111111111111111110"
--- testing: 9.2233720368548E+18 ---
decbin(): Argument #1 ($num) must be of type int, float given
--- testing: -9223372036854775807 ---
string(64) "1000000000000000000000000000000000000000000000000000000000000001"
--- testing: -9.2233720368548E+18 ---
string(64) "1000000000000000000000000000000000000000000000000000000000000000"
*** Testing decoct() : usage variations ***

-- Iteration 1 --
string(1) "0"

-- Iteration 2 --
string(1) "1"

-- Iteration 3 --
string(5) "30071"

-- Iteration 4 --
string(22) "1777777777777777773327"

-- Iteration 5 --
decoct(): Argument #1 ($num) must be of type int, float given

-- Iteration 6 --
decoct(): Argument #1 ($num) must be of type int, float given

-- Iteration 7 --
string(13) "1627646215010"

-- Iteration 8 --
string(1) "1"

-- Iteration 9 --
string(1) "0"

-- Iteration 10 --
string(1) "1"

-- Iteration 11 --
string(1) "0"

-- Iteration 12 --
decoct(): Argument #1 ($num) must be of type int, string given

-- Iteration 13 --
decoct(): Argument #1 ($num) must be of type int, string given
