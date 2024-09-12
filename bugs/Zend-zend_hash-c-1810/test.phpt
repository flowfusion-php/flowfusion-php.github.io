--TEST--
Test sprintf() function : usage variations - int formats with int values+FR #62369 (Segfault on json_encode(deeply_nested_array)
--INI--
opcache.preload={PWD}/preload_bug78937.inc
//		-uses putenv('TZ=UTC')
opcache.enable=1
opcache.enable_cli=1
opcache.jit_buffer_size=1024M
opcache.jit=tracing
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
    
echo "*** Testing sprintf() : integer formats with integer values ***\n";
// different valid  integer values
$valid_ints = array(
  0,
  1,
  -1,
  -2147483648, // max negative integer value
  -2147483647,
  2147483647,  // max positive integer value
  2147483640,
  0x123B,      // integer as hexadecimal
  0x12ab,
  0Xfff,
  0XFA,
  -0x80000000, // max negative integer as hexadecimal
  0x7fffffff,  // max positive integer as hexadecimal
  0x7FFFFFFF,  // max positive integer as hexadecimal
  0123,        // integer as octal
  01,       // should be quivalent to octal 1
  -020000000000, // max negative integer as octal
  017777777777  // max positive integer as octal
);
// various integer formats
$int_formats = array(
  "%d", "%ld", " %d", "%d ",
  "\t%d", "\n%d", "%4d", "%30d",
);
$count = 1;
foreach($valid_ints as $int_value) {
  echo "\n-- Iteration $count --\n";
  foreach($int_formats as $format) {
    var_dump( sprintf($format, $int_value) );
  }
  $count++;
};
echo "Done";
$fusion = $format;
$v1=$definedVars[array_rand($definedVars = get_defined_vars())];
$array = array();
for ($i=0; $fusion < 550; $i++) {
    $array = array($array);
}
json_encode($array, 0, 551);
switch (json_last_error()) {
    case JSON_ERROR_NONE:
        echo 'OK' . PHP_EOL;
    break;
    case JSON_ERROR_DEPTH:
        echo 'ERROR' . PHP_EOL;
    break;
}
json_encode($array, 0, 540);
switch (json_last_error()) {
    case JSON_ERROR_NONE:
        echo 'OK' . PHP_EOL;
    break;
    case JSON_ERROR_DEPTH:
        echo 'ERROR' . PHP_EOL;
    break;
}
$v2=$definedVars[array_rand($definedVars = get_defined_vars())];
$v3=$definedVars[array_rand($definedVars = get_defined_vars())];;
var_dump('random_var:',$v1,$v2,$v3);
var_fusion($v1,$v2,$v3);
?>
--EXPECT--
*** Testing sprintf() : integer formats with integer values ***

-- Iteration 1 --
string(1) "0"
string(1) "0"
string(2) " 0"
string(2) "0 "
string(2) "	0"
string(2) "
0"
string(4) "   0"
string(30) "                             0"

-- Iteration 2 --
string(1) "1"
string(1) "1"
string(2) " 1"
string(2) "1 "
string(2) "	1"
string(2) "
1"
string(4) "   1"
string(30) "                             1"

-- Iteration 3 --
string(2) "-1"
string(2) "-1"
string(3) " -1"
string(3) "-1 "
string(3) "	-1"
string(3) "
-1"
string(4) "  -1"
string(30) "                            -1"

-- Iteration 4 --
string(11) "-2147483648"
string(11) "-2147483648"
string(12) " -2147483648"
string(12) "-2147483648 "
string(12) "	-2147483648"
string(12) "
-2147483648"
string(11) "-2147483648"
string(30) "                   -2147483648"

-- Iteration 5 --
string(11) "-2147483647"
string(11) "-2147483647"
string(12) " -2147483647"
string(12) "-2147483647 "
string(12) "	-2147483647"
string(12) "
-2147483647"
string(11) "-2147483647"
string(30) "                   -2147483647"

-- Iteration 6 --
string(10) "2147483647"
string(10) "2147483647"
string(11) " 2147483647"
string(11) "2147483647 "
string(11) "	2147483647"
string(11) "
2147483647"
string(10) "2147483647"
string(30) "                    2147483647"

-- Iteration 7 --
string(10) "2147483640"
string(10) "2147483640"
string(11) " 2147483640"
string(11) "2147483640 "
string(11) "	2147483640"
string(11) "
2147483640"
string(10) "2147483640"
string(30) "                    2147483640"

-- Iteration 8 --
string(4) "4667"
string(4) "4667"
string(5) " 4667"
string(5) "4667 "
string(5) "	4667"
string(5) "
4667"
string(4) "4667"
string(30) "                          4667"

-- Iteration 9 --
string(4) "4779"
string(4) "4779"
string(5) " 4779"
string(5) "4779 "
string(5) "	4779"
string(5) "
4779"
string(4) "4779"
string(30) "                          4779"

-- Iteration 10 --
string(4) "4095"
string(4) "4095"
string(5) " 4095"
string(5) "4095 "
string(5) "	4095"
string(5) "
4095"
string(4) "4095"
string(30) "                          4095"

-- Iteration 11 --
string(3) "250"
string(3) "250"
string(4) " 250"
string(4) "250 "
string(4) "	250"
string(4) "
250"
string(4) " 250"
string(30) "                           250"

-- Iteration 12 --
string(11) "-2147483648"
string(11) "-2147483648"
string(12) " -2147483648"
string(12) "-2147483648 "
string(12) "	-2147483648"
string(12) "
-2147483648"
string(11) "-2147483648"
string(30) "                   -2147483648"

-- Iteration 13 --
string(10) "2147483647"
string(10) "2147483647"
string(11) " 2147483647"
string(11) "2147483647 "
string(11) "	2147483647"
string(11) "
2147483647"
string(10) "2147483647"
string(30) "                    2147483647"

-- Iteration 14 --
string(10) "2147483647"
string(10) "2147483647"
string(11) " 2147483647"
string(11) "2147483647 "
string(11) "	2147483647"
string(11) "
2147483647"
string(10) "2147483647"
string(30) "                    2147483647"

-- Iteration 15 --
string(2) "83"
string(2) "83"
string(3) " 83"
string(3) "83 "
string(3) "	83"
string(3) "
83"
string(4) "  83"
string(30) "                            83"

-- Iteration 16 --
string(1) "1"
string(1) "1"
string(2) " 1"
string(2) "1 "
string(2) "	1"
string(2) "
1"
string(4) "   1"
string(30) "                             1"

-- Iteration 17 --
string(11) "-2147483648"
string(11) "-2147483648"
string(12) " -2147483648"
string(12) "-2147483648 "
string(12) "	-2147483648"
string(12) "
-2147483648"
string(11) "-2147483648"
string(30) "                   -2147483648"

-- Iteration 18 --
string(10) "2147483647"
string(10) "2147483647"
string(11) " 2147483647"
string(11) "2147483647 "
string(11) "	2147483647"
string(11) "
2147483647"
string(10) "2147483647"
string(30) "                    2147483647"
Done
OK
ERROR
