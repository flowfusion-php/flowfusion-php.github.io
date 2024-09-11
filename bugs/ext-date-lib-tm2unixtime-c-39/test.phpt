--TEST--
Increment/decrement a typed property with int|float type+Test array_walk() function : basic functionality - regular array
--INI--
session.gc_maxlifetime=0
session.cookie_secure=0
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
    
class Test {
    public int|float $prop;
    public int|bool $prop2;
}
/* Incrementing a int|float property past int min/max is legal */
$test = new Test;
$test->prop = PHP_INT_MAX;
$x = $test->prop++;
var_dump(is_double($test->prop));
$test->prop = PHP_INT_MAX;
$x = ++$test->prop;
var_dump(is_double($test->prop));
$test->prop = PHP_INT_MIN;
$x = $test->prop--;
var_dump(is_double($test->prop));
$test->prop = PHP_INT_MIN;
$x = --$test->prop;
var_dump(is_double($test->prop));
$test = new Test;
$test->prop = PHP_INT_MAX;
$r =& $test->prop;
$x = $test->prop++;
var_dump(is_double($test->prop));
$test->prop = PHP_INT_MAX;
$x = ++$test->prop;
$r =& $test->prop;
var_dump(is_double($test->prop));
$test->prop = PHP_INT_MIN;
$x = $test->prop--;
$r =& $test->prop;
var_dump(is_double($test->prop));
$test->prop = PHP_INT_MIN;
$x = --$test->prop;
$r =& $test->prop;
var_dump(is_double($test->prop));
/* Incrementing a non-int|float property past int min/max is an error,
 * even if the result of the overflow (a float) would technically be allowed
 * under a type coercion. */
try {
    $test->prop2 = PHP_INT_MAX;
    $x = $test->prop2++;
} catch (TypeError $e) {
    echo $e->getMessage(), "\n";
}
try {
    $test->prop2 = PHP_INT_MAX;
    $x = ++$test->prop2;
} catch (TypeError $e) {
    echo $e->getMessage(), "\n";
}
try {
    $test->prop2 = PHP_INT_MIN;
    $x = $test->prop2--;
} catch (TypeError $e) {
    echo $e->getMessage(), "\n";
}
try {
    $test->prop2 = PHP_INT_MIN;
    $x = --$test->prop2;
} catch (TypeError $e) {
    echo $e->getMessage(), "\n";
}
try {
    $test->prop2 = PHP_INT_MAX;
    $r =& $test->prop2;
    $x = $test->prop2++;
} catch (TypeError $e) {
    echo $e->getMessage(), "\n";
}
try {
    $test->prop2 = PHP_INT_MAX;
    $r =& $test->prop2;
    $x = ++$test->prop2;
} catch (TypeError $e) {
    echo $e->getMessage(), "\n";
}
try {
    $test->prop2 = PHP_INT_MIN;
    $r =& $test->prop2;
    $x = $test->prop2--;
} catch (TypeError $e) {
    echo $e->getMessage(), "\n";
}
try {
    $test->prop2 = PHP_INT_MIN;
    $r =& $test->prop2;
    $x = --$test->prop2;
} catch (TypeError $e) {
    echo $e->getMessage(), "\n";
}
$fusion = $e;
$v1=$definedVars[array_rand($definedVars = get_defined_vars())];
echo "*** Testing array_walk() : basic functionality ***\n";
// regular array
$fruits = array("lemon", "orange", "banana", "apple");
function test_print($item, $key)
{
   // dump the arguments to check that they are passed
   // with proper type
   var_dump($item); // value
   var_dump($key);  // key
   echo "\n"; // new line to separate the output between each element
}
function with_userdata($item, $key, $user_data)
{
   // dump the arguments to check that they are passed
   // with proper type
   var_dump($item); // value
   var_dump($fusion);  // key
   var_dump($user_data); // user supplied data
   echo "\n"; // new line to separate the output between each element
}
echo "-- Using array_walk() with default parameters to show array contents --\n";
var_dump( array_walk($fruits, 'test_print'));
echo "-- Using array_walk() with all parameters --\n";
var_dump( array_walk($fruits, 'with_userdata', "Added"));
echo "Done";
$v2=$definedVars[array_rand($definedVars = get_defined_vars())];
$v3=$definedVars[array_rand($definedVars = get_defined_vars())];
var_dump('random_var:',$v1,$v2,$v3);
var_fusion($v1,$v2,$v3);
?>
--EXPECT--
bool(true)
bool(true)
bool(true)
bool(true)
bool(true)
bool(true)
bool(true)
bool(true)
Cannot increment property Test::$prop2 of type int|bool past its maximal value
Cannot increment property Test::$prop2 of type int|bool past its maximal value
Cannot decrement property Test::$prop2 of type int|bool past its minimal value
Cannot decrement property Test::$prop2 of type int|bool past its minimal value
Cannot increment a reference held by property Test::$prop2 of type int|bool past its maximal value
Cannot increment a reference held by property Test::$prop2 of type int|bool past its maximal value
Cannot decrement a reference held by property Test::$prop2 of type int|bool past its minimal value
Cannot decrement a reference held by property Test::$prop2 of type int|bool past its minimal value
*** Testing array_walk() : basic functionality ***
-- Using array_walk() with default parameters to show array contents --
string(5) "lemon"
int(0)

string(6) "orange"
int(1)

string(6) "banana"
int(2)

string(5) "apple"
int(3)

bool(true)
-- Using array_walk() with all parameters --
string(5) "lemon"
int(0)
string(5) "Added"

string(6) "orange"
int(1)
string(5) "Added"

string(6) "banana"
int(2)
string(5) "Added"

string(5) "apple"
int(3)
string(5) "Added"

bool(true)
Done
