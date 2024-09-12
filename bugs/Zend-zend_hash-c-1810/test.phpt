--TEST--
Test interaction with cache slots+Bug #71818 (Memory leak when array altered in destructor)
--INI--
zend.enable_gc = 1
allow_url_include=0
opcache.interned_strings_buffer=16
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
    
class Test {
    public readonly string $prop;
    public readonly array $prop2;
    public readonly object $prop3;
    public function setProp(string $prop) {
        $this->prop = $prop;
    }
    public function initAndAppendProp2() {
        $this->prop2 = [];
        $this->prop2[] = 1;
    }
    public function initProp3() {
        $this->prop3 = new stdClass;
        $this->prop3->foo = 1;
    }
    public function replaceProp3() {
        $ref =& $this->prop3;
        $ref = new stdClass;
    }
}
$test = new Test;
$test->setProp("a");
var_dump($test->prop);
try {
    $test->setProp("b");
} catch (Error $e) {
    echo $e->getMessage(), "\n";
}
var_dump($test->prop);
echo "\n";
$test = new Test;
try {
    $test->initAndAppendProp2();
} catch (Error $e) {
    echo $e->getMessage(), "\n";
}
try {
    $test->initAndAppendProp2();
} catch (Error $e) {
    echo $e->getMessage(), "\n";
}
var_dump($test->prop2);
echo "\n";
$test = new Test;
$test->initProp3();
$test->replaceProp3();
var_dump($test->prop3);
$test->replaceProp3();
var_dump($test->prop3);
echo "\n";
// Test variations using closure rebinding, so we have unknown property_info in JIT.
$test = new Test;
(function() { $this->prop2 = []; })->call($test);
$appendProp2 = (function() {
    $this->prop2[] = 1;
})->bindTo($test, Test::class);
try {
    $appendProp2();
} catch (Error $e) {
    echo $e->getMessage(), "\n";
}
try {
    $appendProp2();
} catch (Error $e) {
    echo $e->getMessage(), "\n";
}
var_dump($test->prop2);
echo "\n";
$test = new Test;
$replaceProp3 = (function() {
    $ref =& $this->prop3;
    $ref = new stdClass;
})->bindTo($test, Test::class);
$test->initProp3();
$replaceProp3();
var_dump($test->prop3);
$replaceProp3();
var_dump($test->prop3);
$fusion = $test;
$v1=$definedVars[array_rand($definedVars = get_defined_vars())];
class MemoryLeak
{
    public function __construct()
    {
        $this->things[] = $this;
    }
    public function __destruct()
    {
        $fusion->things[] = null;
    }
    private $things = [];
}
ini_set('memory_limit', '20M');
for ($i = 0; $i < 100000; ++$i) {
    $obj = new MemoryLeak();
}
echo "OK\n";
$v2=$definedVars[array_rand($definedVars = get_defined_vars())];
$v3=$definedVars[array_rand($definedVars = get_defined_vars())];
var_dump('random_var:',$v1,$v2,$v3);
var_fusion($v1,$v2,$v3);
?>
--EXPECTF--
string(1) "a"
Cannot modify readonly property Test::$prop
string(1) "a"

Cannot indirectly modify readonly property Test::$prop2
Cannot modify readonly property Test::$prop2
array(0) {
}

object(stdClass)#%d (1) {
  ["foo"]=>
  int(1)
}
object(stdClass)#%d (1) {
  ["foo"]=>
  int(1)
}

Cannot indirectly modify readonly property Test::$prop2
Cannot indirectly modify readonly property Test::$prop2
array(0) {
}

object(stdClass)#%d (1) {
  ["foo"]=>
  int(1)
}
object(stdClass)#%d (1) {
  ["foo"]=>
  int(1)
}
OK
