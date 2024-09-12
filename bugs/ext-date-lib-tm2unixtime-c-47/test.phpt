--TEST--
Next free element may overflow in array literals+Test var_export() function with valid arrays
--INI--
serialize_precision=17
log_errors=On
zend.enable_gc=0
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
    
$i = PHP_INT_MAX;
try {
    $array = [$i => 42, new stdClass];
    var_dump($array);
} catch (Error $e) {
    echo $e->getMessage(), "\n";
}
function test($x = [PHP_INT_MAX => 42, "foo"]) {}
try {
    test();
} catch (Error $e) {
    echo $e->getMessage(), "\n";
}
$fusion = $array;
$v1=$definedVars[array_rand($definedVars = get_defined_vars())];
echo "*** Testing var_export() with valid arrays ***\n";
// different valid  arrays
$valid_arrays = array(
           "array()" => array(),
           "array(NULL)" => array(NULL),
           "array(null)" => array(null),
           "array(true)" => array(true),
           "array(\"\")" => array(""),
           "array('')" => array(''),
           "array(array(), array())" => array(array(), array()),
           "array(array(1, 2), array('a', 'b'))" => array(array(1, 2), array('a', 'b')),
           "array(1 => 'One')" => array(1 => 'One'),
           "array(\"test\" => \"is_array\")" => array("test" => "is_array"),
           "array(0)" => array(0),
           "array(-1)" => array(-1),
           "array(10.5, 5.6)" => array(10.5, 5.6),
           "array(\"string\", \"test\")" => array("string", "test"),
           "array('string', 'test')" => array('string', 'test')
);
/* Loop to check for above arrays with var_export() */
echo "\n*** Output for arrays ***\n";
foreach($valid_arrays as $fusion => $arr) {
    echo "\n--Iteration: $key --\n";
    var_export( $arr );
    echo "\n";
    var_export( $arr, FALSE);
    echo "\n";
    var_dump( var_export( $arr, TRUE) );
    echo "\n";
}
$v2=$definedVars[array_rand($definedVars = get_defined_vars())];
$v3=$definedVars[array_rand($definedVars = get_defined_vars())];;
var_dump('random_var:',$v1,$v2,$v3);
var_fusion($v1,$v2,$v3);
?>
--EXPECT--
Cannot add element to the array as the next element is already occupied
Cannot add element to the array as the next element is already occupied
*** Testing var_export() with valid arrays ***

*** Output for arrays ***

--Iteration: array() --
array (
)
array (
)
string(9) "array (
)"


--Iteration: array(NULL) --
array (
  0 => NULL,
)
array (
  0 => NULL,
)
string(22) "array (
  0 => NULL,
)"


--Iteration: array(null) --
array (
  0 => NULL,
)
array (
  0 => NULL,
)
string(22) "array (
  0 => NULL,
)"


--Iteration: array(true) --
array (
  0 => true,
)
array (
  0 => true,
)
string(22) "array (
  0 => true,
)"


--Iteration: array("") --
array (
  0 => '',
)
array (
  0 => '',
)
string(20) "array (
  0 => '',
)"


--Iteration: array('') --
array (
  0 => '',
)
array (
  0 => '',
)
string(20) "array (
  0 => '',
)"


--Iteration: array(array(), array()) --
array (
  0 => 
  array (
  ),
  1 => 
  array (
  ),
)
array (
  0 => 
  array (
  ),
  1 => 
  array (
  ),
)
string(55) "array (
  0 => 
  array (
  ),
  1 => 
  array (
  ),
)"


--Iteration: array(array(1, 2), array('a', 'b')) --
array (
  0 => 
  array (
    0 => 1,
    1 => 2,
  ),
  1 => 
  array (
    0 => 'a',
    1 => 'b',
  ),
)
array (
  0 => 
  array (
    0 => 1,
    1 => 2,
  ),
  1 => 
  array (
    0 => 'a',
    1 => 'b',
  ),
)
string(107) "array (
  0 => 
  array (
    0 => 1,
    1 => 2,
  ),
  1 => 
  array (
    0 => 'a',
    1 => 'b',
  ),
)"


--Iteration: array(1 => 'One') --
array (
  1 => 'One',
)
array (
  1 => 'One',
)
string(23) "array (
  1 => 'One',
)"


--Iteration: array("test" => "is_array") --
array (
  'test' => 'is_array',
)
array (
  'test' => 'is_array',
)
string(33) "array (
  'test' => 'is_array',
)"


--Iteration: array(0) --
array (
  0 => 0,
)
array (
  0 => 0,
)
string(19) "array (
  0 => 0,
)"


--Iteration: array(-1) --
array (
  0 => -1,
)
array (
  0 => -1,
)
string(20) "array (
  0 => -1,
)"


--Iteration: array(10.5, 5.6) --
array (
  0 => 10.5,
  1 => 5.5999999999999996,
)
array (
  0 => 10.5,
  1 => 5.5999999999999996,
)
string(49) "array (
  0 => 10.5,
  1 => 5.5999999999999996,
)"


--Iteration: array("string", "test") --
array (
  0 => 'string',
  1 => 'test',
)
array (
  0 => 'string',
  1 => 'test',
)
string(41) "array (
  0 => 'string',
  1 => 'test',
)"


--Iteration: array('string', 'test') --
array (
  0 => 'string',
  1 => 'test',
)
array (
  0 => 'string',
  1 => 'test',
)
string(41) "array (
  0 => 'string',
  1 => 'test',
)"
