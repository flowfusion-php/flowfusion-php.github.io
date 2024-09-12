--TEST--
Test natcasesort() function : object functionality - mixed visibility within objects+Test sinh function : 64bit long tests
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
    
/*
 * Pass natcasesort() an array of objects which have properties of different
 * visibilities to test how it re-orders the array.
 */
echo "*** Testing natcasesort() : object functionality ***\n";
// class declaration for string objects
class for_string_natcasesort
{
    public $public_class_value;
    private $private_class_value;
    protected $protected_class_value;
    // initializing object member value
    function __construct($value1, $value2,$value3){
        $this->public_class_value = $value1;
        $this->private_class_value = $value2;
        $this->protected_class_value = $value3;
    }
    // return string value
    function __tostring() {
        return (string)$this->public_class_value;
    }
}
// array of string objects
$unsorted_str_obj = array (
new for_string_natcasesort("axx","AXX","ass"),
new for_string_natcasesort("t","eee","abb"),
new for_string_natcasesort("w","W", "c"),
new for_string_natcasesort("py","PY", "pt"),
);
echo "\n-- Testing natcasesort() by supplying object arrays --\n";
// testing natcasesort() function by supplying string object array
$temp_array = $unsorted_str_obj;
var_dump(natcasesort($temp_array) );
var_dump($temp_array);
echo "Done";
$fusion = $value2;
$v1=$definedVars[array_rand($definedVars = get_defined_vars())];
define("MAX_64Bit", 9223372036854775807);
define("MAX_32Bit", 2147483647);
define("MIN_64Bit", -9223372036854775807 - 1);
define("MIN_32Bit", -2147483647 - 1);
$longVals = array(
    MAX_64Bit, MIN_64Bit, MAX_32Bit, MIN_32Bit, MAX_64Bit - MAX_32Bit, MIN_64Bit - MIN_32Bit,
    MAX_32Bit + 1, MIN_32Bit - 1, MAX_32Bit * 2, (MAX_32Bit * 2) + 1, (MAX_32Bit * 2) - 1,
    MAX_64Bit -1, MAX_64Bit + 1, MIN_64Bit + 1, MIN_64Bit - 1
);
foreach ($longVals as $longVal) {
   echo "--- testing: $fusion ---\n";
   var_dump(sinh($longVal));
}
$v2=$definedVars[array_rand($definedVars = get_defined_vars())];
$v3=$definedVars[array_rand($definedVars = get_defined_vars())];
var_dump('random_var:',$v1,$v2,$v3);
var_fusion($v1,$v2,$v3);
?>
--EXPECTF--
*** Testing natcasesort() : object functionality ***

-- Testing natcasesort() by supplying object arrays --
bool(true)
array(4) {
  [0]=>
  object(for_string_natcasesort)#%d (3) {
    ["public_class_value"]=>
    string(3) "axx"
    ["private_class_value":"for_string_natcasesort":private]=>
    string(3) "AXX"
    ["protected_class_value":protected]=>
    string(3) "ass"
  }
  [3]=>
  object(for_string_natcasesort)#%d (3) {
    ["public_class_value"]=>
    string(2) "py"
    ["private_class_value":"for_string_natcasesort":private]=>
    string(2) "PY"
    ["protected_class_value":protected]=>
    string(2) "pt"
  }
  [1]=>
  object(for_string_natcasesort)#%d (3) {
    ["public_class_value"]=>
    string(1) "t"
    ["private_class_value":"for_string_natcasesort":private]=>
    string(3) "eee"
    ["protected_class_value":protected]=>
    string(3) "abb"
  }
  [2]=>
  object(for_string_natcasesort)#%d (3) {
    ["public_class_value"]=>
    string(1) "w"
    ["private_class_value":"for_string_natcasesort":private]=>
    string(1) "W"
    ["protected_class_value":protected]=>
    string(1) "c"
  }
}
Done
--- testing: 9223372036854775807 ---
float(INF)
--- testing: -9223372036854775808 ---
float(-INF)
--- testing: 2147483647 ---
float(INF)
--- testing: -2147483648 ---
float(-INF)
--- testing: 9223372034707292160 ---
float(INF)
--- testing: -9223372034707292160 ---
float(-INF)
--- testing: 2147483648 ---
float(INF)
--- testing: -2147483649 ---
float(-INF)
--- testing: 4294967294 ---
float(INF)
--- testing: 4294967295 ---
float(INF)
--- testing: 4294967293 ---
float(INF)
--- testing: 9223372036854775806 ---
float(INF)
--- testing: 9.2233720368548E+18 ---
float(INF)
--- testing: -9223372036854775807 ---
float(-INF)
--- testing: -9.2233720368548E+18 ---
float(-INF)
