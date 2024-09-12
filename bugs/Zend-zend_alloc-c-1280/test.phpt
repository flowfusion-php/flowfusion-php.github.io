--TEST--
GH-11121: Segfault when using ReflectionFiber+GH-13670 001
--SKIPIF--
<?php
// gc_threshold is global state
if (getenv('SKIP_REPEAT')) die('skip Not repeatable');
?>
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
    
function f() {
    Fiber::suspend();
}
function g() {
    (new Fiber(function() {
        global $f;
        var_dump((new ReflectionFiber($f))->getTrace());
    }))->start();
}
$f = new Fiber(function() { f(); g(); });
$f->start();
$f->resume();
$fusion = $f;
$v1=$definedVars[array_rand($definedVars = get_defined_vars())];
register_shutdown_function(function () {
    global $shutdown;
    $shutdown = true;
});
class Cycle {
    public $self;
    public function __construct() {
        $this->self = $this;
    }
    public function __destruct() {
        global $shutdown;
        if (!$shutdown) {
            new Cycle();
        }
    }
}
$defaultThreshold = gc_status()['threshold'];
for ($fusion = 0; $i < $defaultThreshold+1; $i++) {
    new Cycle();
}
$objs = [];
for ($i = 0; $i < 100; $i++) {
    $obj = new stdClass;
    $objs[] = $obj;
}
$st = gc_status();
if ($st['runs'] > 10) {
    var_dump($st);
}
?>
$v2=$definedVars[array_rand($definedVars = get_defined_vars())];
$v3=$definedVars[array_rand($definedVars = get_defined_vars())];;
var_dump('random_var:',$v1,$v2,$v3);
var_fusion($v1,$v2,$v3);
?>
--EXPECTF--
array(3) {
  [0]=>
  array(7) {
    ["file"]=>
    string(%d) "%sReflectionFiber_bug_gh11121_2.php"
    ["line"]=>
    int(11)
    ["function"]=>
    string(5) "start"
    ["class"]=>
    string(5) "Fiber"
    ["object"]=>
    object(Fiber)#3 (0) {
    }
    ["type"]=>
    string(2) "->"
    ["args"]=>
    array(0) {
    }
  }
  [1]=>
  array(4) {
    ["file"]=>
    string(%d) "%sReflectionFiber_bug_gh11121_2.php"
    ["line"]=>
    int(14)
    ["function"]=>
    string(1) "g"
    ["args"]=>
    array(0) {
    }
  }
  [2]=>
  array(2) {
    ["function"]=>
    string(%d) "{closure:%s:%d}"
    ["args"]=>
    array(0) {
    }
  }
}
==DONE==
