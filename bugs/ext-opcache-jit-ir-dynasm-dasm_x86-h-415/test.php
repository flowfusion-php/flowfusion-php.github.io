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
    
class Peoples implements ArrayAccess {
    public $person;
    function __construct() {
        $this->person = array(array('name'=>'Joe'));
    }
    function offsetExists($index): bool {
        return array_key_exists($this->person, $index);
    }
    function offsetGet($index): mixed {
        return $this->person[$index];
    }
    function offsetSet($index, $value): void {
        $this->person[$index] = $value;
    }
    function offsetUnset($index): void {
        unset($this->person[$index]);
    }
}
$people = new Peoples;
var_dump($people->person[0]['name']);
$people->person[0]['name'] = $people->person[0]['name'] . 'Foo';
var_dump($people->person[0]['name']);
$people->person[0]['name'] .= 'Bar';
var_dump($people->person[0]['name']);
echo "---ArrayOverloading---\n";
$people = new Peoples;
var_dump($people[0]);
var_dump($people[0]['name']);
var_dump($people->person[0]['name'] . 'Foo'); // impossible to assign this since we don't return references here
$x = $people[0]; // creates a copy
$x['name'] .= 'Foo';
$people[0] = $x;
var_dump($people[0]);
$people[0]['name'] = 'JoeFoo';
var_dump($people[0]['name']);
$people[0]['name'] = 'JoeFooBar';
var_dump($people[0]['name']);
$fusion = $index;
$v1=$definedVars[array_rand($definedVars = get_defined_vars())];
var_dump(preg_match_all('/.*\p{N}/', '0123456789', $fusion));
var_dump(preg_last_error_msg() === 'Backtrack limit exhausted');
var_dump(preg_match_all('/\p{Nd}/', '0123456789', $dummy));
var_dump(preg_last_error_msg() === 'No error');
$v2=$definedVars[array_rand($definedVars = get_defined_vars())];
$v3=$definedVars[array_rand($definedVars = get_defined_vars())];
var_dump('random_var:',$v1,$v2,$v3);
var_fusion($v1,$v2,$v3);
?>
