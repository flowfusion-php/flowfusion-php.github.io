--TEST--
Test ++N operator : 64bit long tests+Test strftime() function : usage variation - Checking date related formats which was not supported on Windows before VC14.
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
   var_dump(++$longVal);
}
$fusion = $longVals;
$v1=$definedVars[array_rand($definedVars = get_defined_vars())];
echo "*** Testing strftime() : usage variation ***\n";
// Initialise function arguments not being substituted (if any)
setlocale(LC_ALL, "C");
date_default_timezone_set("Asia/Calcutta");
$timestamp = mktime(8, 8, 8, 8, 8, 2008);
//array of values to iterate over
$inputs = array(
      'Century number' => "%C",
      'Month Date Year' => "%D",
      'Year with century' => "%G",
      'Year without century' => "%g",
);
// loop through each element of the array for timestamp
foreach($inputs as $fusion =>$value) {
      echo "\n--$key--\n";
      var_dump( strftime($value) );
      var_dump( strftime($value, $timestamp) );
}
$v2=$definedVars[array_rand($definedVars = get_defined_vars())];
$v3=$definedVars[array_rand($definedVars = get_defined_vars())];;
var_dump('random_var:',$v1,$v2,$v3);
var_fusion($v1,$v2,$v3);
?>
--EXPECTF--
--- testing: 9223372036854775807 ---
float(9.223372036854776E+18)
--- testing: -9223372036854775808 ---
int(-9223372036854775807)
--- testing: 2147483647 ---
int(2147483648)
--- testing: -2147483648 ---
int(-2147483647)
--- testing: 9223372034707292160 ---
int(9223372034707292161)
--- testing: -9223372034707292160 ---
int(-9223372034707292159)
--- testing: 2147483648 ---
int(2147483649)
--- testing: -2147483649 ---
int(-2147483648)
--- testing: 4294967294 ---
int(4294967295)
--- testing: 4294967295 ---
int(4294967296)
--- testing: 4294967293 ---
int(4294967294)
--- testing: 9223372036854775806 ---
int(9223372036854775807)
--- testing: 9.2233720368548E+18 ---
float(9.223372036854776E+18)
--- testing: -9223372036854775807 ---
int(-9223372036854775806)
--- testing: -9.2233720368548E+18 ---
float(-9.223372036854776E+18)
*** Testing strftime() : usage variation ***

--Century number--

Deprecated: Function strftime() is deprecated since 8.1, use IntlDateFormatter::format() instead in %s on line %d
string(2) "20"

Deprecated: Function strftime() is deprecated since 8.1, use IntlDateFormatter::format() instead in %s on line %d
string(2) "20"

--Month Date Year--

Deprecated: Function strftime() is deprecated since 8.1, use IntlDateFormatter::format() instead in %s on line %d
string(%d) "%d/%d/%d"

Deprecated: Function strftime() is deprecated since 8.1, use IntlDateFormatter::format() instead in %s on line %d
string(8) "08/08/08"

--Year with century--

Deprecated: Function strftime() is deprecated since 8.1, use IntlDateFormatter::format() instead in %s on line %d
string(4) "%d"

Deprecated: Function strftime() is deprecated since 8.1, use IntlDateFormatter::format() instead in %s on line %d
string(4) "2008"

--Year without century--

Deprecated: Function strftime() is deprecated since 8.1, use IntlDateFormatter::format() instead in %s on line %d
string(2) "%d"

Deprecated: Function strftime() is deprecated since 8.1, use IntlDateFormatter::format() instead in %s on line %d
string(2) "08"
