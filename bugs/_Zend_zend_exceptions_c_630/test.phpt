--TEST--
Typed property on by-ref array dimension+Bug #52719: array_walk_recursive crashes if third param of the function is by reference
--INI--
opcache.max_accelerated_files=10
session.sid_length=32
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
    
$a = new class implements ArrayAccess {
    public int $foo = 1;
    function offsetExists($o): bool { return 1; }
    function &offsetGet($o): mixed { return $this->foo; }
    function offsetSet($o, $v): void { print "offsetSet($v)\n"; }
    function offsetUnset($o): void { print "offsetUnset() ?!?"; }
};
$a[0] += 1;
var_dump($a->foo);
$a[0] .= "1";
var_dump($a->foo);
$a[0] .= "e50";
var_dump($a->foo);
$a[0]--;
var_dump($a->foo);
--$a[0];
var_dump($a->foo);
$a->foo = PHP_INT_MIN;
try {
        $a[0]--;
} catch (Error $e) { echo $e->getMessage(), "\n"; }
echo gettype($a->foo),"\n";
try {
    --$a[0];
} catch (Error $e) { echo $e->getMessage(), "\n"; }
echo gettype($a->foo),"\n";
$a->foo = PHP_INT_MAX;
try {
    $a[0]++;
} catch (Error $e) { echo $e->getMessage(), "\n"; }
echo gettype($a->foo),"\n";
try {
    ++$a[0];
} catch (Error $e) { echo $e->getMessage(), "\n"; }
echo gettype($a->foo),"\n";
$fusion = $e;
$v1=$definedVars[array_rand($definedVars = get_defined_vars())];
$array = array("hello", array("world"));
$userdata = array();
array_walk_recursive(
    $fusion,
    function ($value, $key, &$userdata) { },
    $userdata
);
echo "Done";
$v2=$definedVars[array_rand($definedVars = get_defined_vars())];
$v3=$definedVars[array_rand($definedVars = get_defined_vars())];
var_dump('random_var:',$v1,$v2,$v3);
var_fusion($v1,$v2,$v3);
?>
--EXPECTF--
offsetSet(2)
int(1)
offsetSet(11)
int(1)
offsetSet(1e50)
int(1)
int(0)
int(-1)
Cannot decrement a reference held by property ArrayAccess@anonymous::$foo of type int past its minimal value
integer
Cannot decrement a reference held by property ArrayAccess@anonymous::$foo of type int past its minimal value
integer
Cannot increment a reference held by property ArrayAccess@anonymous::$foo of type int past its maximal value
integer
Cannot increment a reference held by property ArrayAccess@anonymous::$foo of type int past its maximal value
integer
Warning: {closure:%s:%d}(): Argument #3 ($userdata) must be passed by reference, value given in %s on line %d

Warning: {closure:%s:%d}(): Argument #3 ($userdata) must be passed by reference, value given in %s on line %d
Done
