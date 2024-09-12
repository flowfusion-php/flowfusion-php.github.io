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
    
// Note: basic use cases in Zend/tests/is_a.phpt
spl_autoload_register(function ($className) {
    echo "In autoload($className)\n";
});
function test_error_handler($err_no, $err_msg, $filename, $linenum) {
    echo "Error: $err_no - $err_msg\n";
}
set_error_handler('test_error_handler');
echo "*** Testing is_subclass_of() : usage variations ***\n";
// Initialise function arguments not being substituted (if any)
$class_name = 'stdClass';
//get an unset variable
$unset_var = 10;
unset ($unset_var);
//array of values to iterate over
$values = array(
      // int data
      0,
      1,
      12345,
      -2345,
      // float data
      10.5,
      -10.5,
      10.1234567e10,
      10.7654321E-10,
      .5,
      // array data
      array(),
      array(0),
      array(1),
      array(1, 2),
      array('color' => 'red', 'item' => 'pen'),
      // null data
      NULL,
      null,
      // boolean data
      true,
      false,
      TRUE,
      FALSE,
      // empty data
      "",
      '',
      // string data
      "string",
      'String',
      // undefined data
      $undefined_var,
      // unset data
      $unset_var,
);
// loop through each element of the array for object
foreach($values as $value) {
      echo "\nArg value $value \n";
      var_dump( is_subclass_of($value, $class_name) );
};
echo "Done";
$v1=$definedVars[array_rand($definedVars = get_defined_vars())];
echo "*** Test by calling method or function with deprecated option ***\n";
var_dump(get_cfg_var( 'magic_quotes_gpc' ) );
$v2=$definedVars[array_rand($definedVars = get_defined_vars())];
$v3=$definedVars[array_rand($definedVars = get_defined_vars())];;
var_dump('random_var:',$v1,$v2,$v3);
var_fusion($v1,$v2,$v3);
?>
