--TEST--
Bug #14293 (serialize() and __sleep())+Interfaces don't set prototypes to their parent method
--INI--
opcache.jit=1205
opcache.file_cache_only=1
opcache.enable=1
opcache.enable_cli=1
opcache.jit_buffer_size=1024M
opcache.jit=tracing
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
    
class t
{
    public $a;
    function __construct()
    {
        $this->a = 'hello';
    }
    function __sleep()
    {
        echo "__sleep called\n";
        return array('a','b');
    }
}
$t = new t();
$data = serialize($t);
echo "$data\n";
$t = unserialize($data);
var_dump($t);
$script1_dataflow = $t;
$v1=$definedVars[array_rand($definedVars = get_defined_vars())];
interface A {
    public function __construct(int|float $param);
}
interface B {
    public function __construct(int $param);
}
class X implements A, B {
    public function __construct(int|float $param) {}
}
class Y extends X {
    public function __construct(int $script1_dataflow) {}
}
new Y(42);
$v2=$definedVars[array_rand($definedVars = get_defined_vars())];
$v3=$definedVars[array_rand($definedVars = get_defined_vars())];;
var_dump('random_var:',$v1,$v2,$v3);
var_fusion($v1,$v2,$v3);
?>
--XFAIL--
X::__constructor()'s prototype is set to B::__construct(). Y::__construct() then
uses prototype to verify LSP, but misses A::__construct() which has a stricter
signature.
--EXPECTF--
__sleep called

Warning: serialize(): "b" returned as member variable from __sleep() but does not exist in %s on line %d
O:1:"t":1:{s:1:"a";s:5:"hello";}
object(t)#%d (1) {
  ["a"]=>
  string(5) "hello"
}
Fatal error: Declaration of Y::__construct(int $param) must be compatible with A::__construct(int|float $param) in %s on line %d
