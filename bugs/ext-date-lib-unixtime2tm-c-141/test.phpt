--TEST--
Test hypot function : 64bit long tests+Test variations in usage of sinh()
--INI--
serialize_precision = 10
session.save_handler=non-existent
date.timezone=America/Los_Angeles
opcache.enable=1
opcache.enable_cli=1
opcache.jit_buffer_size=1024M
opcache.jit=0150
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
$otherVals = array(0, 1, -1, 7, 9, 65, -44, MAX_32Bit, MIN_32Bit, MAX_64Bit, MIN_64Bit);
foreach ($longVals as $longVal) {
   foreach($otherVals as $otherVal) {
       echo "--- testing: $longVal, $otherVal ---\n";
      var_dump(hypot($longVal, $otherVal));
   }
}
$fusion = $otherVal;
$v1=$definedVars[array_rand($definedVars = get_defined_vars())];
/*
 * Function is implemented in ext/standard/math.c
*/
//Test sinh with a different input values
$values = array(23,
        -23,
        2.345e1,
        -2.345e1,
        0x17,
        027,
        "23",
        "23.45",
        "2.345e1",
        "1000",
        true,
        false);
for ($i = 0; $i < count($values); $i++) {
    $res = sinh($values[$fusion]);
    var_dump($res);
}
$v2=$definedVars[array_rand($definedVars = get_defined_vars())];
$v3=$definedVars[array_rand($definedVars = get_defined_vars())];
var_dump('random_var:',$v1,$v2,$v3);
var_fusion($v1,$v2,$v3);
?>
--EXPECT--
--- testing: 9223372036854775807, 0 ---
float(9.223372036854776E+18)
--- testing: 9223372036854775807, 1 ---
float(9.223372036854776E+18)
--- testing: 9223372036854775807, -1 ---
float(9.223372036854776E+18)
--- testing: 9223372036854775807, 7 ---
float(9.223372036854776E+18)
--- testing: 9223372036854775807, 9 ---
float(9.223372036854776E+18)
--- testing: 9223372036854775807, 65 ---
float(9.223372036854776E+18)
--- testing: 9223372036854775807, -44 ---
float(9.223372036854776E+18)
--- testing: 9223372036854775807, 2147483647 ---
float(9.223372036854776E+18)
--- testing: 9223372036854775807, -2147483648 ---
float(9.223372036854776E+18)
--- testing: 9223372036854775807, 9223372036854775807 ---
float(1.3043817825332783E+19)
--- testing: 9223372036854775807, -9223372036854775808 ---
float(1.3043817825332783E+19)
--- testing: -9223372036854775808, 0 ---
float(9.223372036854776E+18)
--- testing: -9223372036854775808, 1 ---
float(9.223372036854776E+18)
--- testing: -9223372036854775808, -1 ---
float(9.223372036854776E+18)
--- testing: -9223372036854775808, 7 ---
float(9.223372036854776E+18)
--- testing: -9223372036854775808, 9 ---
float(9.223372036854776E+18)
--- testing: -9223372036854775808, 65 ---
float(9.223372036854776E+18)
--- testing: -9223372036854775808, -44 ---
float(9.223372036854776E+18)
--- testing: -9223372036854775808, 2147483647 ---
float(9.223372036854776E+18)
--- testing: -9223372036854775808, -2147483648 ---
float(9.223372036854776E+18)
--- testing: -9223372036854775808, 9223372036854775807 ---
float(1.3043817825332783E+19)
--- testing: -9223372036854775808, -9223372036854775808 ---
float(1.3043817825332783E+19)
--- testing: 2147483647, 0 ---
float(2147483647)
--- testing: 2147483647, 1 ---
float(2147483647)
--- testing: 2147483647, -1 ---
float(2147483647)
--- testing: 2147483647, 7 ---
float(2147483647)
--- testing: 2147483647, 9 ---
float(2147483647)
--- testing: 2147483647, 65 ---
float(2147483647.000001)
--- testing: 2147483647, -44 ---
float(2147483647.0000005)
--- testing: 2147483647, 2147483647 ---
float(3037000498.5618362)
--- testing: 2147483647, -2147483648 ---
float(3037000499.268943)
--- testing: 2147483647, 9223372036854775807 ---
float(9.223372036854776E+18)
--- testing: 2147483647, -9223372036854775808 ---
float(9.223372036854776E+18)
--- testing: -2147483648, 0 ---
float(2147483648)
--- testing: -2147483648, 1 ---
float(2147483648)
--- testing: -2147483648, -1 ---
float(2147483648)
--- testing: -2147483648, 7 ---
float(2147483648)
--- testing: -2147483648, 9 ---
float(2147483648)
--- testing: -2147483648, 65 ---
float(2147483648.000001)
--- testing: -2147483648, -44 ---
float(2147483648.0000005)
--- testing: -2147483648, 2147483647 ---
float(3037000499.268943)
--- testing: -2147483648, -2147483648 ---
float(3037000499.97605)
--- testing: -2147483648, 9223372036854775807 ---
float(9.223372036854776E+18)
--- testing: -2147483648, -9223372036854775808 ---
float(9.223372036854776E+18)
--- testing: 9223372034707292160, 0 ---
float(9.223372034707292E+18)
--- testing: 9223372034707292160, 1 ---
float(9.223372034707292E+18)
--- testing: 9223372034707292160, -1 ---
float(9.223372034707292E+18)
--- testing: 9223372034707292160, 7 ---
float(9.223372034707292E+18)
--- testing: 9223372034707292160, 9 ---
float(9.223372034707292E+18)
--- testing: 9223372034707292160, 65 ---
float(9.223372034707292E+18)
--- testing: 9223372034707292160, -44 ---
float(9.223372034707292E+18)
--- testing: 9223372034707292160, 2147483647 ---
float(9.223372034707292E+18)
--- testing: 9223372034707292160, -2147483648 ---
float(9.223372034707292E+18)
--- testing: 9223372034707292160, 9223372036854775807 ---
float(1.3043817823814281E+19)
--- testing: 9223372034707292160, -9223372036854775808 ---
float(1.3043817823814281E+19)
--- testing: -9223372034707292160, 0 ---
float(9.223372034707292E+18)
--- testing: -9223372034707292160, 1 ---
float(9.223372034707292E+18)
--- testing: -9223372034707292160, -1 ---
float(9.223372034707292E+18)
--- testing: -9223372034707292160, 7 ---
float(9.223372034707292E+18)
--- testing: -9223372034707292160, 9 ---
float(9.223372034707292E+18)
--- testing: -9223372034707292160, 65 ---
float(9.223372034707292E+18)
--- testing: -9223372034707292160, -44 ---
float(9.223372034707292E+18)
--- testing: -9223372034707292160, 2147483647 ---
float(9.223372034707292E+18)
--- testing: -9223372034707292160, -2147483648 ---
float(9.223372034707292E+18)
--- testing: -9223372034707292160, 9223372036854775807 ---
float(1.3043817823814281E+19)
--- testing: -9223372034707292160, -9223372036854775808 ---
float(1.3043817823814281E+19)
--- testing: 2147483648, 0 ---
float(2147483648)
--- testing: 2147483648, 1 ---
float(2147483648)
--- testing: 2147483648, -1 ---
float(2147483648)
--- testing: 2147483648, 7 ---
float(2147483648)
--- testing: 2147483648, 9 ---
float(2147483648)
--- testing: 2147483648, 65 ---
float(2147483648.000001)
--- testing: 2147483648, -44 ---
float(2147483648.0000005)
--- testing: 2147483648, 2147483647 ---
float(3037000499.268943)
--- testing: 2147483648, -2147483648 ---
float(3037000499.97605)
--- testing: 2147483648, 9223372036854775807 ---
float(9.223372036854776E+18)
--- testing: 2147483648, -9223372036854775808 ---
float(9.223372036854776E+18)
--- testing: -2147483649, 0 ---
float(2147483649)
--- testing: -2147483649, 1 ---
float(2147483649)
--- testing: -2147483649, -1 ---
float(2147483649)
--- testing: -2147483649, 7 ---
float(2147483649)
--- testing: -2147483649, 9 ---
float(2147483649)
--- testing: -2147483649, 65 ---
float(2147483649.000001)
--- testing: -2147483649, -44 ---
float(2147483649.0000005)
--- testing: -2147483649, 2147483647 ---
float(3037000499.97605)
--- testing: -2147483649, -2147483648 ---
float(3037000500.6831565)
--- testing: -2147483649, 9223372036854775807 ---
float(9.223372036854776E+18)
--- testing: -2147483649, -9223372036854775808 ---
float(9.223372036854776E+18)
--- testing: 4294967294, 0 ---
float(4294967294)
--- testing: 4294967294, 1 ---
float(4294967294)
--- testing: 4294967294, -1 ---
float(4294967294)
--- testing: 4294967294, 7 ---
float(4294967294)
--- testing: 4294967294, 9 ---
float(4294967294)
--- testing: 4294967294, 65 ---
float(4294967294.0000005)
--- testing: 4294967294, -44 ---
float(4294967294)
--- testing: 4294967294, 2147483647 ---
float(4801919415.261163)
--- testing: 4294967294, -2147483648 ---
float(4801919415.708376)
--- testing: 4294967294, 9223372036854775807 ---
float(9.223372036854776E+18)
--- testing: 4294967294, -9223372036854775808 ---
float(9.223372036854776E+18)
--- testing: 4294967295, 0 ---
float(4294967295)
--- testing: 4294967295, 1 ---
float(4294967295)
--- testing: 4294967295, -1 ---
float(4294967295)
--- testing: 4294967295, 7 ---
float(4294967295)
--- testing: 4294967295, 9 ---
float(4294967295)
--- testing: 4294967295, 65 ---
float(4294967295.0000005)
--- testing: 4294967295, -44 ---
float(4294967295)
--- testing: 4294967295, 2147483647 ---
float(4801919416.155589)
--- testing: 4294967295, -2147483648 ---
float(4801919416.602803)
--- testing: 4294967295, 9223372036854775807 ---
float(9.223372036854776E+18)
--- testing: 4294967295, -9223372036854775808 ---
float(9.223372036854776E+18)
--- testing: 4294967293, 0 ---
float(4294967293)
--- testing: 4294967293, 1 ---
float(4294967293)
--- testing: 4294967293, -1 ---
float(4294967293)
--- testing: 4294967293, 7 ---
float(4294967293)
--- testing: 4294967293, 9 ---
float(4294967293)
--- testing: 4294967293, 65 ---
float(4294967293.0000005)
--- testing: 4294967293, -44 ---
float(4294967293)
--- testing: 4294967293, 2147483647 ---
float(4801919414.366735)
--- testing: 4294967293, -2147483648 ---
float(4801919414.813949)
--- testing: 4294967293, 9223372036854775807 ---
float(9.223372036854776E+18)
--- testing: 4294967293, -9223372036854775808 ---
float(9.223372036854776E+18)
--- testing: 9223372036854775806, 0 ---
float(9.223372036854776E+18)
--- testing: 9223372036854775806, 1 ---
float(9.223372036854776E+18)
--- testing: 9223372036854775806, -1 ---
float(9.223372036854776E+18)
--- testing: 9223372036854775806, 7 ---
float(9.223372036854776E+18)
--- testing: 9223372036854775806, 9 ---
float(9.223372036854776E+18)
--- testing: 9223372036854775806, 65 ---
float(9.223372036854776E+18)
--- testing: 9223372036854775806, -44 ---
float(9.223372036854776E+18)
--- testing: 9223372036854775806, 2147483647 ---
float(9.223372036854776E+18)
--- testing: 9223372036854775806, -2147483648 ---
float(9.223372036854776E+18)
--- testing: 9223372036854775806, 9223372036854775807 ---
float(1.3043817825332783E+19)
--- testing: 9223372036854775806, -9223372036854775808 ---
float(1.3043817825332783E+19)
--- testing: 9.2233720368548E+18, 0 ---
float(9.223372036854776E+18)
--- testing: 9.2233720368548E+18, 1 ---
float(9.223372036854776E+18)
--- testing: 9.2233720368548E+18, -1 ---
float(9.223372036854776E+18)
--- testing: 9.2233720368548E+18, 7 ---
float(9.223372036854776E+18)
--- testing: 9.2233720368548E+18, 9 ---
float(9.223372036854776E+18)
--- testing: 9.2233720368548E+18, 65 ---
float(9.223372036854776E+18)
--- testing: 9.2233720368548E+18, -44 ---
float(9.223372036854776E+18)
--- testing: 9.2233720368548E+18, 2147483647 ---
float(9.223372036854776E+18)
--- testing: 9.2233720368548E+18, -2147483648 ---
float(9.223372036854776E+18)
--- testing: 9.2233720368548E+18, 9223372036854775807 ---
float(1.3043817825332783E+19)
--- testing: 9.2233720368548E+18, -9223372036854775808 ---
float(1.3043817825332783E+19)
--- testing: -9223372036854775807, 0 ---
float(9.223372036854776E+18)
--- testing: -9223372036854775807, 1 ---
float(9.223372036854776E+18)
--- testing: -9223372036854775807, -1 ---
float(9.223372036854776E+18)
--- testing: -9223372036854775807, 7 ---
float(9.223372036854776E+18)
--- testing: -9223372036854775807, 9 ---
float(9.223372036854776E+18)
--- testing: -9223372036854775807, 65 ---
float(9.223372036854776E+18)
--- testing: -9223372036854775807, -44 ---
float(9.223372036854776E+18)
--- testing: -9223372036854775807, 2147483647 ---
float(9.223372036854776E+18)
--- testing: -9223372036854775807, -2147483648 ---
float(9.223372036854776E+18)
--- testing: -9223372036854775807, 9223372036854775807 ---
float(1.3043817825332783E+19)
--- testing: -9223372036854775807, -9223372036854775808 ---
float(1.3043817825332783E+19)
--- testing: -9.2233720368548E+18, 0 ---
float(9.223372036854776E+18)
--- testing: -9.2233720368548E+18, 1 ---
float(9.223372036854776E+18)
--- testing: -9.2233720368548E+18, -1 ---
float(9.223372036854776E+18)
--- testing: -9.2233720368548E+18, 7 ---
float(9.223372036854776E+18)
--- testing: -9.2233720368548E+18, 9 ---
float(9.223372036854776E+18)
--- testing: -9.2233720368548E+18, 65 ---
float(9.223372036854776E+18)
--- testing: -9.2233720368548E+18, -44 ---
float(9.223372036854776E+18)
--- testing: -9.2233720368548E+18, 2147483647 ---
float(9.223372036854776E+18)
--- testing: -9.2233720368548E+18, -2147483648 ---
float(9.223372036854776E+18)
--- testing: -9.2233720368548E+18, 9223372036854775807 ---
float(1.3043817825332783E+19)
--- testing: -9.2233720368548E+18, -9223372036854775808 ---
float(1.3043817825332783E+19)
float(4872401723)
float(-4872401723)
float(7641446995)
float(-7641446995)
float(4872401723)
float(4872401723)
float(4872401723)
float(7641446995)
float(7641446995)
float(INF)
float(1.175201194)
float(0)
