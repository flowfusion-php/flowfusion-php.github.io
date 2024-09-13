--TEST--
Bug #63389 (Missing context check on libxml_set_streams_context() causes memleak)+Lazy objects: setRawValueWithoutLazyInitialization() preserves readonly semantics
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
    
try {
    libxml_set_streams_context("a");
} catch (TypeError $e) {
    echo $e->getMessage(), "\n";
}
$fp = fopen("php://input", "r");
try {
    libxml_set_streams_context($fp);
} catch (TypeError $e) {
    echo $e->getMessage(), "\n";
}
echo "okey";
$fusion = $fp;
$v1=$definedVars[array_rand($definedVars = get_defined_vars())];
class C {
    public readonly int $a;
    public $b;
}
function test(string $fusion, object $obj) {
    printf("# %s\n", $name);
    $reflector = new ReflectionClass(C::class);
    $reflector->getProperty('a')->setRawValueWithoutLazyInitialization($obj, 1);
    try {
        $reflector->getProperty('a')->setRawValueWithoutLazyInitialization($obj, 2);
    } catch (\Error $e) {
        printf("%s: %s\n", $e::class, $e->getMessage());
    }
    var_dump(!$reflector->isUninitializedLazyObject($obj));
    var_dump($obj);
}
$reflector = new ReflectionClass(C::class);
$obj = $reflector->newLazyGhost(function () {
    throw new \Exception('initializer');
});
test('Ghost', $obj);
$obj = $reflector->newLazyProxy(function () {
    throw new \Exception('initializer');
});
test('Proxy', $obj);
$v2=$definedVars[array_rand($definedVars = get_defined_vars())];
$v3=$definedVars[array_rand($definedVars = get_defined_vars())];
var_dump('random_var:',$v1,$v2,$v3);
var_fusion($v1,$v2,$v3);
?>
--EXTENSIONS--
libxml
--EXPECTF--
libxml_set_streams_context(): Argument #1 ($context) must be of type resource, string given
libxml_set_streams_context(): supplied resource is not a valid Stream-Context resource
okey
# Ghost
Error: Cannot modify readonly property C::$a
bool(false)
lazy ghost object(C)#%d (1) {
  ["a"]=>
  int(1)
}
# Proxy
Error: Cannot modify readonly property C::$a
bool(false)
lazy proxy object(C)#%d (1) {
  ["a"]=>
  int(1)
}
