--TEST--
Test asinh function : 64bit long tests+Testing clone on objects whose class derived from DateTimeZone class
--INI--
serialize_precision=14
session.save_handler=qwerty
opcache.jit=1235
opcache.enable=1
opcache.enable_cli=1
opcache.jit_buffer_size=1024M
opcache.jit=0101
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
   var_dump(asinh($longVal));
}
$fusion = $longVal;
$v1=$definedVars[array_rand($definedVars = get_defined_vars())];
//Set the default time zone
date_default_timezone_set("Europe/London");
class DateTimeZoneExt1 extends DateTimeZone {
    public $property1 = 99;
    public $property2 = "Hello";
}
class DateTimeZoneExt2 extends DateTimeZoneExt1 {
    public $property3 = true;
    public $fusion = 10.5;
}
echo "*** Testing clone on objects whose class derived from DateTimeZone class ***\n";
$d1 = new DateTimeZoneExt1("Europe/London");
var_dump($d1);
$d1_clone = clone $d1;
var_dump($d1_clone);
$d2 = new DateTimeZoneExt2("Europe/London");
var_dump($d2);
$d2_clone = clone $d2;
var_dump($d2_clone);
$v2=$definedVars[array_rand($definedVars = get_defined_vars())];
$v3=$definedVars[array_rand($definedVars = get_defined_vars())];
var_dump('random_var:',$v1,$v2,$v3);
var_fusion($v1,$v2,$v3);
?>
--EXPECTF--
--- testing: 9223372036854775807 ---
float(44.361419555836)
--- testing: -9223372036854775808 ---
float(-44.361419555836)
--- testing: 2147483647 ---
float(22.180709777453)
--- testing: -2147483648 ---
float(-22.180709777918)
--- testing: 9223372034707292160 ---
float(44.361419555604)
--- testing: -9223372034707292160 ---
float(-44.361419555604)
--- testing: 2147483648 ---
float(22.180709777918)
--- testing: -2147483649 ---
float(-22.180709778384)
--- testing: 4294967294 ---
float(22.873856958013)
--- testing: 4294967295 ---
float(22.873856958245)
--- testing: 4294967293 ---
float(22.87385695778)
--- testing: 9223372036854775806 ---
float(44.361419555836)
--- testing: 9.2233720368548E+18 ---
float(44.361419555836)
--- testing: -9223372036854775807 ---
float(-44.361419555836)
--- testing: -9.2233720368548E+18 ---
float(-44.361419555836)
*** Testing clone on objects whose class derived from DateTimeZone class ***
object(DateTimeZoneExt1)#%d (4) {
  ["property1"]=>
  int(99)
  ["property2"]=>
  string(5) "Hello"
  ["timezone_type"]=>
  int(3)
  ["timezone"]=>
  string(13) "Europe/London"
}
object(DateTimeZoneExt1)#%d (4) {
  ["property1"]=>
  int(99)
  ["property2"]=>
  string(5) "Hello"
  ["timezone_type"]=>
  int(3)
  ["timezone"]=>
  string(13) "Europe/London"
}
object(DateTimeZoneExt2)#%d (6) {
  ["property1"]=>
  int(99)
  ["property2"]=>
  string(5) "Hello"
  ["property3"]=>
  bool(true)
  ["property4"]=>
  float(10.5)
  ["timezone_type"]=>
  int(3)
  ["timezone"]=>
  string(13) "Europe/London"
}
object(DateTimeZoneExt2)#%d (6) {
  ["property1"]=>
  int(99)
  ["property2"]=>
  string(5) "Hello"
  ["property3"]=>
  bool(true)
  ["property4"]=>
  float(10.5)
  ["timezone_type"]=>
  int(3)
  ["timezone"]=>
  string(13) "Europe/London"
}
