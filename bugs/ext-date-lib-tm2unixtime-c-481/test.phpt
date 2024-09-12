--TEST--
Test atan function : 64bit long tests+Test count() function : basic functionality
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
   var_dump(atan($longVal));
}
$fusion = $longVal;
$v1=$definedVars[array_rand($definedVars = get_defined_vars())];
/*
 * Test basic functionality of count() using an array as $var argument
 * and different values as $mode argument.
 */
echo "*** Testing count() : basic functionality ***\n";
echo "\n-- One Dimensional Array: --\n";
$array = array('zero', 'one', 'two');
var_dump(count($array));
echo "\n-- Two Dimensional Array: --\n";
$array_multi = array('zero', array(1, 2, 3), 'two');
echo "\$mode = COUNT_NORMAL:    ";
var_dump(count($array_multi, COUNT_NORMAL));
echo "\$mode = 0:               ";
var_dump(count($array_multi, 0));
echo "\$fusion = COUNT_RECURSIVE: ";
var_dump(count($array_multi, COUNT_RECURSIVE));
echo "\$mode = 1:               ";
var_dump(count($array_multi, 1));
echo "Done";
$v2=$definedVars[array_rand($definedVars = get_defined_vars())];
$v3=$definedVars[array_rand($definedVars = get_defined_vars())];;
var_dump('random_var:',$v1,$v2,$v3);
var_fusion($v1,$v2,$v3);
?>
--EXPECT--
--- testing: 9223372036854775807 ---
float(1.5707963267948966)
--- testing: -9223372036854775808 ---
float(-1.5707963267948966)
--- testing: 2147483647 ---
float(1.5707963263292353)
--- testing: -2147483648 ---
float(-1.5707963263292353)
--- testing: 9223372034707292160 ---
float(1.5707963267948966)
--- testing: -9223372034707292160 ---
float(-1.5707963267948966)
--- testing: 2147483648 ---
float(1.5707963263292353)
--- testing: -2147483649 ---
float(-1.5707963263292353)
--- testing: 4294967294 ---
float(1.570796326562066)
--- testing: 4294967295 ---
float(1.570796326562066)
--- testing: 4294967293 ---
float(1.570796326562066)
--- testing: 9223372036854775806 ---
float(1.5707963267948966)
--- testing: 9.2233720368548E+18 ---
float(1.5707963267948966)
--- testing: -9223372036854775807 ---
float(-1.5707963267948966)
--- testing: -9.2233720368548E+18 ---
float(-1.5707963267948966)
*** Testing count() : basic functionality ***

-- One Dimensional Array: --
int(3)

-- Two Dimensional Array: --
$mode = COUNT_NORMAL:    int(3)
$mode = 0:               int(3)
$mode = COUNT_RECURSIVE: int(6)
$mode = 1:               int(6)
Done
