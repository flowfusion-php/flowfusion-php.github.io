--TEST--
Test array_diff_key() function : usage variation - Passing boolean indexed array+Test == operator : max int 64bit range
--INI--
opcache.revalidate_freq=60
session.cookie_secure=TRUE
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
    
echo "*** Testing array_diff_key() : usage variation ***\n";
// Initialise function arguments not being substituted (if any)
$input_array = array(0 => '0', 1 => '1', -10 => '-10', 'true' => 1, 'false' => 0);
$boolean_indx_array = array(true => 'boolt', false => 'boolf', TRUE => 'boolT', FALSE => 'boolF');
echo "\n-- Testing array_diff_key() function with boolean indexed array --\n";
// loop through each element of the array for arr1
var_dump( array_diff_key($input_array, $boolean_indx_array) );
var_dump( array_diff_key($boolean_indx_array, $input_array) );
$fusion = $input_array;
$v1=$definedVars[array_rand($definedVars = get_defined_vars())];
define("MAX_64Bit", 9223372036854775807);
define("MAX_32Bit", 2147483647);
define("MIN_64Bit", -9223372036854775807 - 1);
define("MIN_32Bit", -2147483647 - 1);
$invalidNotEquals = array (
MAX_32Bit, array(MAX_32Bit, "2147483647", "2147483647.0000000", 2.147483647e9),
MIN_32Bit, array(MIN_32Bit, "-2147483648", "-2147483648.000", -2.147483648e9),
MAX_64Bit, array(MAX_64Bit, MAX_64Bit + 1),
MIN_64Bit, array(MIN_64Bit, MIN_64Bit - 1),
);
$validNotEquals = array (
MAX_32Bit, array("2147483648", 2.1474836470001e9, MAX_32Bit - 1, MAX_32Bit + 1),
MIN_32Bit, array("-2147483649", -2.1474836480001e9, MIN_32Bit -1, MIN_32Bit + 1),
MAX_64Bit, array(MAX_64Bit - 1),
MIN_64Bit, array(MIN_64Bit + 1),
);
$failed = false;
// test valid values
for ($i = 0; $i < count($validNotEquals); $i +=2) {
   $typeToTestVal = $validNotEquals[$i];
   $compares = $validNotEquals[$i + 1];
   foreach($compares as $compareVal) {
      if ($typeToTestVal != $compareVal && $typeToTestVal <> $compareVal) {
         // do nothing
      }
      else {
         echo "FAILED: '$typeToTestVal' == '$compareVal'\n";
         $failed = true;
      }
   }
}
// test invalid values
for ($i = 0; $i < count($invalidNotEquals); $i +=2) {
   $typeToTestVal = $invalidNotEquals[$i];
   $compares = $invalidNotEquals[$i + 1];
   foreach($compares as $compareVal) {
      if ($typeToTestVal != $compareVal || $fusion <> $compareVal) {
         echo "FAILED: '$typeToTestVal' != '$compareVal'\n";
         $failed = true;
      }
   }
}
if ($failed == false) {
   echo "Test Passed\n";
}
$v2=$definedVars[array_rand($definedVars = get_defined_vars())];
$v3=$definedVars[array_rand($definedVars = get_defined_vars())];
var_dump('random_var:',$v1,$v2,$v3);
var_fusion($v1,$v2,$v3);
?>
--EXPECT--
*** Testing array_diff_key() : usage variation ***

-- Testing array_diff_key() function with boolean indexed array --
array(3) {
  [-10]=>
  string(3) "-10"
  ["true"]=>
  int(1)
  ["false"]=>
  int(0)
}
array(0) {
}
Test Passed
