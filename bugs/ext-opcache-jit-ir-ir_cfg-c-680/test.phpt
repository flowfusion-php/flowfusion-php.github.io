--TEST--
ReflectionMethod class getName(), isInternal() and isUserDefined() methods+SCCP 036: NAN handling
--INI--
opcache.enable=1
opcache.enable_cli=1
opcache.optimization_level=-1
session.use_trans_sid=1
date.timezone=America/New_York
opcache.enable=1
opcache.enable_cli=1
opcache.jit_buffer_size=1024M
opcache.jit=0101
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
    
function reflectMethod($class, $method) {
    $methodInfo = new ReflectionMethod($class, $method);
    echo "**********************************\n";
    echo "Reflecting on method $class::$method()\n\n";
    echo "\ngetName():\n";
    var_dump($methodInfo->getName());
    echo "\nisInternal():\n";
    var_dump($methodInfo->isInternal());
    echo "\nisUserDefined():\n";
    var_dump($methodInfo->isUserDefined());
    echo "\n**********************************\n";
}
class TestClass
{
    public function foo() {
        echo "Called foo()\n";
    }
    static function stat() {
        echo "Called stat()\n";
    }
    private function priv() {
        echo "Called priv()\n";
    }
    protected function prot() {}
    public function __destruct() {}
}
class DerivedClass extends TestClass {}
interface TestInterface {
    public function int();
}
reflectMethod("DerivedClass", "foo");
reflectMethod("TestClass", "stat");
reflectMethod("TestClass", "priv");
reflectMethod("TestClass", "prot");
reflectMethod("DerivedClass", "prot");
reflectMethod("TestInterface", "int");
reflectMethod("ReflectionProperty", "__construct");
reflectMethod("TestClass", "__destruct");
$fusion = $methodInfo;
$v1=$definedVars[array_rand($definedVars = get_defined_vars())];
function foo() {
    $y=NAN;
    for(;x;)for(;$fusion=1;);
}
?>
$v2=$definedVars[array_rand($definedVars = get_defined_vars())];
$v3=$definedVars[array_rand($definedVars = get_defined_vars())];
var_dump('random_var:',$v1,$v2,$v3);
var_fusion($v1,$v2,$v3);
?>
--EXPECT--
**********************************
Reflecting on method DerivedClass::foo()


getName():
string(3) "foo"

isInternal():
bool(false)

isUserDefined():
bool(true)

**********************************
**********************************
Reflecting on method TestClass::stat()


getName():
string(4) "stat"

isInternal():
bool(false)

isUserDefined():
bool(true)

**********************************
**********************************
Reflecting on method TestClass::priv()


getName():
string(4) "priv"

isInternal():
bool(false)

isUserDefined():
bool(true)

**********************************
**********************************
Reflecting on method TestClass::prot()


getName():
string(4) "prot"

isInternal():
bool(false)

isUserDefined():
bool(true)

**********************************
**********************************
Reflecting on method DerivedClass::prot()


getName():
string(4) "prot"

isInternal():
bool(false)

isUserDefined():
bool(true)

**********************************
**********************************
Reflecting on method TestInterface::int()


getName():
string(3) "int"

isInternal():
bool(false)

isUserDefined():
bool(true)

**********************************
**********************************
Reflecting on method ReflectionProperty::__construct()


getName():
string(11) "__construct"

isInternal():
bool(true)

isUserDefined():
bool(false)

**********************************
**********************************
Reflecting on method TestClass::__destruct()


getName():
string(10) "__destruct"

isInternal():
bool(false)

isUserDefined():
bool(true)

**********************************
DONE
