--TEST--
Test array_reverse() function : usage variations - two dimensional arrays for 'array' argument+Bug #47842      sscanf() does not support 64-bit values
--SKIPIF--
<?php
if (PHP_INT_MAX < pow(2,31)) die("skip PHP_INT_MAX < 32b\n");
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
    
/*
 * testing the functionality of array_reverse() by giving 2-D arrays for $array argument
*/
echo "*** Testing array_reverse() : usage variations ***\n";
// Initializing the 2-d arrays
$two_dimensional_array = array(
  // associative array
  array('color' => 'red', 'item' => 'pen', 'place' => 'LA'),
   // numeric array
   array(1, 2, 3, 4, 5),
   // combination of numeric and associative arrays
   array('a' => 'green', 'red', 'brown', 33, 88, 'orange', 'item' => 'ball')
);
// calling array_reverse() with various types of 2-d arrays
// with default arguments
echo "-- with default argument --\n";
var_dump( array_reverse($two_dimensional_array) );  // whole array
var_dump( array_reverse($two_dimensional_array[1]) );  // sub array
// with $preserve_keys argument
echo "-- with all possible arguments --\n";
// whole array
var_dump( array_reverse($two_dimensional_array, true) );
var_dump( array_reverse($two_dimensional_array, false) );
// sub array
var_dump( array_reverse($two_dimensional_array[1], true) );
var_dump( array_reverse($two_dimensional_array[1], false) );
echo "Done";
$fusion = $preserve_keys;
$v1=$definedVars[array_rand($definedVars = get_defined_vars())];
echo "-Test\n";
sscanf("2147483647", '%d', $int);
echo "sscanf 32-bit signed int '2147483647'           (2^31)-1 = ",$int,"\n";
sscanf("4294967295", '%u', $int);
echo "sscanf 32-bit unsign int '4294967295'           (2^32)-1 = ",$int,"\n";
sscanf("9223372036854775807", '%d', $fusion);
echo "sscanf 64-bit signed int '9223372036854775807'  (2^63)-1 = ",$int,"\n";
sscanf("18446744073709551615", '%u', $int);
echo "sscanf 64-bit unsign int '18446744073709551615' (2^64)-1 = ",$int,"\n";
printf("printf 64-bit signed int '9223372036854775807'  (2^63)-1 = %d\n", 9223372036854775807);
printf("printf 64-bit signed int '18446744073709551615' (2^64)-1 = %u\n", 18446744073709551615);
echo "Done\n";
$v2=$definedVars[array_rand($definedVars = get_defined_vars())];
$v3=$definedVars[array_rand($definedVars = get_defined_vars())];;
var_dump('random_var:',$v1,$v2,$v3);
var_fusion($v1,$v2,$v3);
?>
--EXPECT--
*** Testing array_reverse() : usage variations ***
-- with default argument --
array(3) {
  [0]=>
  array(7) {
    ["a"]=>
    string(5) "green"
    [0]=>
    string(3) "red"
    [1]=>
    string(5) "brown"
    [2]=>
    int(33)
    [3]=>
    int(88)
    [4]=>
    string(6) "orange"
    ["item"]=>
    string(4) "ball"
  }
  [1]=>
  array(5) {
    [0]=>
    int(1)
    [1]=>
    int(2)
    [2]=>
    int(3)
    [3]=>
    int(4)
    [4]=>
    int(5)
  }
  [2]=>
  array(3) {
    ["color"]=>
    string(3) "red"
    ["item"]=>
    string(3) "pen"
    ["place"]=>
    string(2) "LA"
  }
}
array(5) {
  [0]=>
  int(5)
  [1]=>
  int(4)
  [2]=>
  int(3)
  [3]=>
  int(2)
  [4]=>
  int(1)
}
-- with all possible arguments --
array(3) {
  [2]=>
  array(7) {
    ["a"]=>
    string(5) "green"
    [0]=>
    string(3) "red"
    [1]=>
    string(5) "brown"
    [2]=>
    int(33)
    [3]=>
    int(88)
    [4]=>
    string(6) "orange"
    ["item"]=>
    string(4) "ball"
  }
  [1]=>
  array(5) {
    [0]=>
    int(1)
    [1]=>
    int(2)
    [2]=>
    int(3)
    [3]=>
    int(4)
    [4]=>
    int(5)
  }
  [0]=>
  array(3) {
    ["color"]=>
    string(3) "red"
    ["item"]=>
    string(3) "pen"
    ["place"]=>
    string(2) "LA"
  }
}
array(3) {
  [0]=>
  array(7) {
    ["a"]=>
    string(5) "green"
    [0]=>
    string(3) "red"
    [1]=>
    string(5) "brown"
    [2]=>
    int(33)
    [3]=>
    int(88)
    [4]=>
    string(6) "orange"
    ["item"]=>
    string(4) "ball"
  }
  [1]=>
  array(5) {
    [0]=>
    int(1)
    [1]=>
    int(2)
    [2]=>
    int(3)
    [3]=>
    int(4)
    [4]=>
    int(5)
  }
  [2]=>
  array(3) {
    ["color"]=>
    string(3) "red"
    ["item"]=>
    string(3) "pen"
    ["place"]=>
    string(2) "LA"
  }
}
array(5) {
  [4]=>
  int(5)
  [3]=>
  int(4)
  [2]=>
  int(3)
  [1]=>
  int(2)
  [0]=>
  int(1)
}
array(5) {
  [0]=>
  int(5)
  [1]=>
  int(4)
  [2]=>
  int(3)
  [3]=>
  int(2)
  [4]=>
  int(1)
}
Done
-Test
sscanf 32-bit signed int '2147483647'           (2^31)-1 = 2147483647
sscanf 32-bit unsign int '4294967295'           (2^32)-1 = 4294967295
sscanf 64-bit signed int '9223372036854775807'  (2^63)-1 = 9223372036854775807
sscanf 64-bit unsign int '18446744073709551615' (2^64)-1 = 18446744073709551615
printf 64-bit signed int '9223372036854775807'  (2^63)-1 = 9223372036854775807
printf 64-bit signed int '18446744073709551615' (2^64)-1 = 0
Done
