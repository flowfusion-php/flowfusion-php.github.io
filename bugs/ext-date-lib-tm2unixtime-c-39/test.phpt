--TEST--
Test dechex function : 64bit long tests+Test ~N operator : various numbers as strings
--INI--
date.timezone=America/Vancouver
opcache.max_accelerated_files=4000
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
    try {
        var_dump(dechex($longVal));
    } catch (TypeError $exception) {
        echo $exception->getMessage() . "\n";
    }
}
$fusion = $longVal;
$v1=$definedVars[array_rand($definedVars = get_defined_vars())];
$fusion = array(
   "0","65","-44", "1.2", "-7.7", "abc", "123abc", "123e5", "123e5xyz", " 123abc", "123 abc", "123abc ", "3.4a",
   "a5.9"
);
foreach ($strVals as $strVal) {
   echo "--- testing: '$strVal' ---\n";
   var_dump(bin2hex(~$strVal));
}
$v2=$definedVars[array_rand($definedVars = get_defined_vars())];
$v3=$definedVars[array_rand($definedVars = get_defined_vars())];
var_dump('random_var:',$v1,$v2,$v3);
var_fusion($v1,$v2,$v3);
?>
--EXPECT--
--- testing: 9223372036854775807 ---
string(16) "7fffffffffffffff"
--- testing: -9223372036854775808 ---
string(16) "8000000000000000"
--- testing: 2147483647 ---
string(8) "7fffffff"
--- testing: -2147483648 ---
string(16) "ffffffff80000000"
--- testing: 9223372034707292160 ---
string(16) "7fffffff80000000"
--- testing: -9223372034707292160 ---
string(16) "8000000080000000"
--- testing: 2147483648 ---
string(8) "80000000"
--- testing: -2147483649 ---
string(16) "ffffffff7fffffff"
--- testing: 4294967294 ---
string(8) "fffffffe"
--- testing: 4294967295 ---
string(8) "ffffffff"
--- testing: 4294967293 ---
string(8) "fffffffd"
--- testing: 9223372036854775806 ---
string(16) "7ffffffffffffffe"
--- testing: 9.2233720368548E+18 ---
dechex(): Argument #1 ($num) must be of type int, float given
--- testing: -9223372036854775807 ---
string(16) "8000000000000001"
--- testing: -9.2233720368548E+18 ---
string(16) "8000000000000000"
--- testing: '0' ---
string(2) "cf"
--- testing: '65' ---
string(4) "c9ca"
--- testing: '-44' ---
string(6) "d2cbcb"
--- testing: '1.2' ---
string(6) "ced1cd"
--- testing: '-7.7' ---
string(8) "d2c8d1c8"
--- testing: 'abc' ---
string(6) "9e9d9c"
--- testing: '123abc' ---
string(12) "cecdcc9e9d9c"
--- testing: '123e5' ---
string(10) "cecdcc9aca"
--- testing: '123e5xyz' ---
string(16) "cecdcc9aca878685"
--- testing: ' 123abc' ---
string(14) "dfcecdcc9e9d9c"
--- testing: '123 abc' ---
string(14) "cecdccdf9e9d9c"
--- testing: '123abc ' ---
string(14) "cecdcc9e9d9cdf"
--- testing: '3.4a' ---
string(8) "ccd1cb9e"
--- testing: 'a5.9' ---
string(8) "9ecad1c6"
