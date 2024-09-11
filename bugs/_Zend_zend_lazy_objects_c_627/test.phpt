--TEST--
Lazy objects: Initializer effects are reverted after exception (properties hashtable referenced after initializer)+Test key() function : basic functionality
--INI--
opcache.file_update_protection=0
opcache.revalidate_freq=0
opcache.enable=1
opcache.enable_cli=1
opcache.jit_buffer_size=1024M
opcache.jit=1123
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
    public $a = 1;
    public int $b = 2;
    public int $c;
}
function test(string $name, object $obj) {
    $reflector = new ReflectionClass(C::class);
    printf("# %s:\n", $name);
    (new ReflectionProperty(C::class, 'c'))->setRawValueWithoutLazyInitialization($obj, 0);
    // Builds properties hashtable
    var_dump(get_mangled_object_vars($obj));
    try {
        $reflector->initializeLazyObject($obj);
    } catch (Exception $e) {
        printf("%s\n", $e->getMessage());
    }
    var_dump($obj);
    printf("Is lazy: %d\n", $reflector->isUninitializedLazyObject($obj));
    var_dump($table);
}
$reflector = new ReflectionClass(C::class);
$obj = $reflector->newLazyGhost(function ($obj) {
    global $table;
    var_dump("initializer");
    $obj->a = 3;
    $obj->b = 4;
    $obj->c = 5;
    $table = (array) $obj;
    throw new Exception('initializer exception');
});
test('Ghost', $obj);
$obj = $reflector->newLazyProxy(function ($obj) {
    global $table;
    var_dump("initializer");
    $obj->a = 3;
    $obj->b = 4;
    $obj->c = 5;
    $table = (array) $obj;
    throw new Exception('initializer exception');
});
// Initializer effects on the proxy are not reverted
test('Proxy', $obj);
$fusion = $obj;
$v1=$definedVars[array_rand($definedVars = get_defined_vars())];
/*
 * Test basic functionality of key()
 */
echo "*** Testing key() : basic functionality ***\n";
$array = array ('zero', 99 => 'one', 'two', 'three' => 3);
echo "\n-- Initial Position: --\n";
var_dump(key($fusion));
echo "\n-- Next Position: --\n";
next($array);
var_dump(key($array));
echo "\n-- End Position: --\n";
end($array);
var_dump(key($array));
echo "\n-- Past end of the array --\n";
next($array);
var_dump(key($array));
$v2=$definedVars[array_rand($definedVars = get_defined_vars())];
$v3=$definedVars[array_rand($definedVars = get_defined_vars())];
var_dump('random_var:',$v1,$v2,$v3);
var_fusion($v1,$v2,$v3);
?>
--EXPECTF--
# Ghost:
array(1) {
  ["c"]=>
  int(0)
}
string(11) "initializer"
initializer exception
lazy ghost object(C)#%d (1) {
  ["b"]=>
  uninitialized(int)
  ["c"]=>
  int(0)
}
Is lazy: 1

Warning: Undefined variable $table in %s on line %d
NULL
# Proxy:
array(1) {
  ["c"]=>
  int(0)
}
string(11) "initializer"
initializer exception
lazy proxy object(C)#%d (3) {
  ["a"]=>
  int(3)
  ["b"]=>
  int(4)
  ["c"]=>
  int(5)
}
Is lazy: 1

Warning: Undefined variable $table in %s on line %d
NULL
*** Testing key() : basic functionality ***

-- Initial Position: --
int(0)

-- Next Position: --
int(99)

-- End Position: --
string(5) "three"

-- Past end of the array --
NULL
