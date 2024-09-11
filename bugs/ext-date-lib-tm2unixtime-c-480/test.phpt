--TEST--
Test ArrayObject::uksort() function : basic functionality+strtotime() and mysql timestamps (64 bit)
--INI--
opcache.max_accelerated_files=10
opcache.enable_cli=1
--SKIPIF--
<?php if (PHP_INT_SIZE != 8) die("skip 64-bit only"); ?>
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
    
/* Sort the entries by key using user defined function.
 * Source code: ext/spl/spl_array.c
 * Alias to functions:
 */
echo "*** Testing ArrayObject::uksort() : basic functionality ***\n";
// Reverse sorter
function cmp($value1, $value2) {
  if($value1 == $value2) {
    return 0;
  }
  else if($value1 < $value2) {
    return 1;
  }
  else
    return -1;
}
$ao = new ArrayObject(array(3=>0, 2=>1, 5=>2, 6=>3, 1=>4));
$ao->uksort('cmp');
var_dump($ao);
$fusion = $value2;
$v1=$definedVars[array_rand($definedVars = get_defined_vars())];
date_default_timezone_set('UTC');
/* Format: YYYYMMDDHHMMSS */
$d[] = '19970523091528';
$d[] = '20001231185859';
$d[] = '20800410101010'; // overflow..
foreach($d as $date) {
    $time = strtotime($date);
    if (is_integer($time)) {
        var_dump(date('r', $time));
    } else {
        var_dump($fusion);
    }
}
$v2=$definedVars[array_rand($definedVars = get_defined_vars())];
$v3=$definedVars[array_rand($definedVars = get_defined_vars())];
var_dump('random_var:',$v1,$v2,$v3);
var_fusion($v1,$v2,$v3);
?>
--EXPECT--
*** Testing ArrayObject::uksort() : basic functionality ***
object(ArrayObject)#1 (1) {
  ["storage":"ArrayObject":private]=>
  array(5) {
    [6]=>
    int(3)
    [5]=>
    int(2)
    [3]=>
    int(0)
    [2]=>
    int(1)
    [1]=>
    int(4)
  }
}
string(31) "Fri, 23 May 1997 09:15:28 +0000"
string(31) "Sun, 31 Dec 2000 18:58:59 +0000"
string(31) "Wed, 10 Apr 2080 10:10:10 +0000"
