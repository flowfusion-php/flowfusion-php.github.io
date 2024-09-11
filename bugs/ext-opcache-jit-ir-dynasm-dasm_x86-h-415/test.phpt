--TEST--
assert() - variation  - test callback options using ini_get/ini_set/assert_options+Backed prop satisfies interface get hook by-reference
--INI--
assert.active = 1
assert.warning = 0
assert.callback = f1
assert.bail = 0
assert.exception=0
serialize_precision=75
allow_url_include=0
opcache.enable=1
opcache.enable_cli=1
opcache.jit_buffer_size=1024M
opcache.jit=1004
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
    
function f1()
{
    echo "f1 called\n";
}
function f2()
{
    echo "f2 called\n";
}
function f3()
{
    echo "f3 called\n";
}
class c1
{
    static function assert($file, $line, $unused, $desc)
    {
        echo "Class assertion failed $line, \"$desc\"\n";
    }
}
echo "Initial values: assert_options(ASSERT_CALLBACK) => [".assert_options(ASSERT_CALLBACK)."]\n";
echo "Initial values: ini.get(\"assert.callback\") => [".ini_get("assert.callback")."]\n";
var_dump($r2=assert(0 != 0));
echo"\n";
echo "Change callback function using ini.set and test return value\n";
var_dump($rv = ini_set("assert.callback","f2"));
echo "assert_options(ASSERT_CALLBACK) => [".assert_options(ASSERT_CALLBACK)."]\n";
echo "ini.get(\"assert.callback\") => [".ini_get("assert.callback")."]\n";
var_dump($r2=assert(0 != 0));
echo"\n";
echo "Change callback function using assert_options and test return value\n";
var_dump($rv=assert_options(ASSERT_CALLBACK, "f3"));
echo "assert_options(ASSERT_CALLBACK) => [".assert_options(ASSERT_CALLBACK)."]\n";
echo "ini.get(\"assert.callback\") => [".ini_get("assert.callback")."]\n";
var_dump($r2=assert(0 != 0));
echo"\n";
echo "Reset the name of the callback routine to a class method\n";
var_dump($rc=assert_options(ASSERT_CALLBACK, "c1"));
echo "assert_options(ASSERT_CALLBACK) => [".assert_options(ASSERT_CALLBACK)."]\n";
echo "ini.get(\"assert.callback\") => [".ini_get("assert.callback")."]\n";
try {
    var_dump($r2=assert(0 != 0));
} catch (Error $e) {
    echo $e->getMessage(), "\n";
}
echo"\n";
echo "Reset callback options to use a class method\n";
var_dump($rc = assert_options(ASSERT_CALLBACK,array("c1","assert")));
var_dump($rao=assert_options(ASSERT_CALLBACK));
echo "ini.get(\"assert.callback\") => [".ini_get("assert.callback")."]\n\n";
var_dump($r2=assert(0 != 0));
echo"\n";
echo "Reset callback options to use an object method\n";
$o = new c1();
var_dump($rc=assert_options(ASSERT_CALLBACK,array(&$o,"assert")));
var_dump($rao=assert_options(ASSERT_CALLBACK));
echo "ini.get(\"assert.callback\") => [".ini_get("assert.callback")."]\n\n";
var_dump($r2=assert(0 != 0));
echo"\n";
echo "Set callback to something silly\n";
assert_options(ASSERT_CALLBACK, 3.141);
var_dump($rao = assert_options(ASSERT_CALLBACK));
$fusion = $o;
$v1=$definedVars[array_rand($definedVars = get_defined_vars())];
interface I {
    public $prop { get; }
}
class A implements I {
    public $prop = 42 {
        get => $this->prop;
    }
}
$a = new A();
var_dump($fusion);
$v2=$definedVars[array_rand($definedVars = get_defined_vars())];
$v3=$definedVars[array_rand($definedVars = get_defined_vars())];
var_dump('random_var:',$v1,$v2,$v3);
var_fusion($v1,$v2,$v3);
?>
--EXPECTF--
Deprecated: PHP Startup: assert.warning INI setting is deprecated in Unknown on line 0

Deprecated: PHP Startup: assert.callback INI setting is deprecated in Unknown on line 0

Deprecated: PHP Startup: assert.exception INI setting is deprecated in Unknown on line 0

Deprecated: Constant ASSERT_CALLBACK is deprecated in %s on line %d

Deprecated: Function assert_options() is deprecated since 8.3 in %s on line %d
Initial values: assert_options(ASSERT_CALLBACK) => [f1]
Initial values: ini.get("assert.callback") => [f1]
f1 called
bool(false)

Change callback function using ini.set and test return value

Deprecated: ini_set(): assert.callback INI setting is deprecated in %s on line %d
string(2) "f1"

Deprecated: Constant ASSERT_CALLBACK is deprecated in %s on line %d

Deprecated: Function assert_options() is deprecated since 8.3 in %s on line %d
assert_options(ASSERT_CALLBACK) => [f2]
ini.get("assert.callback") => [f2]
f2 called
bool(false)

Change callback function using assert_options and test return value

Deprecated: Constant ASSERT_CALLBACK is deprecated in %s on line %d

Deprecated: Function assert_options() is deprecated since 8.3 in %s on line %d
string(2) "f2"

Deprecated: Constant ASSERT_CALLBACK is deprecated in %s on line %d

Deprecated: Function assert_options() is deprecated since 8.3 in %s on line %d
assert_options(ASSERT_CALLBACK) => [f3]
ini.get("assert.callback") => [f2]
f3 called
bool(false)

Reset the name of the callback routine to a class method

Deprecated: Constant ASSERT_CALLBACK is deprecated in %s on line %d

Deprecated: Function assert_options() is deprecated since 8.3 in %s on line %d
string(2) "f3"

Deprecated: Constant ASSERT_CALLBACK is deprecated in %s on line %d

Deprecated: Function assert_options() is deprecated since 8.3 in %s on line %d
assert_options(ASSERT_CALLBACK) => [c1]
ini.get("assert.callback") => [f2]
Invalid callback c1, function "c1" not found or invalid function name

Reset callback options to use a class method

Deprecated: Constant ASSERT_CALLBACK is deprecated in %s on line %d

Deprecated: Function assert_options() is deprecated since 8.3 in %s on line %d
string(2) "c1"

Deprecated: Constant ASSERT_CALLBACK is deprecated in %s on line %d

Deprecated: Function assert_options() is deprecated since 8.3 in %s on line %d
array(2) {
  [0]=>
  string(2) "c1"
  [1]=>
  string(6) "assert"
}
ini.get("assert.callback") => [f2]

Class assertion failed 56, "assert(0 != 0)"
bool(false)

Reset callback options to use an object method

Deprecated: Constant ASSERT_CALLBACK is deprecated in %s on line %d

Deprecated: Function assert_options() is deprecated since 8.3 in %s on line %d
array(2) {
  [0]=>
  string(2) "c1"
  [1]=>
  string(6) "assert"
}

Deprecated: Constant ASSERT_CALLBACK is deprecated in %s on line %d

Deprecated: Function assert_options() is deprecated since 8.3 in %s on line %d
array(2) {
  [0]=>
  &object(c1)#2 (0) {
  }
  [1]=>
  string(6) "assert"
}
ini.get("assert.callback") => [f2]

Class assertion failed 64, "assert(0 != 0)"
bool(false)

Set callback to something silly

Deprecated: Constant ASSERT_CALLBACK is deprecated in %s on line %d

Deprecated: Function assert_options() is deprecated since 8.3 in %s on line %d

Deprecated: Constant ASSERT_CALLBACK is deprecated in %s on line %d

Deprecated: Function assert_options() is deprecated since 8.3 in %s on line %d
float(3.141)
object(A)#1 (1) {
  ["prop"]=>
  int(42)
}
