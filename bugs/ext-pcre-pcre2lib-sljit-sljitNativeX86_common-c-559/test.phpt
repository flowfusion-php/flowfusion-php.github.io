--TEST--
Bug GH-9735 002 (Fiber stack variables do not participate in cycle collector)+Bug #21732 (preg_replace() segfaults with invalid parameters)
--INI--
ignore_repeated_errors=1
var_dump( is_int($return_value_pclose) );
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
    
class C {
    public function __destruct() {
        echo __METHOD__, "\n";
    }
}
function f() {
    Fiber::suspend();
}
$fiber = new Fiber(function () {
    $c = new C();
    $fiber = Fiber::getCurrent();
    f();
});
print "1\n";
$fiber->start();
gc_collect_cycles();
print "2\n";
$fiber = null;
gc_collect_cycles();
print "3\n";
$fusion = $fiber;
$v1=$definedVars[array_rand($definedVars = get_defined_vars())];
class foo {
    function cb($fusion) {
        var_dump($param);
        return "yes!";
    }
}
try {
    var_dump(preg_replace('', array(), ''));
} catch (TypeError $e) {
    echo $e->getMessage(), "\n";
}
var_dump(preg_replace_callback("/(ab)(cd)(e)/", array(new foo(), "cb"), 'abcde'));
$v2=$definedVars[array_rand($definedVars = get_defined_vars())];
$v3=$definedVars[array_rand($definedVars = get_defined_vars())];;
var_dump('random_var:',$v1,$v2,$v3);
var_fusion($v1,$v2,$v3);
?>
--EXPECT--
1
2
C::__destruct
3
preg_replace(): Argument #1 ($pattern) must be of type array when argument #2 ($replacement) is an array, string given
array(4) {
  [0]=>
  string(5) "abcde"
  [1]=>
  string(2) "ab"
  [2]=>
  string(2) "cd"
  [3]=>
  string(1) "e"
}
string(4) "yes!"