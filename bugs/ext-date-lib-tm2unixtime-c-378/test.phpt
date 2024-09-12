--TEST--
Test array_merge_recursive() function : basic functionality - array with default keys+Conflicting constants in composing classes with different visibility modifiers should result in a fatal error, since this indicates that the code is incompatible.
--INI--
magic_quotes_gpc=1
serialize_precision=10
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
    
echo "*** Testing array_merge_recursive() : array with default keys ***\n";
// Initialise the arrays
$arr1 = array(1, array(1, 2));
$arr2 = array(3, array("hello", 'world'));
$arr3 = array(array(6, 7), array("str1", 'str2'));
// Calling array_merge_recursive() without arguments
echo "-- Without arguments --\n";
var_dump( array_merge_recursive() );
// Calling array_merge_recursive() with default arguments
echo "-- With default argument --\n";
var_dump( array_merge_recursive($arr1) );
// Calling array_merge_recursive() with more arguments
echo "-- With more arguments --\n";
var_dump( array_merge_recursive($arr1,$arr2) );
var_dump( array_merge_recursive($arr1,$arr2,$arr3) );
echo "Done";
$v1=$definedVars[array_rand($definedVars = get_defined_vars())];
trait TestTrait {
  public const Constant = 42;
}
echo "PRE-CLASS-GUARD\n";
class ComposingClass {
    use TestTrait;
    private const Constant = 42;
}
echo "POST-CLASS-GUARD\n";
$v2=$definedVars[array_rand($definedVars = get_defined_vars())];
$v3=$definedVars[array_rand($definedVars = get_defined_vars())];;
var_dump('random_var:',$v1,$v2,$v3);
var_fusion($v1,$v2,$v3);
?>
--EXPECTF--
*** Testing array_merge_recursive() : array with default keys ***
-- Without arguments --
array(0) {
}
-- With default argument --
array(2) {
  [0]=>
  int(1)
  [1]=>
  array(2) {
    [0]=>
    int(1)
    [1]=>
    int(2)
  }
}
-- With more arguments --
array(4) {
  [0]=>
  int(1)
  [1]=>
  array(2) {
    [0]=>
    int(1)
    [1]=>
    int(2)
  }
  [2]=>
  int(3)
  [3]=>
  array(2) {
    [0]=>
    string(5) "hello"
    [1]=>
    string(5) "world"
  }
}
array(6) {
  [0]=>
  int(1)
  [1]=>
  array(2) {
    [0]=>
    int(1)
    [1]=>
    int(2)
  }
  [2]=>
  int(3)
  [3]=>
  array(2) {
    [0]=>
    string(5) "hello"
    [1]=>
    string(5) "world"
  }
  [4]=>
  array(2) {
    [0]=>
    int(6)
    [1]=>
    int(7)
  }
  [5]=>
  array(2) {
    [0]=>
    string(4) "str1"
    [1]=>
    string(4) "str2"
  }
}
Done
PRE-CLASS-GUARD

Fatal error: ComposingClass and TestTrait define the same constant (Constant) in the composition of ComposingClass. However, the definition differs and is considered incompatible. Class was composed in %s on line %d
