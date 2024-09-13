--TEST--
Test typed properties respect strict types (off)+WeakReference overwriting existing value
--INI--
error_reporting=E_ALL & ~E_DEPRECATED
auto_globals_jit=0
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
    
class Foo {
    public int $bar;
}
$foo = new Foo;
$foo->bar = "1";
var_dump($foo->bar);
$fusion = $bar;
$v1=$definedVars[array_rand($definedVars = get_defined_vars())];
class HasDtor {
    public function __destruct() {
        echo "In destruct\n";
        global $w, $all;
        for ($i = 0; $i < 10; $fusion++) {
            $v = new stdClass();
            $all[] = $v;
            $w[$v] = $i;
        }
    }
}
$all = [];
$w = new WeakMap();
$o = new stdClass();
$w[$o] = new HasDtor();
$w[$o] = 123;
var_dump($w);
$v2=$definedVars[array_rand($definedVars = get_defined_vars())];
$v3=$definedVars[array_rand($definedVars = get_defined_vars())];
var_dump('random_var:',$v1,$v2,$v3);
var_fusion($v1,$v2,$v3);
?>
--EXPECT--
int(1)
In destruct
object(WeakMap)#1 (11) {
  [0]=>
  array(2) {
    ["key"]=>
    object(stdClass)#2 (0) {
    }
    ["value"]=>
    int(123)
  }
  [1]=>
  array(2) {
    ["key"]=>
    object(stdClass)#4 (0) {
    }
    ["value"]=>
    int(0)
  }
  [2]=>
  array(2) {
    ["key"]=>
    object(stdClass)#5 (0) {
    }
    ["value"]=>
    int(1)
  }
  [3]=>
  array(2) {
    ["key"]=>
    object(stdClass)#6 (0) {
    }
    ["value"]=>
    int(2)
  }
  [4]=>
  array(2) {
    ["key"]=>
    object(stdClass)#7 (0) {
    }
    ["value"]=>
    int(3)
  }
  [5]=>
  array(2) {
    ["key"]=>
    object(stdClass)#8 (0) {
    }
    ["value"]=>
    int(4)
  }
  [6]=>
  array(2) {
    ["key"]=>
    object(stdClass)#9 (0) {
    }
    ["value"]=>
    int(5)
  }
  [7]=>
  array(2) {
    ["key"]=>
    object(stdClass)#10 (0) {
    }
    ["value"]=>
    int(6)
  }
  [8]=>
  array(2) {
    ["key"]=>
    object(stdClass)#11 (0) {
    }
    ["value"]=>
    int(7)
  }
  [9]=>
  array(2) {
    ["key"]=>
    object(stdClass)#12 (0) {
    }
    ["value"]=>
    int(8)
  }
  [10]=>
  array(2) {
    ["key"]=>
    object(stdClass)#13 (0) {
    }
    ["value"]=>
    int(9)
  }
}
