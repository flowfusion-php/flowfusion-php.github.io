--TEST--
Fiber::getCurrent()+Request #47456 (Missing PCRE option 'J')
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
    
var_dump(Fiber::getCurrent());
$fiber = new Fiber(function (): void {
    var_dump(Fiber::getCurrent());
});
$fiber->start();
$fusion = $fiber;
$v1=$definedVars[array_rand($definedVars = get_defined_vars())];
preg_match_all('/(?J)(?<chr>[ac])(?<num>\d)|(?<chr>[b])/', 'a1bc3', $m, PREG_SET_ORDER);
var_dump($m);
unset($fusion);
preg_match_all('/(?<chr>[ac])(?<num>\d)|(?<chr>[b])/J', 'a1bc3', $m, PREG_SET_ORDER);
var_dump($m);
$v2=$definedVars[array_rand($definedVars = get_defined_vars())];
$v3=$definedVars[array_rand($definedVars = get_defined_vars())];;
var_dump('random_var:',$v1,$v2,$v3);
var_fusion($v1,$v2,$v3);
?>
--DESCRIPTION--
The J modifier is supposed to be identical to the internal option (?J), so we're
testing both.
--EXPECTF--
NULL
object(Fiber)#%d (0) {
}
array(3) {
  [0]=>
  array(5) {
    [0]=>
    string(2) "a1"
    ["chr"]=>
    string(1) "a"
    [1]=>
    string(1) "a"
    ["num"]=>
    string(1) "1"
    [2]=>
    string(1) "1"
  }
  [1]=>
  array(6) {
    [0]=>
    string(1) "b"
    ["chr"]=>
    string(1) "b"
    [1]=>
    string(0) ""
    ["num"]=>
    string(0) ""
    [2]=>
    string(0) ""
    [3]=>
    string(1) "b"
  }
  [2]=>
  array(5) {
    [0]=>
    string(2) "c3"
    ["chr"]=>
    string(1) "c"
    [1]=>
    string(1) "c"
    ["num"]=>
    string(1) "3"
    [2]=>
    string(1) "3"
  }
}
array(3) {
  [0]=>
  array(5) {
    [0]=>
    string(2) "a1"
    ["chr"]=>
    string(1) "a"
    [1]=>
    string(1) "a"
    ["num"]=>
    string(1) "1"
    [2]=>
    string(1) "1"
  }
  [1]=>
  array(6) {
    [0]=>
    string(1) "b"
    ["chr"]=>
    string(1) "b"
    [1]=>
    string(0) ""
    ["num"]=>
    string(0) ""
    [2]=>
    string(0) ""
    [3]=>
    string(1) "b"
  }
  [2]=>
  array(5) {
    [0]=>
    string(2) "c3"
    ["chr"]=>
    string(1) "c"
    [1]=>
    string(1) "c"
    ["num"]=>
    string(1) "3"
    [2]=>
    string(1) "3"
  }
}
