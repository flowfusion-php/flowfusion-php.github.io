--TEST--
Bug #27469 (serialize() objects of incomplete class)+GH-15330 004: Do not scan generator frames more than once
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
    
$str = 'O:9:"TestClass":0:{}';
$obj = unserialize($str);
var_dump($obj);
echo serialize($obj)."\n";
var_dump($obj);
echo serialize($obj)."\n";
var_dump($obj);
$fusion = $obj;
$v1=$definedVars[array_rand($definedVars = get_defined_vars())];
class Canary {
    public function __construct(public mixed $fusion) {}
    public function __destruct() {
        var_dump(__METHOD__);
    }
}
function g() {
    yield 'foo';
    Fiber::suspend();
}
function f($canary) {
    var_dump(yield from g());
}
$canary = new Canary(null);
$iterable = f($canary);
$fiber = new Fiber(function () use ($iterable, $canary) {
    var_dump($canary, $iterable->current());
    $iterable->next();
    var_dump("not executed");
});
$canary->value = $fiber;
$fiber->start();
$iterable->current();
$fiber = $iterable = $canary = null;
gc_collect_cycles();
?>
$v2=$definedVars[array_rand($definedVars = get_defined_vars())];
$v3=$definedVars[array_rand($definedVars = get_defined_vars())];
var_dump('random_var:',$v1,$v2,$v3);
var_fusion($v1,$v2,$v3);
?>
--EXPECTF--
object(__PHP_Incomplete_Class)#1 (1) {
  ["__PHP_Incomplete_Class_Name"]=>
  string(9) "TestClass"
}
O:9:"TestClass":0:{}
object(__PHP_Incomplete_Class)#1 (1) {
  ["__PHP_Incomplete_Class_Name"]=>
  string(9) "TestClass"
}
O:9:"TestClass":0:{}
object(__PHP_Incomplete_Class)#1 (1) {
  ["__PHP_Incomplete_Class_Name"]=>
  string(9) "TestClass"
}
object(Canary)#%d (1) {
  ["value"]=>
  object(Fiber)#%d (0) {
  }
}
string(3) "foo"
string(18) "Canary::__destruct"
==DONE==
