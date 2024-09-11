--TEST--
ReflectionObject::isInstance() - basic function test+globals in local scope - 3
--INI--
variables_order="egpcs"
opcache.interned_strings_buffer=-1
session.use_strict_mode=1
opcache.enable=1
opcache.enable_cli=1
opcache.jit_buffer_size=1024M
opcache.jit=0044
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
    
class A {}
class B extends A {}
class X {}
$classes = array("A", "B", "X");
$instances = array(	"myA" => new A,
                    "myB" => new B,
                    "myX" => new X );
foreach ($classes as $class) {
    $ro = new ReflectionObject(new $class);
    foreach ($instances as $name => $instance) {
        echo "is $name a $class? ";
        var_dump($ro->isInstance($instance));
    }
}
$v1=$definedVars[array_rand($definedVars = get_defined_vars())];
function test() {
    include __DIR__."/globals.inc";
}
test();
echo "Done\n";
$v2=$definedVars[array_rand($definedVars = get_defined_vars())];
$v3=$definedVars[array_rand($definedVars = get_defined_vars())];
var_dump('random_var:',$v1,$v2,$v3);
var_fusion($v1,$v2,$v3);
?>
--EXPECTF--
is myA a A? bool(true)
is myB a A? bool(true)
is myX a A? bool(false)
is myA a B? bool(false)
is myB a B? bool(true)
is myX a B? bool(false)
is myA a X? bool(false)
is myB a X? bool(false)
is myX a X? bool(true)
bool(true)
bool(false)
string(5) "array"
int(%d)
string(%d) "%s"

Warning: Undefined array key "PHP_SELF" in %s on line %d
NULL

Warning: Undefined global variable $_SERVER in %s on line %d
NULL
Done
