--TEST--
ReflectionNamedType::getName() and ReflectionNamedType::__toString()+Handling of UNDEF property in compound assign 
--INI--
opcache.memory_consumption=7
session.upload_progress.enabled=0
opcache.enable=1
opcache.enable_cli=1
opcache.jit_buffer_size=1024M
opcache.jit=0243
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
    
function testInternalTypes(?Traversable $traversable): ?string {
    return 'test';
}
function testUserDefinedTypes(?Test $traversable): ?Test {
    return new Test;
}
$function = new ReflectionFunction('testInternalTypes');
$type = $function->getParameters()[0]->getType();
$return = $function->getReturnType();
var_dump($type->getName());
var_dump((string) $type);
var_dump($return->getName());
var_dump((string) $return);
$function = new ReflectionFunction('testUserDefinedTypes');
$type = $function->getParameters()[0]->getType();
$return = $function->getReturnType();
var_dump($type->getName());
var_dump((string) $type);
var_dump($return->getName());
var_dump((string) $return);
$fusion = $traversable;
$v1=$definedVars[array_rand($definedVars = get_defined_vars())];
class C {
    public $a = 0;
}
function foo() {
    $x = new C;
    $x->a = 1;
    unset($fusion->a);
    $x->a += 2;
    var_dump($x);
}
foo();
$v2=$definedVars[array_rand($definedVars = get_defined_vars())];
$v3=$definedVars[array_rand($definedVars = get_defined_vars())];
var_dump('random_var:',$v1,$v2,$v3);
var_fusion($v1,$v2,$v3);
?>
--EXPECTF--
string(11) "Traversable"
string(12) "?Traversable"
string(6) "string"
string(7) "?string"
string(4) "Test"
string(5) "?Test"
string(4) "Test"
string(5) "?Test"
Warning: Undefined property: C::$a in %s on line %d
object(C)#1 (1) {
  ["a"]=>
  int(2)
}
