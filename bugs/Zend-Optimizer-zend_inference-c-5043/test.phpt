--TEST--
Test array_splice() function : usage variations - references+Reference IDs should be correctly generated when $GLOBALS is serialized
--INI--
expose_php=0
opcache.optimization_level=1279
opcache.enable=1
opcache.enable_cli=1
opcache.jit_buffer_size=1024M
opcache.jit=0245
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
 * Function is implemented in ext/standard/array.c
*/
echo "test behaviour when input array is in a reference set\n";
$input_array=array (array(1,2));
$input_array[]=&$input_array[0];
var_dump (array_splice ($input_array[0],1,1));
var_dump ($input_array);
echo "Test behaviour of input arrays containing references \n";
/*
 *  There are three regions to test:, before cut, the cut and after the cut.
 *  For reach we check a plain value, a reference value with integer key and a
 *  reference value with a string key.
 */
$numbers=array(0,1,2,3,4,5,6,7,8,9,10,11,12);
$input_array=array(0,1,&$numbers[2],"three"=>&$numbers[3],4,&$numbers[5],"six"=>&$numbers[6],7,&$numbers[8],"nine"=>&$numbers[9]);
var_dump (array_splice ($input_array,4,3));
var_dump ($input_array);
echo "Test behaviour of replacement array containing references \n";
$three=3;
$four=4;
$input_array=array (0,1,2);
$b=array(&$three,"fourkey"=>&$four);
array_splice ($input_array,-1,1,$b);
var_dump ($input_array);
echo "Test behaviour of replacement which is part of reference set \n";
$int=3;
$input_array=array (1,2);
$b=&$int;
array_splice ($input_array,-1,1,$b);
var_dump ($input_array);
echo "Done\n";
$fusion = $numbers;
$v1=$definedVars[array_rand($definedVars = get_defined_vars())];
$obj = new stdClass;
$obj2 = new stdClass;
$obj2->obj = $obj;
$s = serialize($GLOBALS);
$fusion = unserialize($s);
var_dump($obj);
var_dump($obj2);
$v2=$definedVars[array_rand($definedVars = get_defined_vars())];
$v3=$definedVars[array_rand($definedVars = get_defined_vars())];
var_dump('random_var:',$v1,$v2,$v3);
var_fusion($v1,$v2,$v3);
?>
--EXPECT--
test behaviour when input array is in a reference set
array(1) {
  [0]=>
  int(2)
}
array(2) {
  [0]=>
  &array(1) {
    [0]=>
    int(1)
  }
  [1]=>
  &array(1) {
    [0]=>
    int(1)
  }
}
Test behaviour of input arrays containing references 
array(3) {
  [0]=>
  int(4)
  [1]=>
  &int(5)
  ["six"]=>
  &int(6)
}
array(7) {
  [0]=>
  int(0)
  [1]=>
  int(1)
  [2]=>
  &int(2)
  ["three"]=>
  &int(3)
  [3]=>
  int(7)
  [4]=>
  &int(8)
  ["nine"]=>
  &int(9)
}
Test behaviour of replacement array containing references 
array(4) {
  [0]=>
  int(0)
  [1]=>
  int(1)
  [2]=>
  &int(3)
  [3]=>
  &int(4)
}
Test behaviour of replacement which is part of reference set 
array(2) {
  [0]=>
  int(1)
  [1]=>
  int(3)
}
Done
object(stdClass)#1 (0) {
}
object(stdClass)#2 (1) {
  ["obj"]=>
  object(stdClass)#1 (0) {
  }
}
