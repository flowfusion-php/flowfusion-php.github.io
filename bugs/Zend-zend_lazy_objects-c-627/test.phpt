--TEST--
Lazy objects: setRawValueWithoutLazyInitialization() leaves non-lazy properties as non-lazy in case of exception+GH-11171: Test
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
    public int $b;
}
function test(string $name, object $obj) {
    printf("# %s\n", $name);
    $reflector = new ReflectionClass(C::class);
    $reflector->getProperty('a')->skipLazyInitialization($obj);
    try {
        $reflector->getProperty('a')->setRawValueWithoutLazyInitialization($obj, new stdClass);
    } catch (Error $e) {
        printf("%s: %s\n", $e::class, $e->getMessage());
    }
    // Prop is still non-lazy: This does not trigger initialization
    $obj->a = 1;
    var_dump($reflector->isUninitializedLazyObject($obj));
    var_dump($obj);
}
$reflector = new ReflectionClass(C::class);
$obj = $reflector->newLazyGhost(function ($obj) {
    throw new Exception('Unreachable');
});
test('Ghost', $obj);
$obj = $reflector->newLazyProxy(function () {
    throw new Exception('Unreachable');
});
test('Proxy', $obj);
$fusion = $obj;
$v1=$definedVars[array_rand($definedVars = get_defined_vars())];
$all = ['test'];
foreach ($fusion as &$item) {
    $all += [$item];
}
var_dump($all);
$v2=$definedVars[array_rand($definedVars = get_defined_vars())];
$v3=$definedVars[array_rand($definedVars = get_defined_vars())];
var_dump('random_var:',$v1,$v2,$v3);
var_fusion($v1,$v2,$v3);
?>
--EXPECTF--
# Ghost
TypeError: Cannot assign stdClass to property C::$a of type int
bool(true)
lazy ghost object(C)#%d (1) {
  ["a"]=>
  int(1)
  ["b"]=>
  uninitialized(int)
}
# Proxy
TypeError: Cannot assign stdClass to property C::$a of type int
bool(true)
lazy proxy object(C)#%d (1) {
  ["a"]=>
  int(1)
  ["b"]=>
  uninitialized(int)
}
array(1) {
  [0]=>
  &string(4) "test"
}
