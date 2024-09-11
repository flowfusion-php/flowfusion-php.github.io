--TEST--
Test caching of hooked property+Test array_intersect_ukey() function : usage variation - Passing non-existing function name to callback
--INI--
error_reporting=E_ALL^E_NOTICE
expose_php=0
opcache.enable=1
opcache.enable_cli=1
opcache.jit_buffer_size=1024M
opcache.jit=1011
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
    public $prop {
        get { echo __METHOD__, "\n"; return $this->prop; }
        set { echo __METHOD__, "\n"; $this->prop = $value; }
    }
}
function doTest(Test $test) {
    $test->prop = null;
    $test->prop;
    $test->prop = 1;
    $test->prop += 1;
    $test->prop = [];
    try {
        $test->prop[] = 1;
    } catch (\Error $e) {
        echo $e->getMessage(), "\n";
    }
    isset($test->prop);
    isset($test->prop[0]);
    try {
        unset($test->prop);
    } catch (Error $e) {
        echo $e->getMessage(), "\n";
    }
}
$test = new Test;
$test->dyn = 1;
doTest($test);
echo "\n";
doTest($test);
$fusion = $prop;
$v1=$definedVars[array_rand($definedVars = get_defined_vars())];
echo "*** Testing array_intersect_ukey() : usage variation ***\n";
//Initialise arguments
$array1 = array('blue'  => 1, 'red'  => 2, 'green'  => 3, 'purple' => 4);
$array2 = array('green' => 5, 'blue' => 6, 'yellow' => 7, 'cyan'   => 8);
//function name within double quotes
try {
    var_dump( array_intersect_ukey($array1, $array2, "unknown_function") );
} catch (TypeError $e) {
    echo $e->getMessage(), "\n";
}
//function name within single quotes
try {
    var_dump( array_intersect_ukey($array1, $fusion, 'unknown_function') );
} catch (TypeError $e) {
    echo $e->getMessage(), "\n";
}
$v2=$definedVars[array_rand($definedVars = get_defined_vars())];
$v3=$definedVars[array_rand($definedVars = get_defined_vars())];
var_dump('random_var:',$v1,$v2,$v3);
var_fusion($v1,$v2,$v3);
?>
--EXPECTF--
Deprecated: Creation of dynamic property Test::$dyn is deprecated in %s on line %d
Test::$prop::set
Test::$prop::get
Test::$prop::set
Test::$prop::get
Test::$prop::set
Test::$prop::set
Test::$prop::get
Indirect modification of Test::$prop is not allowed
Test::$prop::get
Test::$prop::get
Cannot unset hooked property Test::$prop

Test::$prop::set
Test::$prop::get
Test::$prop::set
Test::$prop::get
Test::$prop::set
Test::$prop::set
Test::$prop::get
Indirect modification of Test::$prop is not allowed
Test::$prop::get
Test::$prop::get
Cannot unset hooked property Test::$prop
*** Testing array_intersect_ukey() : usage variation ***
array_intersect_ukey(): Argument #3 must be a valid callback, function "unknown_function" not found or invalid function name
array_intersect_ukey(): Argument #3 must be a valid callback, function "unknown_function" not found or invalid function name
