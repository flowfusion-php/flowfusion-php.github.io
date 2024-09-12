--TEST--
Test % operator : 64bit long tests+Bug #66084 simplexml_load_string() mangles empty node name, json variant
--INI--
opcache.validate_timestamps=1
session.save_handler=files
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
$otherVals = array(0, 1, -1, 7, 9, 65, -44, MAX_32Bit, MAX_64Bit);
error_reporting(E_ERROR);
foreach ($longVals as $longVal) {
   foreach($otherVals as $otherVal) {
      echo "--- testing: $longVal % $otherVal ---\n";
      try {
        var_dump($longVal%$otherVal);
      } catch (DivisionByZeroError $e) {
        echo "Exception: " . $e->getMessage() . "\n";
      }
   }
}
foreach ($otherVals as $otherVal) {
   foreach($longVals as $longVal) {
      echo "--- testing: $otherVal % $longVal ---\n";
      try {
        var_dump($otherVal%$longVal);
      } catch (DivisionByZeroError $e) {
        echo "Exception: " . $e->getMessage() . "\n";
      }
   }
}
$v1=$definedVars[array_rand($definedVars = get_defined_vars())];
echo json_encode(simplexml_load_string('<a><b/><c><x/></c></a>')->c), "\n";
echo json_encode(simplexml_load_string('<a><b/><c><x/></c></a>')), "\n";
echo json_encode(simplexml_load_string('<a><b/><d/><c><x/></c></a>')), "\n";
echo json_encode(simplexml_load_string('<a><b/><c><d/><x/></c></a>')), "\n";
echo json_encode(simplexml_load_string('<a><b/><c><d><x/></d></c></a>')), "\n";
$v2=$definedVars[array_rand($definedVars = get_defined_vars())];
$v3=$definedVars[array_rand($definedVars = get_defined_vars())];
var_dump('random_var:',$v1,$v2,$v3);
var_fusion($v1,$v2,$v3);
?>
--EXTENSIONS--
simplexml
--EXPECT--
--- testing: 9223372036854775807 % 0 ---
Exception: Modulo by zero
--- testing: 9223372036854775807 % 1 ---
int(0)
--- testing: 9223372036854775807 % -1 ---
int(0)
--- testing: 9223372036854775807 % 7 ---
int(0)
--- testing: 9223372036854775807 % 9 ---
int(7)
--- testing: 9223372036854775807 % 65 ---
int(7)
--- testing: 9223372036854775807 % -44 ---
int(7)
--- testing: 9223372036854775807 % 2147483647 ---
int(1)
--- testing: 9223372036854775807 % 9223372036854775807 ---
int(0)
--- testing: -9223372036854775808 % 0 ---
Exception: Modulo by zero
--- testing: -9223372036854775808 % 1 ---
int(0)
--- testing: -9223372036854775808 % -1 ---
int(0)
--- testing: -9223372036854775808 % 7 ---
int(-1)
--- testing: -9223372036854775808 % 9 ---
int(-8)
--- testing: -9223372036854775808 % 65 ---
int(-8)
--- testing: -9223372036854775808 % -44 ---
int(-8)
--- testing: -9223372036854775808 % 2147483647 ---
int(-2)
--- testing: -9223372036854775808 % 9223372036854775807 ---
int(-1)
--- testing: 2147483647 % 0 ---
Exception: Modulo by zero
--- testing: 2147483647 % 1 ---
int(0)
--- testing: 2147483647 % -1 ---
int(0)
--- testing: 2147483647 % 7 ---
int(1)
--- testing: 2147483647 % 9 ---
int(1)
--- testing: 2147483647 % 65 ---
int(62)
--- testing: 2147483647 % -44 ---
int(23)
--- testing: 2147483647 % 2147483647 ---
int(0)
--- testing: 2147483647 % 9223372036854775807 ---
int(2147483647)
--- testing: -2147483648 % 0 ---
Exception: Modulo by zero
--- testing: -2147483648 % 1 ---
int(0)
--- testing: -2147483648 % -1 ---
int(0)
--- testing: -2147483648 % 7 ---
int(-2)
--- testing: -2147483648 % 9 ---
int(-2)
--- testing: -2147483648 % 65 ---
int(-63)
--- testing: -2147483648 % -44 ---
int(-24)
--- testing: -2147483648 % 2147483647 ---
int(-1)
--- testing: -2147483648 % 9223372036854775807 ---
int(-2147483648)
--- testing: 9223372034707292160 % 0 ---
Exception: Modulo by zero
--- testing: 9223372034707292160 % 1 ---
int(0)
--- testing: 9223372034707292160 % -1 ---
int(0)
--- testing: 9223372034707292160 % 7 ---
int(6)
--- testing: 9223372034707292160 % 9 ---
int(6)
--- testing: 9223372034707292160 % 65 ---
int(10)
--- testing: 9223372034707292160 % -44 ---
int(28)
--- testing: 9223372034707292160 % 2147483647 ---
int(1)
--- testing: 9223372034707292160 % 9223372036854775807 ---
int(9223372034707292160)
--- testing: -9223372034707292160 % 0 ---
Exception: Modulo by zero
--- testing: -9223372034707292160 % 1 ---
int(0)
--- testing: -9223372034707292160 % -1 ---
int(0)
--- testing: -9223372034707292160 % 7 ---
int(-6)
--- testing: -9223372034707292160 % 9 ---
int(-6)
--- testing: -9223372034707292160 % 65 ---
int(-10)
--- testing: -9223372034707292160 % -44 ---
int(-28)
--- testing: -9223372034707292160 % 2147483647 ---
int(-1)
--- testing: -9223372034707292160 % 9223372036854775807 ---
int(-9223372034707292160)
--- testing: 2147483648 % 0 ---
Exception: Modulo by zero
--- testing: 2147483648 % 1 ---
int(0)
--- testing: 2147483648 % -1 ---
int(0)
--- testing: 2147483648 % 7 ---
int(2)
--- testing: 2147483648 % 9 ---
int(2)
--- testing: 2147483648 % 65 ---
int(63)
--- testing: 2147483648 % -44 ---
int(24)
--- testing: 2147483648 % 2147483647 ---
int(1)
--- testing: 2147483648 % 9223372036854775807 ---
int(2147483648)
--- testing: -2147483649 % 0 ---
Exception: Modulo by zero
--- testing: -2147483649 % 1 ---
int(0)
--- testing: -2147483649 % -1 ---
int(0)
--- testing: -2147483649 % 7 ---
int(-3)
--- testing: -2147483649 % 9 ---
int(-3)
--- testing: -2147483649 % 65 ---
int(-64)
--- testing: -2147483649 % -44 ---
int(-25)
--- testing: -2147483649 % 2147483647 ---
int(-2)
--- testing: -2147483649 % 9223372036854775807 ---
int(-2147483649)
--- testing: 4294967294 % 0 ---
Exception: Modulo by zero
--- testing: 4294967294 % 1 ---
int(0)
--- testing: 4294967294 % -1 ---
int(0)
--- testing: 4294967294 % 7 ---
int(2)
--- testing: 4294967294 % 9 ---
int(2)
--- testing: 4294967294 % 65 ---
int(59)
--- testing: 4294967294 % -44 ---
int(2)
--- testing: 4294967294 % 2147483647 ---
int(0)
--- testing: 4294967294 % 9223372036854775807 ---
int(4294967294)
--- testing: 4294967295 % 0 ---
Exception: Modulo by zero
--- testing: 4294967295 % 1 ---
int(0)
--- testing: 4294967295 % -1 ---
int(0)
--- testing: 4294967295 % 7 ---
int(3)
--- testing: 4294967295 % 9 ---
int(3)
--- testing: 4294967295 % 65 ---
int(60)
--- testing: 4294967295 % -44 ---
int(3)
--- testing: 4294967295 % 2147483647 ---
int(1)
--- testing: 4294967295 % 9223372036854775807 ---
int(4294967295)
--- testing: 4294967293 % 0 ---
Exception: Modulo by zero
--- testing: 4294967293 % 1 ---
int(0)
--- testing: 4294967293 % -1 ---
int(0)
--- testing: 4294967293 % 7 ---
int(1)
--- testing: 4294967293 % 9 ---
int(1)
--- testing: 4294967293 % 65 ---
int(58)
--- testing: 4294967293 % -44 ---
int(1)
--- testing: 4294967293 % 2147483647 ---
int(2147483646)
--- testing: 4294967293 % 9223372036854775807 ---
int(4294967293)
--- testing: 9223372036854775806 % 0 ---
Exception: Modulo by zero
--- testing: 9223372036854775806 % 1 ---
int(0)
--- testing: 9223372036854775806 % -1 ---
int(0)
--- testing: 9223372036854775806 % 7 ---
int(6)
--- testing: 9223372036854775806 % 9 ---
int(6)
--- testing: 9223372036854775806 % 65 ---
int(6)
--- testing: 9223372036854775806 % -44 ---
int(6)
--- testing: 9223372036854775806 % 2147483647 ---
int(0)
--- testing: 9223372036854775806 % 9223372036854775807 ---
int(9223372036854775806)
--- testing: 9.2233720368548E+18 % 0 ---
Exception: Modulo by zero
--- testing: 9.2233720368548E+18 % 1 ---
int(0)
--- testing: 9.2233720368548E+18 % -1 ---
int(0)
--- testing: 9.2233720368548E+18 % 7 ---
int(-1)
--- testing: 9.2233720368548E+18 % 9 ---
int(-8)
--- testing: 9.2233720368548E+18 % 65 ---
int(-8)
--- testing: 9.2233720368548E+18 % -44 ---
int(-8)
--- testing: 9.2233720368548E+18 % 2147483647 ---
int(-2)
--- testing: 9.2233720368548E+18 % 9223372036854775807 ---
int(-1)
--- testing: -9223372036854775807 % 0 ---
Exception: Modulo by zero
--- testing: -9223372036854775807 % 1 ---
int(0)
--- testing: -9223372036854775807 % -1 ---
int(0)
--- testing: -9223372036854775807 % 7 ---
int(0)
--- testing: -9223372036854775807 % 9 ---
int(-7)
--- testing: -9223372036854775807 % 65 ---
int(-7)
--- testing: -9223372036854775807 % -44 ---
int(-7)
--- testing: -9223372036854775807 % 2147483647 ---
int(-1)
--- testing: -9223372036854775807 % 9223372036854775807 ---
int(0)
--- testing: -9.2233720368548E+18 % 0 ---
Exception: Modulo by zero
--- testing: -9.2233720368548E+18 % 1 ---
int(0)
--- testing: -9.2233720368548E+18 % -1 ---
int(0)
--- testing: -9.2233720368548E+18 % 7 ---
int(-1)
--- testing: -9.2233720368548E+18 % 9 ---
int(-8)
--- testing: -9.2233720368548E+18 % 65 ---
int(-8)
--- testing: -9.2233720368548E+18 % -44 ---
int(-8)
--- testing: -9.2233720368548E+18 % 2147483647 ---
int(-2)
--- testing: -9.2233720368548E+18 % 9223372036854775807 ---
int(-1)
--- testing: 0 % 9223372036854775807 ---
int(0)
--- testing: 0 % -9223372036854775808 ---
int(0)
--- testing: 0 % 2147483647 ---
int(0)
--- testing: 0 % -2147483648 ---
int(0)
--- testing: 0 % 9223372034707292160 ---
int(0)
--- testing: 0 % -9223372034707292160 ---
int(0)
--- testing: 0 % 2147483648 ---
int(0)
--- testing: 0 % -2147483649 ---
int(0)
--- testing: 0 % 4294967294 ---
int(0)
--- testing: 0 % 4294967295 ---
int(0)
--- testing: 0 % 4294967293 ---
int(0)
--- testing: 0 % 9223372036854775806 ---
int(0)
--- testing: 0 % 9.2233720368548E+18 ---
int(0)
--- testing: 0 % -9223372036854775807 ---
int(0)
--- testing: 0 % -9.2233720368548E+18 ---
int(0)
--- testing: 1 % 9223372036854775807 ---
int(1)
--- testing: 1 % -9223372036854775808 ---
int(1)
--- testing: 1 % 2147483647 ---
int(1)
--- testing: 1 % -2147483648 ---
int(1)
--- testing: 1 % 9223372034707292160 ---
int(1)
--- testing: 1 % -9223372034707292160 ---
int(1)
--- testing: 1 % 2147483648 ---
int(1)
--- testing: 1 % -2147483649 ---
int(1)
--- testing: 1 % 4294967294 ---
int(1)
--- testing: 1 % 4294967295 ---
int(1)
--- testing: 1 % 4294967293 ---
int(1)
--- testing: 1 % 9223372036854775806 ---
int(1)
--- testing: 1 % 9.2233720368548E+18 ---
int(1)
--- testing: 1 % -9223372036854775807 ---
int(1)
--- testing: 1 % -9.2233720368548E+18 ---
int(1)
--- testing: -1 % 9223372036854775807 ---
int(-1)
--- testing: -1 % -9223372036854775808 ---
int(-1)
--- testing: -1 % 2147483647 ---
int(-1)
--- testing: -1 % -2147483648 ---
int(-1)
--- testing: -1 % 9223372034707292160 ---
int(-1)
--- testing: -1 % -9223372034707292160 ---
int(-1)
--- testing: -1 % 2147483648 ---
int(-1)
--- testing: -1 % -2147483649 ---
int(-1)
--- testing: -1 % 4294967294 ---
int(-1)
--- testing: -1 % 4294967295 ---
int(-1)
--- testing: -1 % 4294967293 ---
int(-1)
--- testing: -1 % 9223372036854775806 ---
int(-1)
--- testing: -1 % 9.2233720368548E+18 ---
int(-1)
--- testing: -1 % -9223372036854775807 ---
int(-1)
--- testing: -1 % -9.2233720368548E+18 ---
int(-1)
--- testing: 7 % 9223372036854775807 ---
int(7)
--- testing: 7 % -9223372036854775808 ---
int(7)
--- testing: 7 % 2147483647 ---
int(7)
--- testing: 7 % -2147483648 ---
int(7)
--- testing: 7 % 9223372034707292160 ---
int(7)
--- testing: 7 % -9223372034707292160 ---
int(7)
--- testing: 7 % 2147483648 ---
int(7)
--- testing: 7 % -2147483649 ---
int(7)
--- testing: 7 % 4294967294 ---
int(7)
--- testing: 7 % 4294967295 ---
int(7)
--- testing: 7 % 4294967293 ---
int(7)
--- testing: 7 % 9223372036854775806 ---
int(7)
--- testing: 7 % 9.2233720368548E+18 ---
int(7)
--- testing: 7 % -9223372036854775807 ---
int(7)
--- testing: 7 % -9.2233720368548E+18 ---
int(7)
--- testing: 9 % 9223372036854775807 ---
int(9)
--- testing: 9 % -9223372036854775808 ---
int(9)
--- testing: 9 % 2147483647 ---
int(9)
--- testing: 9 % -2147483648 ---
int(9)
--- testing: 9 % 9223372034707292160 ---
int(9)
--- testing: 9 % -9223372034707292160 ---
int(9)
--- testing: 9 % 2147483648 ---
int(9)
--- testing: 9 % -2147483649 ---
int(9)
--- testing: 9 % 4294967294 ---
int(9)
--- testing: 9 % 4294967295 ---
int(9)
--- testing: 9 % 4294967293 ---
int(9)
--- testing: 9 % 9223372036854775806 ---
int(9)
--- testing: 9 % 9.2233720368548E+18 ---
int(9)
--- testing: 9 % -9223372036854775807 ---
int(9)
--- testing: 9 % -9.2233720368548E+18 ---
int(9)
--- testing: 65 % 9223372036854775807 ---
int(65)
--- testing: 65 % -9223372036854775808 ---
int(65)
--- testing: 65 % 2147483647 ---
int(65)
--- testing: 65 % -2147483648 ---
int(65)
--- testing: 65 % 9223372034707292160 ---
int(65)
--- testing: 65 % -9223372034707292160 ---
int(65)
--- testing: 65 % 2147483648 ---
int(65)
--- testing: 65 % -2147483649 ---
int(65)
--- testing: 65 % 4294967294 ---
int(65)
--- testing: 65 % 4294967295 ---
int(65)
--- testing: 65 % 4294967293 ---
int(65)
--- testing: 65 % 9223372036854775806 ---
int(65)
--- testing: 65 % 9.2233720368548E+18 ---
int(65)
--- testing: 65 % -9223372036854775807 ---
int(65)
--- testing: 65 % -9.2233720368548E+18 ---
int(65)
--- testing: -44 % 9223372036854775807 ---
int(-44)
--- testing: -44 % -9223372036854775808 ---
int(-44)
--- testing: -44 % 2147483647 ---
int(-44)
--- testing: -44 % -2147483648 ---
int(-44)
--- testing: -44 % 9223372034707292160 ---
int(-44)
--- testing: -44 % -9223372034707292160 ---
int(-44)
--- testing: -44 % 2147483648 ---
int(-44)
--- testing: -44 % -2147483649 ---
int(-44)
--- testing: -44 % 4294967294 ---
int(-44)
--- testing: -44 % 4294967295 ---
int(-44)
--- testing: -44 % 4294967293 ---
int(-44)
--- testing: -44 % 9223372036854775806 ---
int(-44)
--- testing: -44 % 9.2233720368548E+18 ---
int(-44)
--- testing: -44 % -9223372036854775807 ---
int(-44)
--- testing: -44 % -9.2233720368548E+18 ---
int(-44)
--- testing: 2147483647 % 9223372036854775807 ---
int(2147483647)
--- testing: 2147483647 % -9223372036854775808 ---
int(2147483647)
--- testing: 2147483647 % 2147483647 ---
int(0)
--- testing: 2147483647 % -2147483648 ---
int(2147483647)
--- testing: 2147483647 % 9223372034707292160 ---
int(2147483647)
--- testing: 2147483647 % -9223372034707292160 ---
int(2147483647)
--- testing: 2147483647 % 2147483648 ---
int(2147483647)
--- testing: 2147483647 % -2147483649 ---
int(2147483647)
--- testing: 2147483647 % 4294967294 ---
int(2147483647)
--- testing: 2147483647 % 4294967295 ---
int(2147483647)
--- testing: 2147483647 % 4294967293 ---
int(2147483647)
--- testing: 2147483647 % 9223372036854775806 ---
int(2147483647)
--- testing: 2147483647 % 9.2233720368548E+18 ---
int(2147483647)
--- testing: 2147483647 % -9223372036854775807 ---
int(2147483647)
--- testing: 2147483647 % -9.2233720368548E+18 ---
int(2147483647)
--- testing: 9223372036854775807 % 9223372036854775807 ---
int(0)
--- testing: 9223372036854775807 % -9223372036854775808 ---
int(9223372036854775807)
--- testing: 9223372036854775807 % 2147483647 ---
int(1)
--- testing: 9223372036854775807 % -2147483648 ---
int(2147483647)
--- testing: 9223372036854775807 % 9223372034707292160 ---
int(2147483647)
--- testing: 9223372036854775807 % -9223372034707292160 ---
int(2147483647)
--- testing: 9223372036854775807 % 2147483648 ---
int(2147483647)
--- testing: 9223372036854775807 % -2147483649 ---
int(1)
--- testing: 9223372036854775807 % 4294967294 ---
int(1)
--- testing: 9223372036854775807 % 4294967295 ---
int(2147483647)
--- testing: 9223372036854775807 % 4294967293 ---
int(2147483650)
--- testing: 9223372036854775807 % 9223372036854775806 ---
int(1)
--- testing: 9223372036854775807 % 9.2233720368548E+18 ---
int(9223372036854775807)
--- testing: 9223372036854775807 % -9223372036854775807 ---
int(0)
--- testing: 9223372036854775807 % -9.2233720368548E+18 ---
int(9223372036854775807)
{"x":{}}
{"b":{},"c":{"x":{}}}
{"b":{},"d":{},"c":{"x":{}}}
{"b":{},"c":{"d":{},"x":{}}}
{"b":{},"c":{"d":{"x":{}}}}
