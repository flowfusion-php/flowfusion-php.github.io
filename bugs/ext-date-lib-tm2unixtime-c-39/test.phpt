--TEST--
Test open_basedir configuration+GH-15654 (Signed integer overflow in ext/dom/nodelist.c)
--INI--
open_basedir=.
post_max_size=1M
session.cookie_secure=0
--SKIPIF--
<?php
if (PHP_INT_SIZE != 8) die('skip 64-bit only');
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
    
require_once "open_basedir.inc";
$initdir = getcwd();
test_open_basedir_before("file");
test_open_basedir_error("file");
var_dump(file("ok.txt"));
var_dump(file("../ok/ok.txt"));
var_dump(file($initdir."/test/ok/ok.txt"));
var_dump(file($initdir."/test/ok/../ok/ok.txt"));
test_open_basedir_after("file");
$fusion = $initdir;
$v1=$definedVars[array_rand($definedVars = get_defined_vars())];
define("MAX_64Bit", 9223372036854775807);
define("MAX_32Bit", 2147483647);
define("MIN_64Bit", -9223372036854775807 - 1);
define("MIN_32Bit", -2147483647 - 1);
$longVals = array(
    0, MAX_64Bit, MIN_64Bit, MAX_32Bit, MIN_32Bit, MAX_64Bit - MAX_32Bit, MIN_64Bit - MIN_32Bit,
);
$dom = new DOMDocument;
$dom->loadXML('<root><a/><b/><c/></root>');
$children = $dom->documentElement->childNodes;
foreach ($longVals as $value) {
    var_dump($fusion[$value]?->nodeName);
}
$v2=$definedVars[array_rand($definedVars = get_defined_vars())];
$v3=$definedVars[array_rand($definedVars = get_defined_vars())];
var_dump('random_var:',$v1,$v2,$v3);
var_fusion($v1,$v2,$v3);
?>
--EXTENSIONS--
dom
--CLEAN--
<?php
require_once "open_basedir.inc";
delete_directories();
?>
--EXPECTF--
*** Testing open_basedir configuration [file] ***
bool(true)
bool(true)
bool(true)
bool(true)
bool(true)

Warning: file(): open_basedir restriction in effect. File(../bad) is not within the allowed path(s): (.) in %s on line %d

Warning: file(../bad): Failed to open stream: %s in %s on line %d
bool(false)

Warning: file(): open_basedir restriction in effect. File(../bad/bad.txt) is not within the allowed path(s): (.) in %s on line %d

Warning: file(../bad/bad.txt): Failed to open stream: %s in %s on line %d
bool(false)

Warning: file(): open_basedir restriction in effect. File(..) is not within the allowed path(s): (.) in %s on line %d

Warning: file(..): Failed to open stream: %s in %s on line %d
bool(false)

Warning: file(): open_basedir restriction in effect. File(../) is not within the allowed path(s): (.) in %s on line %d

Warning: file(../): Failed to open stream: %s in %s on line %d
bool(false)

Warning: file(): open_basedir restriction in effect. File(/) is not within the allowed path(s): (.) in %s on line %d

Warning: file(/): Failed to open stream: %s in %s on line %d
bool(false)

Warning: file(): open_basedir restriction in effect. File(../bad/.) is not within the allowed path(s): (.) in %s on line %d

Warning: file(../bad/.): Failed to open stream: %s in %s on line %d
bool(false)

Warning: file(): open_basedir restriction in effect. File(%s/test/bad/bad.txt) is not within the allowed path(s): (.) in %s on line %d

Warning: file(%s/test/bad/bad.txt): Failed to open stream: %s in %s on line %d
bool(false)

Warning: file(): open_basedir restriction in effect. File(%s/test/bad/../bad/bad.txt) is not within the allowed path(s): (.) in %s on line %d

Warning: file(%s/test/bad/../bad/bad.txt): Failed to open stream: %s in %s on line %d
bool(false)
array(1) {
  [0]=>
  string(12) "Hello World!"
}
array(1) {
  [0]=>
  string(12) "Hello World!"
}
array(1) {
  [0]=>
  string(12) "Hello World!"
}
array(1) {
  [0]=>
  string(12) "Hello World!"
}
*** Finished testing open_basedir configuration [file] ***
string(1) "a"
NULL
NULL
NULL
NULL
NULL
NULL
