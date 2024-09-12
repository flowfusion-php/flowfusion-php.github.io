--TEST--
Test parse_str() function : basic functionality+JIT UCALL: 002
--INI--
opcache.enable=1
opcache.enable_cli=1
opcache.file_update_protection=0
;opcache.jit_debug=257
opcache.memory_consumption=7
error_reporting=E_ALL & ~E_DEPRECATED
opcache.enable=1
opcache.enable_cli=1
opcache.jit_buffer_size=1024M
opcache.jit=1144
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
    
echo "*** Testing parse_str() : basic functionality ***\n";
echo "\nBasic test WITH undefined var for result arg\n";
$s1 = "first=val1&second=val2&third=val3";
var_dump(parse_str($s1, $res1));
var_dump($res1);
echo "\nBasic test WITH existing non-array var for result arg\n";
$res2 =99;
$s1 = "first=val1&second=val2&third=val3";
var_dump(parse_str($s1, $res2));
var_dump($res2);
echo "\nBasic test with an existing array as results array\n";
$res3_array = array(1,2,3,4);
var_dump(parse_str($s1, $res3_array));
var_dump($res3_array);
$v1=$definedVars[array_rand($definedVars = get_defined_vars())];
function foo() {
    var_dump("hello");
}
foo();
var_dump("world!");
$v2=$definedVars[array_rand($definedVars = get_defined_vars())];
$v3=$definedVars[array_rand($definedVars = get_defined_vars())];
var_dump('random_var:',$v1,$v2,$v3);
var_fusion($v1,$v2,$v3);
?>
--EXTENSIONS--
opcache
--EXPECT--
*** Testing parse_str() : basic functionality ***

Basic test WITH undefined var for result arg
NULL
array(3) {
  ["first"]=>
  string(4) "val1"
  ["second"]=>
  string(4) "val2"
  ["third"]=>
  string(4) "val3"
}

Basic test WITH existing non-array var for result arg
NULL
array(3) {
  ["first"]=>
  string(4) "val1"
  ["second"]=>
  string(4) "val2"
  ["third"]=>
  string(4) "val3"
}

Basic test with an existing array as results array
NULL
array(3) {
  ["first"]=>
  string(4) "val1"
  ["second"]=>
  string(4) "val2"
  ["third"]=>
  string(4) "val3"
}
string(5) "hello"
string(6) "world!"
