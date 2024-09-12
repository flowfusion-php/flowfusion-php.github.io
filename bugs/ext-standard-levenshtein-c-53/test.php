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
    
$values = [
    -PHP_INT_MAX-1,
    (string)(-PHP_INT_MAX-1),
];
foreach ($values as $var) {
    $var--;
    var_dump($var);
}
echo "Done\n";
$v1=$definedVars[array_rand($definedVars = get_defined_vars())];
/*
 * testing array_fill() by passing different types of array  values for 'val' argument
 */
echo "*** Testing array_fill() : usage variations ***\n";
// Initialise function arguments not being substituted
$start_key = 0;
$num = 2;
//array of different types of array values for 'val' argument
$values = array(
  /* 1  */  array(),
            array(1 , 2 , 3 , 4),
            array(1 => "Hi" , 2 => "Hello"),
            array("Saffron" , "White" , "Green"),
  /* 5  */  array('color' => 'red' , 'item' => 'pen'),
            array( 'color' => 'red' , 2 => 'green ' ),
            array("colour" => "red" , "item" => "pen"),
            array( TRUE => "red" , FALSE => "green" ),
            array( true => "red" , FALSE => "green" ),
  /* 10 */  array( 1 => "Hi" , "color" => "red" , 'item' => 'pen'),
            array( NULL => "Hi", '1' => "Hello" , "1" => "Green"),
            array( ""=>1, "color" => "green"),
  /* 13 */  array('Saffron' , 'White' , 'Green')
);
// loop through each element of the values array for 'val' argument
// check the working of array_fill()
echo "--- Testing array_fill() with different types of array values for 'val' argument ---\n";
$counter = 1;
for($i = 0; $i < count($values); $i++)
{
  echo "-- Iteration $counter --\n";
  $val = $values[$i];
  var_dump( array_fill($start_key , $num , $val) );
  $counter++;
}
echo "Done";
$v2=$definedVars[array_rand($definedVars = get_defined_vars())];
$v3=$definedVars[array_rand($definedVars = get_defined_vars())];;
var_dump('random_var:',$v1,$v2,$v3);
var_fusion($v1,$v2,$v3);
?>
