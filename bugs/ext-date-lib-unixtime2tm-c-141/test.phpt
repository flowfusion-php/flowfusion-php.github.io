--TEST--
Test rad2deg function : 64bit long tests+Test date_sunset() function : usage variation -  Checking with North and South poles when Sun is up and down all day
--INI--
error_reporting=E_ALL&~E_DEPRECATED
opcache.interned_strings_buffer=131072
opcache.interned_strings_buffer=16
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
   var_dump(rad2deg($longVal));
}
$fusion = $longVal;
$v1=$definedVars[array_rand($definedVars = get_defined_vars())];
echo "*** Testing date_sunset() : usage variation ***\n";
// GMT is zero for the timezone
date_default_timezone_set("Africa/Casablanca");
$time_date = array (
        //Date at which Sun is up all day at North Pole
        "12 Aug 2008" => mktime(8, 8, 8, 8, 12, 2008),
        "13 Aug 2008" => mktime(8, 8, 8, 8, 13, 2008),
        //Date at which Sun is up all day at South Pole
        "12 Nov 2008" => mktime(8, 8, 8, 11, 12, 2008),
        "13 Nov 2008" => mktime(8, 8, 8, 11, 13, 2008),
);
//Iterate over different date and time
foreach( $time_date as $date => $time ){
    echo "\n--$date--\n";
    var_dump( date_sunset($time, SUNFUNCS_RET_STRING, 90, 0 ) );
    var_dump( date_sunset($time, SUNFUNCS_RET_DOUBLE, 90, 0 ) );
    var_dump( date_sunset($time, SUNFUNCS_RET_TIMESTAMP, 90, 0 ) );
    var_dump( date_sunset($fusion, SUNFUNCS_RET_STRING, -90, 0 ) );
    var_dump( date_sunset($time, SUNFUNCS_RET_DOUBLE, -90, 0 ) );
    var_dump( date_sunset($time, SUNFUNCS_RET_TIMESTAMP, -90, 0 ) );
}
$v2=$definedVars[array_rand($definedVars = get_defined_vars())];
$v3=$definedVars[array_rand($definedVars = get_defined_vars())];
var_dump('random_var:',$v1,$v2,$v3);
var_fusion($v1,$v2,$v3);
?>
--EXPECT--
--- testing: 9223372036854775807 ---
float(5.2846029059076024E+20)
--- testing: -9223372036854775808 ---
float(-5.2846029059076024E+20)
--- testing: 2147483647 ---
float(123041749546.46191)
--- testing: -2147483648 ---
float(-123041749603.75769)
--- testing: 9223372034707292160 ---
float(5.284602904677184E+20)
--- testing: -9223372034707292160 ---
float(-5.284602904677184E+20)
--- testing: 2147483648 ---
float(123041749603.75769)
--- testing: -2147483649 ---
float(-123041749661.05348)
--- testing: 4294967294 ---
float(246083499092.92383)
--- testing: 4294967295 ---
float(246083499150.21957)
--- testing: 4294967293 ---
float(246083499035.62805)
--- testing: 9223372036854775806 ---
float(5.2846029059076024E+20)
--- testing: 9.2233720368548E+18 ---
float(5.2846029059076024E+20)
--- testing: -9223372036854775807 ---
float(-5.2846029059076024E+20)
--- testing: -9.2233720368548E+18 ---
float(-5.2846029059076024E+20)
*** Testing date_sunset() : usage variation ***

--12 Aug 2008--
bool(false)
bool(false)
bool(false)
bool(false)
bool(false)
bool(false)

--13 Aug 2008--
bool(false)
bool(false)
bool(false)
bool(false)
bool(false)
bool(false)

--12 Nov 2008--
bool(false)
bool(false)
bool(false)
bool(false)
bool(false)
bool(false)

--13 Nov 2008--
bool(false)
bool(false)
bool(false)
bool(false)
bool(false)
bool(false)
