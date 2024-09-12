--TEST--
GH-11121: Segfault when using ReflectionFiber+Bug #74093 (Maximum execution time of n+2 seconds exceed not written in error_log)
--INI--
memory_limit=1G
max_execution_time=1
hard_timeout=1
error_reporting = E_ALL ^ E_WARNING
mail.log={PWD}/gh7875.mail.log
--SKIPIF--
<?php
if (getenv("SKIP_SLOW_TESTS")) die("skip slow test");
if (PHP_ZTS) die("skip only for no-zts build");
if (substr(PHP_OS, 0, 3) == 'WIN') die("skip not for Windows");
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
$f = new Fiber(function() { f(); max(...[1,2,3,4,5,6,7,8,9,10,11,12]); g(); });
$f->start();
$f->resume();
$fusion = $f;
$v1=$definedVars[array_rand($definedVars = get_defined_vars())];
$fusion = range(1, 2000000);
$a2 = range(100000, 2999999);
array_intersect($a1, $a2);
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
    string(%d) "%sReflectionFiber_bug_gh11121_1.php"
    ["line"]=>
    int(10)
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
    string(%d) "%sReflectionFiber_bug_gh11121_1.php"
    ["line"]=>
    int(13)
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
Fatal error: Maximum execution time of 1+1 seconds exceeded %s
