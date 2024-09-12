--TEST--
Test array_chunk() function : usage variations - array with diff. sub arrays+Parent type with interning disabled
--INI--
opcache.interned_strings_buffer=0
opcache.file_cache_only=1
session.auto_start = 0
opcache.enable=1
opcache.enable_cli=1
opcache.jit_buffer_size=1024M
opcache.jit=tracing
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
                // Prepare arguments
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
    try{
        fuzz_internal_interface($vars);
        fuzz_internal_interface($vars);
        fuzz_internal_interface($vars);
    } catch (ReflectionException $e) {
        echo("Error: " . $e->getMessage());
    }
    return $result;
}
    
/*
* Testing array_chunk() function - input array containing different sub arrays
*/
echo "*** Testing array_chunk() : usage variations ***\n";
$size = 2;
// input array
$input_array = array (
  "array1" => array(),
  "array2" => array(1, 2, 3),
  "array3" =>  array(1)
);
echo "\n-- Testing array_chunk() by supplying an array containing different sub arrays & 'preserve_key' as default --\n";
var_dump( array_chunk($input_array, $size) );
echo "\n-- Testing array_chunk() by supplying an array containing different sub arrays & 'preserve_key' = true --\n";
var_dump( array_chunk($input_array, $size, true) );
echo "\n-- Testing array_chunk() by supplying an array containing different sub arrays & 'preserve_key' = false --\n";
var_dump( array_chunk($input_array, $size, false) );
echo "Done";
$v1=$definedVars[array_rand($definedVars = get_defined_vars())];
class Foo {
    public function test(): self {
    }
}
class Bar extends Foo {
    public function test(): parent {
    }
}
?>
$v2=$definedVars[array_rand($definedVars = get_defined_vars())];
$v3=$definedVars[array_rand($definedVars = get_defined_vars())];;
var_dump('random_var:',$v1,$v2,$v3);
var_fusion($v1,$v2,$v3);
?>
--EXPECT--
*** Testing array_chunk() : usage variations ***

-- Testing array_chunk() by supplying an array containing different sub arrays & 'preserve_key' as default --
array(2) {
  [0]=>
  array(2) {
    [0]=>
    array(0) {
    }
    [1]=>
    array(3) {
      [0]=>
      int(1)
      [1]=>
      int(2)
      [2]=>
      int(3)
    }
  }
  [1]=>
  array(1) {
    [0]=>
    array(1) {
      [0]=>
      int(1)
    }
  }
}

-- Testing array_chunk() by supplying an array containing different sub arrays & 'preserve_key' = true --
array(2) {
  [0]=>
  array(2) {
    ["array1"]=>
    array(0) {
    }
    ["array2"]=>
    array(3) {
      [0]=>
      int(1)
      [1]=>
      int(2)
      [2]=>
      int(3)
    }
  }
  [1]=>
  array(1) {
    ["array3"]=>
    array(1) {
      [0]=>
      int(1)
    }
  }
}

-- Testing array_chunk() by supplying an array containing different sub arrays & 'preserve_key' = false --
array(2) {
  [0]=>
  array(2) {
    [0]=>
    array(0) {
    }
    [1]=>
    array(3) {
      [0]=>
      int(1)
      [1]=>
      int(2)
      [2]=>
      int(3)
    }
  }
  [1]=>
  array(1) {
    [0]=>
    array(1) {
      [0]=>
      int(1)
    }
  }
}
Done
===DONE===
