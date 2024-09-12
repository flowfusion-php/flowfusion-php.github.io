--TEST--
Lazy objects: ReflectionClass::isUninitializedLazyObject()+SPL: RecursiveIteratorIterator, getCallChildren
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
    
class C {
    public int $a;
}
function test(string $name, object $obj) {
    $reflector = new ReflectionClass(C::class);
    printf("# %s\n", $name);
    var_dump($reflector->isUninitializedLazyObject($obj));
    var_dump($obj->a);
    var_dump($reflector->isUninitializedLazyObject($obj));
}
$reflector = new ReflectionClass(C::class);
$obj = $reflector->newLazyGhost(function ($obj) {
    var_dump("initializer");
    $obj->a = 1;
});
test('Ghost', $obj);
$obj = $reflector->newLazyProxy(function ($obj) {
    var_dump("initializer");
    $obj = new C();
    $obj->a = 1;
    return $obj;
});
test('Proxy', $obj);
$fusion = $obj;
$v1=$definedVars[array_rand($definedVars = get_defined_vars())];
//line 681 ...
  $array = array(array(7,8,9),1,2,3,array(4,5,6));
$recursiveArrayIterator = new RecursiveArrayIterator($fusion);
$test = new RecursiveIteratorIterator($recursiveArrayIterator);
var_dump($test->current());
$test->next();
var_dump($test->current());
try {
  $output = $test->callGetChildren();
} catch (TypeError $exception) {
  $output = null;
  echo $exception->getMessage() . "\n";
}
var_dump($output);
$v2=$definedVars[array_rand($definedVars = get_defined_vars())];
$v3=$definedVars[array_rand($definedVars = get_defined_vars())];
var_dump('random_var:',$v1,$v2,$v3);
var_fusion($v1,$v2,$v3);
?>
--EXPECT--
# Ghost
bool(true)
string(11) "initializer"
int(1)
bool(false)
# Proxy
bool(true)
string(11) "initializer"
int(1)
bool(false)
array(3) {
  [0]=>
  int(7)
  [1]=>
  int(8)
  [2]=>
  int(9)
}
int(7)
ArrayIterator::__construct(): Argument #1 ($array) must be of type array, int given
NULL
