--TEST--
Test pow function : 64bit long tests+"Reference Unpacking - Class ArrayAccess No Reference" list()
--SKIPIF--
<?php
if (PHP_INT_SIZE != 8) die("skip this test is for 64bit platform only");
?>
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
    
define("MAX_64Bit", 9223372036854775807);
define("MAX_32Bit", 2147483647);
define("MIN_64Bit", -9223372036854775807 - 1);
define("MIN_32Bit", -2147483647 - 1);
$longVals = array(
    MAX_64Bit, MIN_64Bit, MAX_32Bit, MIN_32Bit, MAX_64Bit - MAX_32Bit, MIN_64Bit - MIN_32Bit,
    MAX_32Bit + 1, MIN_32Bit - 1, MAX_32Bit * 2, (MAX_32Bit * 2) + 1, (MAX_32Bit * 2) - 1,
    MAX_64Bit -1, MAX_64Bit + 1, MIN_64Bit + 1, MIN_64Bit - 1
);
$otherVals = array(0, 1, -1, 7, 9, 65, -44, MAX_32Bit, MIN_32Bit, MAX_64Bit, MIN_64Bit);
foreach ($longVals as $longVal) {
   foreach($otherVals as $otherVal) {
       echo "--- testing: $longVal, $otherVal ---\n";
      var_dump(pow($longVal, $otherVal));
   }
}
$fusion = $longVals;
$v1=$definedVars[array_rand($definedVars = get_defined_vars())];
class StorageNoRef implements ArrayAccess {
    private $fusion = [];
    function __construct(array $a) { $this->s = $a; }
    function offsetSet ($k, $v): void { $this->s[$k] = $v; }
    function offsetGet ($k): mixed { return $this->s[$k]; }
    function offsetExists ($k): bool { return isset($this->s[$k]); }
    function offsetUnset ($k): void { unset($this->s[$k]); }
}
$a = new StorageNoRef([1, 2]);
list(&$one, $two) = $a;
var_dump($a);
$a = new StorageNoRef([1, 2]);
list(,,list($var)) = $a;
var_dump($a);
$a = new StorageNoRef(['one' => 1, 'two' => 2]);
['one' => &$one, 'two' => $two] = $a;
var_dump($a);
$v2=$definedVars[array_rand($definedVars = get_defined_vars())];
$v3=$definedVars[array_rand($definedVars = get_defined_vars())];
var_dump('random_var:',$v1,$v2,$v3);
var_fusion($v1,$v2,$v3);
?>
--EXPECTF--
--- testing: 9223372036854775807, 0 ---
int(1)
--- testing: 9223372036854775807, 1 ---
int(9223372036854775807)
--- testing: 9223372036854775807, -1 ---
float(1.0842021724855044E-19)
--- testing: 9223372036854775807, 7 ---
float(5.678427533559429E+132)
--- testing: 9223372036854775807, 9 ---
float(4.830671903771573E+170)
--- testing: 9223372036854775807, 65 ---
float(INF)
--- testing: 9223372036854775807, -44 ---
float(0)
--- testing: 9223372036854775807, 2147483647 ---
float(INF)
--- testing: 9223372036854775807, -2147483648 ---
float(0)
--- testing: 9223372036854775807, 9223372036854775807 ---
float(INF)
--- testing: 9223372036854775807, -9223372036854775808 ---
float(0)
--- testing: -9223372036854775808, 0 ---
int(1)
--- testing: -9223372036854775808, 1 ---
int(-9223372036854775808)
--- testing: -9223372036854775808, -1 ---
float(-1.0842021724855044E-19)
--- testing: -9223372036854775808, 7 ---
float(-5.678427533559429E+132)
--- testing: -9223372036854775808, 9 ---
float(-4.830671903771573E+170)
--- testing: -9223372036854775808, 65 ---
float(-INF)
--- testing: -9223372036854775808, -44 ---
float(0)
--- testing: -9223372036854775808, 2147483647 ---
float(-INF)
--- testing: -9223372036854775808, -2147483648 ---
float(0)
--- testing: -9223372036854775808, 9223372036854775807 ---
float(-INF)
--- testing: -9223372036854775808, -9223372036854775808 ---
float(0)
--- testing: 2147483647, 0 ---
int(1)
--- testing: 2147483647, 1 ---
int(2147483647)
--- testing: 2147483647, -1 ---
float(4.656612875245797E-10)
--- testing: 2147483647, 7 ---
float(2.1062458265055637E+65)
--- testing: 2147483647, 9 ---
float(9.713344420420489E+83)
--- testing: 2147483647, 65 ---
float(INF)
--- testing: 2147483647, -44 ---
float(0)
--- testing: 2147483647, 2147483647 ---
float(INF)
--- testing: 2147483647, -2147483648 ---
float(0)
--- testing: 2147483647, 9223372036854775807 ---
float(INF)
--- testing: 2147483647, -9223372036854775808 ---
float(0)
--- testing: -2147483648, 0 ---
int(1)
--- testing: -2147483648, 1 ---
int(-2147483648)
--- testing: -2147483648, -1 ---
float(-4.656612873077393E-10)
--- testing: -2147483648, 7 ---
float(-2.1062458333711437E+65)
--- testing: -2147483648, 9 ---
float(-9.713344461128645E+83)
--- testing: -2147483648, 65 ---
float(-INF)
--- testing: -2147483648, -44 ---
float(0)
--- testing: -2147483648, 2147483647 ---
float(-INF)
--- testing: -2147483648, -2147483648 ---
float(0)
--- testing: -2147483648, 9223372036854775807 ---
float(-INF)
--- testing: -2147483648, -9223372036854775808 ---
float(0)
--- testing: 9223372034707292160, 0 ---
int(1)
--- testing: 9223372034707292160, 1 ---
int(9223372034707292160)
--- testing: 9223372034707292160, -1 ---
float(1.08420217273794E-19)
--- testing: 9223372034707292160, 7 ---
float(5.678427524304645E+132)
--- testing: 9223372034707292160, 9 ---
float(4.830671893649017E+170)
--- testing: 9223372034707292160, 65 ---
float(INF)
--- testing: 9223372034707292160, -44 ---
float(0)
--- testing: 9223372034707292160, 2147483647 ---
float(INF)
--- testing: 9223372034707292160, -2147483648 ---
float(0)
--- testing: 9223372034707292160, 9223372036854775807 ---
float(INF)
--- testing: 9223372034707292160, -9223372036854775808 ---
float(0)
--- testing: -9223372034707292160, 0 ---
int(1)
--- testing: -9223372034707292160, 1 ---
int(-9223372034707292160)
--- testing: -9223372034707292160, -1 ---
float(-1.08420217273794E-19)
--- testing: -9223372034707292160, 7 ---
float(-5.678427524304645E+132)
--- testing: -9223372034707292160, 9 ---
float(-4.830671893649017E+170)
--- testing: -9223372034707292160, 65 ---
float(-INF)
--- testing: -9223372034707292160, -44 ---
float(0)
--- testing: -9223372034707292160, 2147483647 ---
float(-INF)
--- testing: -9223372034707292160, -2147483648 ---
float(0)
--- testing: -9223372034707292160, 9223372036854775807 ---
float(-INF)
--- testing: -9223372034707292160, -9223372036854775808 ---
float(0)
--- testing: 2147483648, 0 ---
int(1)
--- testing: 2147483648, 1 ---
int(2147483648)
--- testing: 2147483648, -1 ---
float(4.656612873077393E-10)
--- testing: 2147483648, 7 ---
float(2.1062458333711437E+65)
--- testing: 2147483648, 9 ---
float(9.713344461128645E+83)
--- testing: 2147483648, 65 ---
float(INF)
--- testing: 2147483648, -44 ---
float(0)
--- testing: 2147483648, 2147483647 ---
float(INF)
--- testing: 2147483648, -2147483648 ---
float(0)
--- testing: 2147483648, 9223372036854775807 ---
float(INF)
--- testing: 2147483648, -9223372036854775808 ---
float(0)
--- testing: -2147483649, 0 ---
int(1)
--- testing: -2147483649, 1 ---
int(-2147483649)
--- testing: -2147483649, -1 ---
float(-4.656612870908988E-10)
--- testing: -2147483649, 7 ---
float(-2.1062458402367238E+65)
--- testing: -2147483649, 9 ---
float(-9.713344501836802E+83)
--- testing: -2147483649, 65 ---
float(-INF)
--- testing: -2147483649, -44 ---
float(0)
--- testing: -2147483649, 2147483647 ---
float(-INF)
--- testing: -2147483649, -2147483648 ---
float(0)
--- testing: -2147483649, 9223372036854775807 ---
float(-INF)
--- testing: -2147483649, -9223372036854775808 ---
float(0)
--- testing: 4294967294, 0 ---
int(1)
--- testing: 4294967294, 1 ---
int(4294967294)
--- testing: 4294967294, -1 ---
float(2.3283064376228985E-10)
--- testing: 4294967294, 7 ---
float(2.6959946579271215E+67)
--- testing: 4294967294, 9 ---
float(4.9732323432552904E+86)
--- testing: 4294967294, 65 ---
float(INF)
--- testing: 4294967294, -44 ---
float(0)
--- testing: 4294967294, 2147483647 ---
float(INF)
--- testing: 4294967294, -2147483648 ---
float(0)
--- testing: 4294967294, 9223372036854775807 ---
float(INF)
--- testing: 4294967294, -9223372036854775808 ---
float(0)
--- testing: 4294967295, 0 ---
int(1)
--- testing: 4294967295, 1 ---
int(4294967295)
--- testing: 4294967295, -1 ---
float(2.3283064370807974E-10)
--- testing: 4294967295, 7 ---
float(2.6959946623210928E+67)
--- testing: 4294967295, 9 ---
float(4.9732323536765784E+86)
--- testing: 4294967295, 65 ---
float(INF)
--- testing: 4294967295, -44 ---
float(0)
--- testing: 4294967295, 2147483647 ---
float(INF)
--- testing: 4294967295, -2147483648 ---
float(0)
--- testing: 4294967295, 9223372036854775807 ---
float(INF)
--- testing: 4294967295, -9223372036854775808 ---
float(0)
--- testing: 4294967293, 0 ---
int(1)
--- testing: 4294967293, 1 ---
int(4294967293)
--- testing: 4294967293, -1 ---
float(2.3283064381649995E-10)
--- testing: 4294967293, 7 ---
float(2.6959946535331503E+67)
--- testing: 4294967293, 9 ---
float(4.9732323328340023E+86)
--- testing: 4294967293, 65 ---
float(INF)
--- testing: 4294967293, -44 ---
float(0)
--- testing: 4294967293, 2147483647 ---
float(INF)
--- testing: 4294967293, -2147483648 ---
float(0)
--- testing: 4294967293, 9223372036854775807 ---
float(INF)
--- testing: 4294967293, -9223372036854775808 ---
float(0)
--- testing: 9223372036854775806, 0 ---
int(1)
--- testing: 9223372036854775806, 1 ---
int(9223372036854775806)
--- testing: 9223372036854775806, -1 ---
float(1.0842021724855044E-19)
--- testing: 9223372036854775806, 7 ---
float(5.678427533559429E+132)
--- testing: 9223372036854775806, 9 ---
float(4.830671903771573E+170)
--- testing: 9223372036854775806, 65 ---
float(INF)
--- testing: 9223372036854775806, -44 ---
float(0)
--- testing: 9223372036854775806, 2147483647 ---
float(INF)
--- testing: 9223372036854775806, -2147483648 ---
float(0)
--- testing: 9223372036854775806, 9223372036854775807 ---
float(INF)
--- testing: 9223372036854775806, -9223372036854775808 ---
float(0)
--- testing: 9.2233720368548E+18, 0 ---
float(1)
--- testing: 9.2233720368548E+18, 1 ---
float(9.223372036854776E+18)
--- testing: 9.2233720368548E+18, -1 ---
float(1.0842021724855044E-19)
--- testing: 9.2233720368548E+18, 7 ---
float(5.678427533559429E+132)
--- testing: 9.2233720368548E+18, 9 ---
float(4.830671903771573E+170)
--- testing: 9.2233720368548E+18, 65 ---
float(INF)
--- testing: 9.2233720368548E+18, -44 ---
float(0)
--- testing: 9.2233720368548E+18, 2147483647 ---
float(INF)
--- testing: 9.2233720368548E+18, -2147483648 ---
float(0)
--- testing: 9.2233720368548E+18, 9223372036854775807 ---
float(INF)
--- testing: 9.2233720368548E+18, -9223372036854775808 ---
float(0)
--- testing: -9223372036854775807, 0 ---
int(1)
--- testing: -9223372036854775807, 1 ---
int(-9223372036854775807)
--- testing: -9223372036854775807, -1 ---
float(-1.0842021724855044E-19)
--- testing: -9223372036854775807, 7 ---
float(-5.678427533559429E+132)
--- testing: -9223372036854775807, 9 ---
float(-4.830671903771573E+170)
--- testing: -9223372036854775807, 65 ---
float(-INF)
--- testing: -9223372036854775807, -44 ---
float(0)
--- testing: -9223372036854775807, 2147483647 ---
float(-INF)
--- testing: -9223372036854775807, -2147483648 ---
float(0)
--- testing: -9223372036854775807, 9223372036854775807 ---
float(-INF)
--- testing: -9223372036854775807, -9223372036854775808 ---
float(0)
--- testing: -9.2233720368548E+18, 0 ---
float(1)
--- testing: -9.2233720368548E+18, 1 ---
float(-9.223372036854776E+18)
--- testing: -9.2233720368548E+18, -1 ---
float(-1.0842021724855044E-19)
--- testing: -9.2233720368548E+18, 7 ---
float(-5.678427533559429E+132)
--- testing: -9.2233720368548E+18, 9 ---
float(-4.830671903771573E+170)
--- testing: -9.2233720368548E+18, 65 ---
float(-INF)
--- testing: -9.2233720368548E+18, -44 ---
float(0)
--- testing: -9.2233720368548E+18, 2147483647 ---
float(-INF)
--- testing: -9.2233720368548E+18, -2147483648 ---
float(0)
--- testing: -9.2233720368548E+18, 9223372036854775807 ---
float(INF)
--- testing: -9.2233720368548E+18, -9223372036854775808 ---
float(0)
Notice: Indirect modification of overloaded element of %s has no effect in %s on line %d
object(StorageNoRef)#1 (1) {
  ["s":"StorageNoRef":private]=>
  array(2) {
    [0]=>
    int(1)
    [1]=>
    int(2)
  }
}

Warning: Undefined array key 2 in %s on line %d
object(StorageNoRef)#2 (1) {
  ["s":"StorageNoRef":private]=>
  array(2) {
    [0]=>
    int(1)
    [1]=>
    int(2)
  }
}

Notice: Indirect modification of overloaded element of %s has no effect in %s on line %d
object(StorageNoRef)#1 (1) {
  ["s":"StorageNoRef":private]=>
  array(2) {
    ["one"]=>
    int(1)
    ["two"]=>
    int(2)
  }
}
