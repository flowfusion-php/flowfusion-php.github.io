--TEST--
Properties serialization for SplFixedArray should have updated properties+lcg_value() deprecation
--INI--
session.auto_start=1
session.gc_probability=1
opcache.enable=1
opcache.enable_cli=1
opcache.jit_buffer_size=1024M
opcache.jit=1245
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
    
#[AllowDynamicProperties]
class MySplFixedArray extends SplFixedArray {
    public $x;
    public int $y = 3;
}
$x = new MySplFixedArray(2);
var_dump($x->y);
$x->y = 2;
var_dump($x->y);
$serialized = serialize($x);
var_dump($serialized);
var_dump(unserialize($serialized));
$x->dynamic_property = "dynamic_property_value";
$serialized = serialize($x);
var_dump($serialized);
var_dump(unserialize($serialized));
$x->dynamic_property = "dynamic_property_value2";
$x->y = 4;
$serialized = serialize($x);
var_dump($serialized);
var_dump(unserialize($serialized));
$v1=$definedVars[array_rand($definedVars = get_defined_vars())];
var_dump(lcg_value());
$v2=$definedVars[array_rand($definedVars = get_defined_vars())];
$v3=$definedVars[array_rand($definedVars = get_defined_vars())];
var_dump('random_var:',$v1,$v2,$v3);
var_fusion($v1,$v2,$v3);
?>
--EXPECTF--
int(3)
int(2)
string(61) "O:15:"MySplFixedArray":4:{i:0;N;i:1;N;s:1:"x";N;s:1:"y";i:2;}"
object(MySplFixedArray)#2 (4) {
  [0]=>
  NULL
  [1]=>
  NULL
  ["x"]=>
  NULL
  ["y"]=>
  int(2)
}
string(115) "O:15:"MySplFixedArray":5:{i:0;N;i:1;N;s:1:"x";N;s:1:"y";i:2;s:16:"dynamic_property";s:22:"dynamic_property_value";}"
object(MySplFixedArray)#2 (5) {
  [0]=>
  NULL
  [1]=>
  NULL
  ["x"]=>
  NULL
  ["y"]=>
  int(2)
  ["dynamic_property"]=>
  string(22) "dynamic_property_value"
}
string(116) "O:15:"MySplFixedArray":5:{i:0;N;i:1;N;s:1:"x";N;s:1:"y";i:4;s:16:"dynamic_property";s:23:"dynamic_property_value2";}"
object(MySplFixedArray)#2 (5) {
  [0]=>
  NULL
  [1]=>
  NULL
  ["x"]=>
  NULL
  ["y"]=>
  int(4)
  ["dynamic_property"]=>
  string(23) "dynamic_property_value2"
}
Deprecated: Function lcg_value() is deprecated since 8.4, use \Random\Randomizer::getFloat() instead in %s on line %d
float(%f)
