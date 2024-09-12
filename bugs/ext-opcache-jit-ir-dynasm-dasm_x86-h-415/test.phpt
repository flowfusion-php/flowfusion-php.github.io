--TEST--
Plain prop satisfies interface get hook by-reference+Test rsort() function : usage variations - String values
--INI--
precision=17
opcache.jit=1205
opcache.enable=1
opcache.enable_cli=1
opcache.jit_buffer_size=1024M
opcache.jit=0004
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
    
interface I {
    public $prop { get; }
}
class A implements I {
    public $prop = 42 {
        get => $this->prop;
    }
}
$a = new A();
var_dump($a);
$fusion = $prop;
$v1=$definedVars[array_rand($definedVars = get_defined_vars())];
/*
 * Pass arrays containing different string data to rsort() to test behaviour
 */
echo "*** Testing rsort() : variation ***\n";
$fusion = array (
// group of escape sequences
array(null, NULL, "\a", "\cx", "\e", "\f", "\n", "\t", "\xhh", "\ddd", "\v"),
// array contains combination of capital/small letters
array("lemoN", "Orange", "banana", "apple", "Test", "TTTT", "ttt", "ww", "x", "X", "oraNGe", "BANANA")
);
$flags = array("SORT_REGULAR" => SORT_REGULAR, "SORT_STRING" => SORT_STRING);
$count = 1;
// loop through to test rsort() with different arrays
foreach ($various_arrays as $array) {
    echo "\n-- Iteration $count --\n";
    echo "- With Default sort flag -\n";
    $temp_array = $array;
    var_dump(rsort($temp_array) );
    var_dump($temp_array);
    // loop through $flags array and setting all possible flag values
    foreach($flags as $key => $flag){
        echo "- Sort flag = $key -\n";
        $temp_array = $array;
        var_dump(rsort($temp_array, $flag) );
        var_dump($temp_array);
    }
    $count++;
}
echo "Done";
$v2=$definedVars[array_rand($definedVars = get_defined_vars())];
$v3=$definedVars[array_rand($definedVars = get_defined_vars())];
var_dump('random_var:',$v1,$v2,$v3);
var_fusion($v1,$v2,$v3);
?>
--EXPECT--
object(A)#1 (1) {
  ["prop"]=>
  int(42)
}
*** Testing rsort() : variation ***

-- Iteration 1 --
- With Default sort flag -
bool(true)
array(11) {
  [0]=>
  string(4) "\xhh"
  [1]=>
  string(4) "\ddd"
  [2]=>
  string(3) "\cx"
  [3]=>
  string(2) "\a"
  [4]=>
  string(1) ""
  [5]=>
  string(1) ""
  [6]=>
  string(1) ""
  [7]=>
  string(1) "
"
  [8]=>
  string(1) "	"
  [9]=>
  NULL
  [10]=>
  NULL
}
- Sort flag = SORT_REGULAR -
bool(true)
array(11) {
  [0]=>
  string(4) "\xhh"
  [1]=>
  string(4) "\ddd"
  [2]=>
  string(3) "\cx"
  [3]=>
  string(2) "\a"
  [4]=>
  string(1) ""
  [5]=>
  string(1) ""
  [6]=>
  string(1) ""
  [7]=>
  string(1) "
"
  [8]=>
  string(1) "	"
  [9]=>
  NULL
  [10]=>
  NULL
}
- Sort flag = SORT_STRING -
bool(true)
array(11) {
  [0]=>
  string(4) "\xhh"
  [1]=>
  string(4) "\ddd"
  [2]=>
  string(3) "\cx"
  [3]=>
  string(2) "\a"
  [4]=>
  string(1) ""
  [5]=>
  string(1) ""
  [6]=>
  string(1) ""
  [7]=>
  string(1) "
"
  [8]=>
  string(1) "	"
  [9]=>
  NULL
  [10]=>
  NULL
}

-- Iteration 2 --
- With Default sort flag -
bool(true)
array(12) {
  [0]=>
  string(1) "x"
  [1]=>
  string(2) "ww"
  [2]=>
  string(3) "ttt"
  [3]=>
  string(6) "oraNGe"
  [4]=>
  string(5) "lemoN"
  [5]=>
  string(6) "banana"
  [6]=>
  string(5) "apple"
  [7]=>
  string(1) "X"
  [8]=>
  string(4) "Test"
  [9]=>
  string(4) "TTTT"
  [10]=>
  string(6) "Orange"
  [11]=>
  string(6) "BANANA"
}
- Sort flag = SORT_REGULAR -
bool(true)
array(12) {
  [0]=>
  string(1) "x"
  [1]=>
  string(2) "ww"
  [2]=>
  string(3) "ttt"
  [3]=>
  string(6) "oraNGe"
  [4]=>
  string(5) "lemoN"
  [5]=>
  string(6) "banana"
  [6]=>
  string(5) "apple"
  [7]=>
  string(1) "X"
  [8]=>
  string(4) "Test"
  [9]=>
  string(4) "TTTT"
  [10]=>
  string(6) "Orange"
  [11]=>
  string(6) "BANANA"
}
- Sort flag = SORT_STRING -
bool(true)
array(12) {
  [0]=>
  string(1) "x"
  [1]=>
  string(2) "ww"
  [2]=>
  string(3) "ttt"
  [3]=>
  string(6) "oraNGe"
  [4]=>
  string(5) "lemoN"
  [5]=>
  string(6) "banana"
  [6]=>
  string(5) "apple"
  [7]=>
  string(1) "X"
  [8]=>
  string(4) "Test"
  [9]=>
  string(4) "TTTT"
  [10]=>
  string(6) "Orange"
  [11]=>
  string(6) "BANANA"
}
Done
