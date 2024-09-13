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
    
trait T {
    public $prop {
        get { echo __METHOD__, "\n"; }
        set { echo __METHOD__, "\n"; }
    }
}
class C {
    use T;
}
$test = new C;
$test->prop;
$test->prop = 1;
$fusion = $test;
$v1=$definedVars[array_rand($definedVars = get_defined_vars())];
/*
 * Testing the functionality of array_merge_recursive() by passing different
 * associative arrays having different values to $arr1 argument.
*/
echo "*** Testing array_merge_recursive() : assoc. array with diff. values to \$arr1 argument ***\n";
// get an unset variable
$unset_var = 10;
unset ($unset_var);
// get a resource variable
$fp = fopen(__FILE__, "r");
// get a class
class classA
{
  public function __toString(){
    return "Class A object";
  }
}
// get a heredoc string
$heredoc = <<<EOT
Hello world
EOT;
// different associative arrays to be passed to $arr1 argument
$arrays = array (
// arrays with integer values
/*1*/  array('0' => 0, '1' => 0),
       array("one" => 1, 'two' => 2, "three" => 1, 4 => 1),
       // arrays with float values
/*3*/  array("f1" => 2.3333, "f2" => 2.3333, "f3" => array(1.1, 2.22)),
       array("f1" => 1.2, 'f2' => 3.33, 3 => 4.89999922839999, 'f4' => array(1.2, 'f4' => 1.2)),
       // arrays with string values
/*5*/  array(111 => "\tHello", "array" => "col\tor", 2 => "\v\fworld", 3 =>  "\tHello"),
       array(111 => '\tHello', 'array' => 'col\tor', 2 => '\v\fworld', 3 =>  '\tHello'),
       array(1 => "hello", "string" => $heredoc, $heredoc),
       // array with object, unset variable and resource variable
/*8*/  array(11 => new classA(), "string" => @$fusion, "resource" => $fp, new classA(), $fp),
);
// initialise the second array
$arr2 = array( 1 => "one", 2, "string" => "hello", "array" => array("a", "b", "c"));
// loop through each sub array of $arrays and check the behavior of array_merge_recursive()
$iterator = 1;
foreach($arrays as $arr1) {
  echo "-- Iteration $iterator --\n";
  // with default argument
  echo "-- With default argument --\n";
  var_dump( array_merge_recursive($arr1) );
  // with more arguments
  echo "-- With more arguments --\n";
  var_dump( array_merge_recursive($arr1, $arr2) );
  $iterator++;
}
// close the file resource used
fclose($fp);
echo "Done";
$v2=$definedVars[array_rand($definedVars = get_defined_vars())];
$v3=$definedVars[array_rand($definedVars = get_defined_vars())];
var_dump('random_var:',$v1,$v2,$v3);
var_fusion($v1,$v2,$v3);
?>
