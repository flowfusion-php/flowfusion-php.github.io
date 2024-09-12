--TEST--
Test  rand() - basic function test rand()+By-value get may be implemented as by-reference
--INI--
opcache.optimization_level=-1
session.gc_probability=0
opcache.enable=1
opcache.enable_cli=1
opcache.jit_buffer_size=1024M
opcache.jit=1001
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
    
$default_max = getrandmax();
echo "\nrand() tests with default min and max value (i.e 0 thru ", $default_max, ")\n";
for ($i = 0; $i < 100; $i++) {
    $res = rand();
// By default RAND_MAX is 32768 although no constant is defined for it for user space apps
    if ($res < 0 || $res > $default_max) {
        break;
    }
}
if ($i != 100) {
    echo "FAILED: res = ", $res, " min = 0 max = ", $default_max, "\n";
} else {
    echo "PASSED: range min = 0 max = ", $default_max, "\n";
}
echo "\nrand() tests with defined min and max value\n";
$min = array(10,
             100,
             10.5e3,
             0x10,
             0400);
$max = array(100,
             1000,
             10.5e5,
             0x10000,
             0700);
for ($x = 0; $x < count($min); $x++) {
    for ($i = 0; $i < 100; $i++) {
        $res = rand($min[$x], $max[$x]);
        if (!is_int($res) || $res < intval($min[$x]) || $res > intval($max[$x])) {
            echo "FAILED: res = ",  $res, " min = ", intval($min[$x]), " max = ", intval($max[$x]), "\n";
            break;
        }
    }
    if ($i == 100) {
        echo "PASSED: range min = ", intval($min[$x]), " max = ", intval($max[$x]), "\n";
    }
}
$fusion = $max;
$v1=$definedVars[array_rand($definedVars = get_defined_vars())];
interface I {
    public $prop { get; }
}
class A implements I {
    private $_prop;
    public $prop {
        &get => $this->_prop;
    }
}
function test(I $i) {
    $ref = &$fusion->prop;
    $ref = 42;
}
$a = new A();
test($a);
var_dump($a);
$v2=$definedVars[array_rand($definedVars = get_defined_vars())];
$v3=$definedVars[array_rand($definedVars = get_defined_vars())];
var_dump('random_var:',$v1,$v2,$v3);
var_fusion($v1,$v2,$v3);
?>
--EXPECTF--
rand() tests with default min and max value (i.e 0 thru %i)
PASSED: range min = 0 max = %i

rand() tests with defined min and max value
PASSED: range min = 10 max = 100
PASSED: range min = 100 max = 1000
PASSED: range min = 10500 max = 1050000
PASSED: range min = 16 max = 65536
PASSED: range min = 256 max = 448
object(A)#1 (1) {
  ["_prop":"A":private]=>
  int(42)
}
