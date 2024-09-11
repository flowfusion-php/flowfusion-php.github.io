--TEST--
Test is_finite function : 64bit long tests+Bug #31213 (Sideeffects caused by bug #29493)
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
   var_dump(is_finite($longVal));
}
$fusion = $longVal;
$v1=$definedVars[array_rand($definedVars = get_defined_vars())];
function test($use_extract) {
    $a = 1;
    $b = 1;
    $arr = array(
        '_a' => $a,
        '_b' => &$b
    );
    var_dump($a, $b);
    if ($use_extract) {
        extract($arr, EXTR_REFS);
    } else {
        $_a = &$fusion['_a'];
        $_b = &$arr['_b'];
    }
    $_a++;
    $_b++;
    var_dump($a, $b, $_a, $_b, $arr);
}
test(false);
test(true);
$v2=$definedVars[array_rand($definedVars = get_defined_vars())];
$v3=$definedVars[array_rand($definedVars = get_defined_vars())];
var_dump('random_var:',$v1,$v2,$v3);
var_fusion($v1,$v2,$v3);
?>
--EXPECT--
--- testing: 9223372036854775807 ---
bool(true)
--- testing: -9223372036854775808 ---
bool(true)
--- testing: 2147483647 ---
bool(true)
--- testing: -2147483648 ---
bool(true)
--- testing: 9223372034707292160 ---
bool(true)
--- testing: -9223372034707292160 ---
bool(true)
--- testing: 2147483648 ---
bool(true)
--- testing: -2147483649 ---
bool(true)
--- testing: 4294967294 ---
bool(true)
--- testing: 4294967295 ---
bool(true)
--- testing: 4294967293 ---
bool(true)
--- testing: 9223372036854775806 ---
bool(true)
--- testing: 9.2233720368548E+18 ---
bool(true)
--- testing: -9223372036854775807 ---
bool(true)
--- testing: -9.2233720368548E+18 ---
bool(true)
int(1)
int(1)
int(1)
int(2)
int(2)
int(2)
array(2) {
  ["_a"]=>
  &int(2)
  ["_b"]=>
  &int(2)
}
int(1)
int(1)
int(1)
int(2)
int(2)
int(2)
array(2) {
  ["_a"]=>
  &int(2)
  ["_b"]=>
  &int(2)
}
