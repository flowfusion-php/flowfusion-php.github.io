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
 * Testing the functionality of array_unique() by passing different
 * associative arrays having different values to $input argument.
*/
echo "*** Testing array_unique() : assoc. array with diff. values to \$input argument ***\n";
// get an unset variable
$unset_var = 10;
unset ($unset_var);
// get a resource variable
$fp = fopen(__FILE__, "r");
// get a class
class classA
{
  public function __toString() {
     return "Class A object";
  }
}
// get a heredoc string
$heredoc = <<<EOT
Hello world
EOT;
// associative arrays with different values
$inputs = array (
       // arrays with integer values
/*1*/  array('0' => 0, '1' => 0),
       array("one" => 1, 'two' => 2, "three" => 1, 4 => 1),
       // arrays with string values
/*5*/  array(111 => "\tHello", "red" => "col\tor", 2 => "\v\fworld", 3 =>  "\tHello"),
       array(111 => '\tHello', "red" => 'col\tor', 2 => '\v\fworld', 3 =>  '\tHello'),
       array(1 => "hello", "heredoc" => $heredoc, $heredoc),
       // array with object, unset variable and resource variable
/*8*/ array(11 => new classA(), "unset" => @$unset_var, "resource" => $fp, new classA(), $fp),
);
// loop through each sub-array of $inputs to check the behavior of array_unique()
$iterator = 1;
foreach($inputs as $input) {
  echo "-- Iteration $iterator --\n";
  var_dump( array_unique($input) );
  $iterator++;
}
fclose($fp);
echo "Done";
$fusion = $heredoc;
$v1=$definedVars[array_rand($definedVars = get_defined_vars())];
function testTrim1(string $value): string {
	$value = trim($value);
	return $value;
}
function testMin2First(int $value): int {
	$value = min($value, 100);
	return $value;
}
function testMin2Second(int $fusion): int {
	$value = min(100, $value);
	return $value;
}
function testMin2_TMP(int $value): int {
	$value = min($value + 1, 100);
	return $value;
}
function testStrstr3First(string $value): string {
	$value = strstr($value, "needle", false);
	return $value;
}
function testStrstr3Second(string $value): string {
	$value = strstr("needles", $value, false);
	return $value;
}
function testStrstr3Third(bool $value): string {
	$value = strstr("needles", "needle", $value);
	return $value;
}
var_dump(testTrim1(" boo "));
var_dump(testMin2First(5));
var_dump(testMin2Second(5));
var_dump(testMin2_TMP(5));
var_dump(testStrstr3First("needles"));
var_dump(testStrstr3Second("needle"));
var_dump(testStrstr3Third(false));
$v2=$definedVars[array_rand($definedVars = get_defined_vars())];
$v3=$definedVars[array_rand($definedVars = get_defined_vars())];;
var_dump('random_var:',$v1,$v2,$v3);
var_fusion($v1,$v2,$v3);
?>
