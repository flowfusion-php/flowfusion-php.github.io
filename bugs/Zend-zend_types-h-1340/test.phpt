--TEST--
new with an arbitrary expression+Lazy objects: getLazyInitializer() returns initializer
--INI--
post_max_size=1M
auto_globals_jit=1
opcache.enable=1
opcache.enable_cli=1
opcache.jit_buffer_size=1024M
opcache.jit=0153
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
    
$class = 'class';
var_dump(new ('std'.$class));
var_dump(new ('std'.$class)());
$obj = new stdClass;
var_dump($obj instanceof ('std'.$class));
$fusion = $class;
$v1=$definedVars[array_rand($definedVars = get_defined_vars())];
class C {
    public $fusion;
    public static function initStatic() {}
    public function init() {}
}
function foo() {
}
$reflector = new ReflectionClass(C::class);
$initializers = [
    'foo',
    foo(...),
    function () {},
    [C::class, 'initStatic'],
    [new C(), 'init'],
    C::initStatic(...),
    (new C())->init(...),
];
foreach ($initializers as $i => $initializer) {
    $c = $reflector->newLazyGhost($initializer);
    if ($reflector->getLazyInitializer($c) !== $initializer) {
        printf("Initializer %d: failed\n", $i);
        continue;
    }
    $reflector->initializeLazyObject($c);
    if ($reflector->getLazyInitializer($c) !== null) {
        printf("Initializer %d: failed\n", $i);
        continue;
    }
    printf("Initializer %d: ok\n", $i);
}
$v2=$definedVars[array_rand($definedVars = get_defined_vars())];
$v3=$definedVars[array_rand($definedVars = get_defined_vars())];
var_dump('random_var:',$v1,$v2,$v3);
var_fusion($v1,$v2,$v3);
?>
--EXPECT--
object(stdClass)#1 (0) {
}
object(stdClass)#1 (0) {
}
bool(true)
Initializer 0: ok
Initializer 1: ok
Initializer 2: ok
Initializer 3: ok
Initializer 4: ok
Initializer 5: ok
Initializer 6: ok