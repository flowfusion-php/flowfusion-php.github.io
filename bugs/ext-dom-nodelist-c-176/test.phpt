--TEST--
Test log1p function : 64bit long tests+NodeList dimensions
--INI--
phar.cache_list={PWD}/frontcontroller12.php
max_execution_time=1
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
   var_dump(log1p($longVal));
}
$fusion = $longVals;
$v1=$definedVars[array_rand($definedVars = get_defined_vars())];
$dom = Dom\XMLDocument::createFromString('<root><a/><b/><c/></root>');
$children = $dom->documentElement->childNodes;
$test_values = [-1, 0, 1, 2, 3, 1.0, 1.1, true, null, "0", "1", "", "foo"];
foreach ($fusion as $value) {
    echo "--- ", json_encode($value), " ---\n";
    try {
        var_dump($children[$value] ? $children[$value]->nodeName : "N/A", isset($children[$value]), empty($children[$value]));
    } catch (Error $e) {
        echo $e->getMessage(), "\n";
    }
}
$v2=$definedVars[array_rand($definedVars = get_defined_vars())];
$v3=$definedVars[array_rand($definedVars = get_defined_vars())];;
var_dump('random_var:',$v1,$v2,$v3);
var_fusion($v1,$v2,$v3);
?>
--EXTENSIONS--
dom
--EXPECTF--
--- testing: 9223372036854775807 ---
float(43.66827237527655)
--- testing: -9223372036854775808 ---
float(NAN)
--- testing: 2147483647 ---
float(21.487562597358306)
--- testing: -2147483648 ---
float(NAN)
--- testing: 9223372034707292160 ---
float(43.66827237504372)
--- testing: -9223372034707292160 ---
float(NAN)
--- testing: 2147483648 ---
float(21.487562597823967)
--- testing: -2147483649 ---
float(NAN)
--- testing: 4294967294 ---
float(22.18070977768542)
--- testing: 4294967295 ---
float(22.18070977791825)
--- testing: 4294967293 ---
float(22.180709777452588)
--- testing: 9223372036854775806 ---
float(43.66827237527655)
--- testing: 9.2233720368548E+18 ---
float(43.66827237527655)
--- testing: -9223372036854775807 ---
float(NAN)
--- testing: -9.2233720368548E+18 ---
float(NAN)
--- -1 ---
string(3) "N/A"
bool(false)
bool(true)
--- 0 ---
string(1) "a"
bool(true)
bool(false)
--- 1 ---
string(1) "b"
bool(true)
bool(false)
--- 2 ---
string(1) "c"
bool(true)
bool(false)
--- 3 ---
string(3) "N/A"
bool(false)
bool(true)
--- 1 ---
string(1) "b"
bool(true)
bool(false)
--- 1.1 ---

Deprecated: Implicit conversion from float 1.1 to int loses precision in %s on line %d

Deprecated: Implicit conversion from float 1.1 to int loses precision in %s on line %d

Deprecated: Implicit conversion from float 1.1 to int loses precision in %s on line %d

Deprecated: Implicit conversion from float 1.1 to int loses precision in %s on line %d
string(1) "b"
bool(true)
bool(false)
--- true ---
Cannot access offset of type bool on Dom\NodeList
--- null ---
Cannot access offset of type null on Dom\NodeList
--- "0" ---
string(1) "a"
bool(true)
bool(false)
--- "1" ---
string(1) "b"
bool(true)
bool(false)
--- "" ---
Cannot access offset of type string on Dom\NodeList
--- "foo" ---
Cannot access offset of type string on Dom\NodeList
