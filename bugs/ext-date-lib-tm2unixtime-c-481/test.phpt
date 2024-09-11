--TEST--
Test >> operator : 64bit long tests+Test finfo_open() function : error functionality
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
      echo "--- testing: $longVal >> $otherVal ---\n";
      try {
        var_dump($longVal>>$otherVal);
      } catch (ArithmeticError $e) {
        echo "Exception: " . $e->getMessage() . "\n";
      }
   }
}
foreach ($otherVals as $otherVal) {
   foreach($longVals as $longVal) {
      echo "--- testing: $otherVal >> $longVal ---\n";
      try {
        var_dump($otherVal>>$longVal);
      } catch (ArithmeticError $e) {
        echo "Exception: " . $e->getMessage() . "\n";
      }
   }
}
$fusion = $longVals;
$v1=$definedVars[array_rand($definedVars = get_defined_vars())];
$fusion = __DIR__ . DIRECTORY_SEPARATOR . 'magic';
echo "*** Testing finfo_open() : error functionality ***\n";
var_dump( finfo_open( FILEINFO_MIME, 'foobarfile' ) );
var_dump( finfo_open( PHP_INT_MAX - 1, $magicFile ) );
try {
    var_dump( finfo_open( 'foobar' ) );
} catch (TypeError $e) {
    echo $e->getMessage(), "\n";
}
try {
    var_dump( new finfo('foobar') );
} catch (TypeError $e) {
    echo $e->getMessage(), "\n";
}
$v2=$definedVars[array_rand($definedVars = get_defined_vars())];
$v3=$definedVars[array_rand($definedVars = get_defined_vars())];
var_dump('random_var:',$v1,$v2,$v3);
var_fusion($v1,$v2,$v3);
?>
--EXTENSIONS--
fileinfo
--EXPECTF--
--- testing: 9223372036854775807 >> 0 ---
int(9223372036854775807)
--- testing: 9223372036854775807 >> 1 ---
int(4611686018427387903)
--- testing: 9223372036854775807 >> -1 ---
Exception: Bit shift by negative number
--- testing: 9223372036854775807 >> 7 ---
int(72057594037927935)
--- testing: 9223372036854775807 >> 9 ---
int(18014398509481983)
--- testing: 9223372036854775807 >> 65 ---
int(0)
--- testing: 9223372036854775807 >> -44 ---
Exception: Bit shift by negative number
--- testing: 9223372036854775807 >> 2147483647 ---
int(0)
--- testing: 9223372036854775807 >> 9223372036854775807 ---
int(0)
--- testing: -9223372036854775808 >> 0 ---
int(-9223372036854775808)
--- testing: -9223372036854775808 >> 1 ---
int(-4611686018427387904)
--- testing: -9223372036854775808 >> -1 ---
Exception: Bit shift by negative number
--- testing: -9223372036854775808 >> 7 ---
int(-72057594037927936)
--- testing: -9223372036854775808 >> 9 ---
int(-18014398509481984)
--- testing: -9223372036854775808 >> 65 ---
int(-1)
--- testing: -9223372036854775808 >> -44 ---
Exception: Bit shift by negative number
--- testing: -9223372036854775808 >> 2147483647 ---
int(-1)
--- testing: -9223372036854775808 >> 9223372036854775807 ---
int(-1)
--- testing: 2147483647 >> 0 ---
int(2147483647)
--- testing: 2147483647 >> 1 ---
int(1073741823)
--- testing: 2147483647 >> -1 ---
Exception: Bit shift by negative number
--- testing: 2147483647 >> 7 ---
int(16777215)
--- testing: 2147483647 >> 9 ---
int(4194303)
--- testing: 2147483647 >> 65 ---
int(0)
--- testing: 2147483647 >> -44 ---
Exception: Bit shift by negative number
--- testing: 2147483647 >> 2147483647 ---
int(0)
--- testing: 2147483647 >> 9223372036854775807 ---
int(0)
--- testing: -2147483648 >> 0 ---
int(-2147483648)
--- testing: -2147483648 >> 1 ---
int(-1073741824)
--- testing: -2147483648 >> -1 ---
Exception: Bit shift by negative number
--- testing: -2147483648 >> 7 ---
int(-16777216)
--- testing: -2147483648 >> 9 ---
int(-4194304)
--- testing: -2147483648 >> 65 ---
int(-1)
--- testing: -2147483648 >> -44 ---
Exception: Bit shift by negative number
--- testing: -2147483648 >> 2147483647 ---
int(-1)
--- testing: -2147483648 >> 9223372036854775807 ---
int(-1)
--- testing: 9223372034707292160 >> 0 ---
int(9223372034707292160)
--- testing: 9223372034707292160 >> 1 ---
int(4611686017353646080)
--- testing: 9223372034707292160 >> -1 ---
Exception: Bit shift by negative number
--- testing: 9223372034707292160 >> 7 ---
int(72057594021150720)
--- testing: 9223372034707292160 >> 9 ---
int(18014398505287680)
--- testing: 9223372034707292160 >> 65 ---
int(0)
--- testing: 9223372034707292160 >> -44 ---
Exception: Bit shift by negative number
--- testing: 9223372034707292160 >> 2147483647 ---
int(0)
--- testing: 9223372034707292160 >> 9223372036854775807 ---
int(0)
--- testing: -9223372034707292160 >> 0 ---
int(-9223372034707292160)
--- testing: -9223372034707292160 >> 1 ---
int(-4611686017353646080)
--- testing: -9223372034707292160 >> -1 ---
Exception: Bit shift by negative number
--- testing: -9223372034707292160 >> 7 ---
int(-72057594021150720)
--- testing: -9223372034707292160 >> 9 ---
int(-18014398505287680)
--- testing: -9223372034707292160 >> 65 ---
int(-1)
--- testing: -9223372034707292160 >> -44 ---
Exception: Bit shift by negative number
--- testing: -9223372034707292160 >> 2147483647 ---
int(-1)
--- testing: -9223372034707292160 >> 9223372036854775807 ---
int(-1)
--- testing: 2147483648 >> 0 ---
int(2147483648)
--- testing: 2147483648 >> 1 ---
int(1073741824)
--- testing: 2147483648 >> -1 ---
Exception: Bit shift by negative number
--- testing: 2147483648 >> 7 ---
int(16777216)
--- testing: 2147483648 >> 9 ---
int(4194304)
--- testing: 2147483648 >> 65 ---
int(0)
--- testing: 2147483648 >> -44 ---
Exception: Bit shift by negative number
--- testing: 2147483648 >> 2147483647 ---
int(0)
--- testing: 2147483648 >> 9223372036854775807 ---
int(0)
--- testing: -2147483649 >> 0 ---
int(-2147483649)
--- testing: -2147483649 >> 1 ---
int(-1073741825)
--- testing: -2147483649 >> -1 ---
Exception: Bit shift by negative number
--- testing: -2147483649 >> 7 ---
int(-16777217)
--- testing: -2147483649 >> 9 ---
int(-4194305)
--- testing: -2147483649 >> 65 ---
int(-1)
--- testing: -2147483649 >> -44 ---
Exception: Bit shift by negative number
--- testing: -2147483649 >> 2147483647 ---
int(-1)
--- testing: -2147483649 >> 9223372036854775807 ---
int(-1)
--- testing: 4294967294 >> 0 ---
int(4294967294)
--- testing: 4294967294 >> 1 ---
int(2147483647)
--- testing: 4294967294 >> -1 ---
Exception: Bit shift by negative number
--- testing: 4294967294 >> 7 ---
int(33554431)
--- testing: 4294967294 >> 9 ---
int(8388607)
--- testing: 4294967294 >> 65 ---
int(0)
--- testing: 4294967294 >> -44 ---
Exception: Bit shift by negative number
--- testing: 4294967294 >> 2147483647 ---
int(0)
--- testing: 4294967294 >> 9223372036854775807 ---
int(0)
--- testing: 4294967295 >> 0 ---
int(4294967295)
--- testing: 4294967295 >> 1 ---
int(2147483647)
--- testing: 4294967295 >> -1 ---
Exception: Bit shift by negative number
--- testing: 4294967295 >> 7 ---
int(33554431)
--- testing: 4294967295 >> 9 ---
int(8388607)
--- testing: 4294967295 >> 65 ---
int(0)
--- testing: 4294967295 >> -44 ---
Exception: Bit shift by negative number
--- testing: 4294967295 >> 2147483647 ---
int(0)
--- testing: 4294967295 >> 9223372036854775807 ---
int(0)
--- testing: 4294967293 >> 0 ---
int(4294967293)
--- testing: 4294967293 >> 1 ---
int(2147483646)
--- testing: 4294967293 >> -1 ---
Exception: Bit shift by negative number
--- testing: 4294967293 >> 7 ---
int(33554431)
--- testing: 4294967293 >> 9 ---
int(8388607)
--- testing: 4294967293 >> 65 ---
int(0)
--- testing: 4294967293 >> -44 ---
Exception: Bit shift by negative number
--- testing: 4294967293 >> 2147483647 ---
int(0)
--- testing: 4294967293 >> 9223372036854775807 ---
int(0)
--- testing: 9223372036854775806 >> 0 ---
int(9223372036854775806)
--- testing: 9223372036854775806 >> 1 ---
int(4611686018427387903)
--- testing: 9223372036854775806 >> -1 ---
Exception: Bit shift by negative number
--- testing: 9223372036854775806 >> 7 ---
int(72057594037927935)
--- testing: 9223372036854775806 >> 9 ---
int(18014398509481983)
--- testing: 9223372036854775806 >> 65 ---
int(0)
--- testing: 9223372036854775806 >> -44 ---
Exception: Bit shift by negative number
--- testing: 9223372036854775806 >> 2147483647 ---
int(0)
--- testing: 9223372036854775806 >> 9223372036854775807 ---
int(0)
--- testing: 9.2233720368548E+18 >> 0 ---
int(-9223372036854775808)
--- testing: 9.2233720368548E+18 >> 1 ---
int(-4611686018427387904)
--- testing: 9.2233720368548E+18 >> -1 ---
Exception: Bit shift by negative number
--- testing: 9.2233720368548E+18 >> 7 ---
int(-72057594037927936)
--- testing: 9.2233720368548E+18 >> 9 ---
int(-18014398509481984)
--- testing: 9.2233720368548E+18 >> 65 ---
int(-1)
--- testing: 9.2233720368548E+18 >> -44 ---
Exception: Bit shift by negative number
--- testing: 9.2233720368548E+18 >> 2147483647 ---
int(-1)
--- testing: 9.2233720368548E+18 >> 9223372036854775807 ---
int(-1)
--- testing: -9223372036854775807 >> 0 ---
int(-9223372036854775807)
--- testing: -9223372036854775807 >> 1 ---
int(-4611686018427387904)
--- testing: -9223372036854775807 >> -1 ---
Exception: Bit shift by negative number
--- testing: -9223372036854775807 >> 7 ---
int(-72057594037927936)
--- testing: -9223372036854775807 >> 9 ---
int(-18014398509481984)
--- testing: -9223372036854775807 >> 65 ---
int(-1)
--- testing: -9223372036854775807 >> -44 ---
Exception: Bit shift by negative number
--- testing: -9223372036854775807 >> 2147483647 ---
int(-1)
--- testing: -9223372036854775807 >> 9223372036854775807 ---
int(-1)
--- testing: -9.2233720368548E+18 >> 0 ---
int(-9223372036854775808)
--- testing: -9.2233720368548E+18 >> 1 ---
int(-4611686018427387904)
--- testing: -9.2233720368548E+18 >> -1 ---
Exception: Bit shift by negative number
--- testing: -9.2233720368548E+18 >> 7 ---
int(-72057594037927936)
--- testing: -9.2233720368548E+18 >> 9 ---
int(-18014398509481984)
--- testing: -9.2233720368548E+18 >> 65 ---
int(-1)
--- testing: -9.2233720368548E+18 >> -44 ---
Exception: Bit shift by negative number
--- testing: -9.2233720368548E+18 >> 2147483647 ---
int(-1)
--- testing: -9.2233720368548E+18 >> 9223372036854775807 ---
int(-1)
--- testing: 0 >> 9223372036854775807 ---
int(0)
--- testing: 0 >> -9223372036854775808 ---
Exception: Bit shift by negative number
--- testing: 0 >> 2147483647 ---
int(0)
--- testing: 0 >> -2147483648 ---
Exception: Bit shift by negative number
--- testing: 0 >> 9223372034707292160 ---
int(0)
--- testing: 0 >> -9223372034707292160 ---
Exception: Bit shift by negative number
--- testing: 0 >> 2147483648 ---
int(0)
--- testing: 0 >> -2147483649 ---
Exception: Bit shift by negative number
--- testing: 0 >> 4294967294 ---
int(0)
--- testing: 0 >> 4294967295 ---
int(0)
--- testing: 0 >> 4294967293 ---
int(0)
--- testing: 0 >> 9223372036854775806 ---
int(0)
--- testing: 0 >> 9.2233720368548E+18 ---
Exception: Bit shift by negative number
--- testing: 0 >> -9223372036854775807 ---
Exception: Bit shift by negative number
--- testing: 0 >> -9.2233720368548E+18 ---
Exception: Bit shift by negative number
--- testing: 1 >> 9223372036854775807 ---
int(0)
--- testing: 1 >> -9223372036854775808 ---
Exception: Bit shift by negative number
--- testing: 1 >> 2147483647 ---
int(0)
--- testing: 1 >> -2147483648 ---
Exception: Bit shift by negative number
--- testing: 1 >> 9223372034707292160 ---
int(0)
--- testing: 1 >> -9223372034707292160 ---
Exception: Bit shift by negative number
--- testing: 1 >> 2147483648 ---
int(0)
--- testing: 1 >> -2147483649 ---
Exception: Bit shift by negative number
--- testing: 1 >> 4294967294 ---
int(0)
--- testing: 1 >> 4294967295 ---
int(0)
--- testing: 1 >> 4294967293 ---
int(0)
--- testing: 1 >> 9223372036854775806 ---
int(0)
--- testing: 1 >> 9.2233720368548E+18 ---
Exception: Bit shift by negative number
--- testing: 1 >> -9223372036854775807 ---
Exception: Bit shift by negative number
--- testing: 1 >> -9.2233720368548E+18 ---
Exception: Bit shift by negative number
--- testing: -1 >> 9223372036854775807 ---
int(-1)
--- testing: -1 >> -9223372036854775808 ---
Exception: Bit shift by negative number
--- testing: -1 >> 2147483647 ---
int(-1)
--- testing: -1 >> -2147483648 ---
Exception: Bit shift by negative number
--- testing: -1 >> 9223372034707292160 ---
int(-1)
--- testing: -1 >> -9223372034707292160 ---
Exception: Bit shift by negative number
--- testing: -1 >> 2147483648 ---
int(-1)
--- testing: -1 >> -2147483649 ---
Exception: Bit shift by negative number
--- testing: -1 >> 4294967294 ---
int(-1)
--- testing: -1 >> 4294967295 ---
int(-1)
--- testing: -1 >> 4294967293 ---
int(-1)
--- testing: -1 >> 9223372036854775806 ---
int(-1)
--- testing: -1 >> 9.2233720368548E+18 ---
Exception: Bit shift by negative number
--- testing: -1 >> -9223372036854775807 ---
Exception: Bit shift by negative number
--- testing: -1 >> -9.2233720368548E+18 ---
Exception: Bit shift by negative number
--- testing: 7 >> 9223372036854775807 ---
int(0)
--- testing: 7 >> -9223372036854775808 ---
Exception: Bit shift by negative number
--- testing: 7 >> 2147483647 ---
int(0)
--- testing: 7 >> -2147483648 ---
Exception: Bit shift by negative number
--- testing: 7 >> 9223372034707292160 ---
int(0)
--- testing: 7 >> -9223372034707292160 ---
Exception: Bit shift by negative number
--- testing: 7 >> 2147483648 ---
int(0)
--- testing: 7 >> -2147483649 ---
Exception: Bit shift by negative number
--- testing: 7 >> 4294967294 ---
int(0)
--- testing: 7 >> 4294967295 ---
int(0)
--- testing: 7 >> 4294967293 ---
int(0)
--- testing: 7 >> 9223372036854775806 ---
int(0)
--- testing: 7 >> 9.2233720368548E+18 ---
Exception: Bit shift by negative number
--- testing: 7 >> -9223372036854775807 ---
Exception: Bit shift by negative number
--- testing: 7 >> -9.2233720368548E+18 ---
Exception: Bit shift by negative number
--- testing: 9 >> 9223372036854775807 ---
int(0)
--- testing: 9 >> -9223372036854775808 ---
Exception: Bit shift by negative number
--- testing: 9 >> 2147483647 ---
int(0)
--- testing: 9 >> -2147483648 ---
Exception: Bit shift by negative number
--- testing: 9 >> 9223372034707292160 ---
int(0)
--- testing: 9 >> -9223372034707292160 ---
Exception: Bit shift by negative number
--- testing: 9 >> 2147483648 ---
int(0)
--- testing: 9 >> -2147483649 ---
Exception: Bit shift by negative number
--- testing: 9 >> 4294967294 ---
int(0)
--- testing: 9 >> 4294967295 ---
int(0)
--- testing: 9 >> 4294967293 ---
int(0)
--- testing: 9 >> 9223372036854775806 ---
int(0)
--- testing: 9 >> 9.2233720368548E+18 ---
Exception: Bit shift by negative number
--- testing: 9 >> -9223372036854775807 ---
Exception: Bit shift by negative number
--- testing: 9 >> -9.2233720368548E+18 ---
Exception: Bit shift by negative number
--- testing: 65 >> 9223372036854775807 ---
int(0)
--- testing: 65 >> -9223372036854775808 ---
Exception: Bit shift by negative number
--- testing: 65 >> 2147483647 ---
int(0)
--- testing: 65 >> -2147483648 ---
Exception: Bit shift by negative number
--- testing: 65 >> 9223372034707292160 ---
int(0)
--- testing: 65 >> -9223372034707292160 ---
Exception: Bit shift by negative number
--- testing: 65 >> 2147483648 ---
int(0)
--- testing: 65 >> -2147483649 ---
Exception: Bit shift by negative number
--- testing: 65 >> 4294967294 ---
int(0)
--- testing: 65 >> 4294967295 ---
int(0)
--- testing: 65 >> 4294967293 ---
int(0)
--- testing: 65 >> 9223372036854775806 ---
int(0)
--- testing: 65 >> 9.2233720368548E+18 ---
Exception: Bit shift by negative number
--- testing: 65 >> -9223372036854775807 ---
Exception: Bit shift by negative number
--- testing: 65 >> -9.2233720368548E+18 ---
Exception: Bit shift by negative number
--- testing: -44 >> 9223372036854775807 ---
int(-1)
--- testing: -44 >> -9223372036854775808 ---
Exception: Bit shift by negative number
--- testing: -44 >> 2147483647 ---
int(-1)
--- testing: -44 >> -2147483648 ---
Exception: Bit shift by negative number
--- testing: -44 >> 9223372034707292160 ---
int(-1)
--- testing: -44 >> -9223372034707292160 ---
Exception: Bit shift by negative number
--- testing: -44 >> 2147483648 ---
int(-1)
--- testing: -44 >> -2147483649 ---
Exception: Bit shift by negative number
--- testing: -44 >> 4294967294 ---
int(-1)
--- testing: -44 >> 4294967295 ---
int(-1)
--- testing: -44 >> 4294967293 ---
int(-1)
--- testing: -44 >> 9223372036854775806 ---
int(-1)
--- testing: -44 >> 9.2233720368548E+18 ---
Exception: Bit shift by negative number
--- testing: -44 >> -9223372036854775807 ---
Exception: Bit shift by negative number
--- testing: -44 >> -9.2233720368548E+18 ---
Exception: Bit shift by negative number
--- testing: 2147483647 >> 9223372036854775807 ---
int(0)
--- testing: 2147483647 >> -9223372036854775808 ---
Exception: Bit shift by negative number
--- testing: 2147483647 >> 2147483647 ---
int(0)
--- testing: 2147483647 >> -2147483648 ---
Exception: Bit shift by negative number
--- testing: 2147483647 >> 9223372034707292160 ---
int(0)
--- testing: 2147483647 >> -9223372034707292160 ---
Exception: Bit shift by negative number
--- testing: 2147483647 >> 2147483648 ---
int(0)
--- testing: 2147483647 >> -2147483649 ---
Exception: Bit shift by negative number
--- testing: 2147483647 >> 4294967294 ---
int(0)
--- testing: 2147483647 >> 4294967295 ---
int(0)
--- testing: 2147483647 >> 4294967293 ---
int(0)
--- testing: 2147483647 >> 9223372036854775806 ---
int(0)
--- testing: 2147483647 >> 9.2233720368548E+18 ---
Exception: Bit shift by negative number
--- testing: 2147483647 >> -9223372036854775807 ---
Exception: Bit shift by negative number
--- testing: 2147483647 >> -9.2233720368548E+18 ---
Exception: Bit shift by negative number
--- testing: 9223372036854775807 >> 9223372036854775807 ---
int(0)
--- testing: 9223372036854775807 >> -9223372036854775808 ---
Exception: Bit shift by negative number
--- testing: 9223372036854775807 >> 2147483647 ---
int(0)
--- testing: 9223372036854775807 >> -2147483648 ---
Exception: Bit shift by negative number
--- testing: 9223372036854775807 >> 9223372034707292160 ---
int(0)
--- testing: 9223372036854775807 >> -9223372034707292160 ---
Exception: Bit shift by negative number
--- testing: 9223372036854775807 >> 2147483648 ---
int(0)
--- testing: 9223372036854775807 >> -2147483649 ---
Exception: Bit shift by negative number
--- testing: 9223372036854775807 >> 4294967294 ---
int(0)
--- testing: 9223372036854775807 >> 4294967295 ---
int(0)
--- testing: 9223372036854775807 >> 4294967293 ---
int(0)
--- testing: 9223372036854775807 >> 9223372036854775806 ---
int(0)
--- testing: 9223372036854775807 >> 9.2233720368548E+18 ---
Exception: Bit shift by negative number
--- testing: 9223372036854775807 >> -9223372036854775807 ---
Exception: Bit shift by negative number
--- testing: 9223372036854775807 >> -9.2233720368548E+18 ---
Exception: Bit shift by negative number
*** Testing finfo_open() : error functionality ***

Warning: finfo_open(%sfoobarfile): Failed to open stream: No such file or directory in %sfinfo_open_error.php on line %d

Warning: finfo_open(%sfoobarfile): Failed to open stream: No such file or directory in %sfinfo_open_error.php on line %d

Warning: finfo_open(): Failed to load magic database at "%sfoobarfile" in %sfinfo_open_error.php on line %d
bool(false)

Warning: finfo_open(): using regular magic file `%smagic' in %sfinfo_open_error.php on line %d
object(finfo)#%d (0) {
}
finfo_open(): Argument #1 ($flags) must be of type int, string given
finfo::__construct(): Argument #1 ($flags) must be of type int, string given
