--TEST--
Backed prop satisfies interface get hook by-reference+rfc1867 array upload
--INI--
file_uploads=1
upload_max_filesize=1024
max_file_uploads=10
opcache.enable_cli=1
opcache.jit_buffer_size=1M
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
var_dump($fusion);
var_dump($_POST);
$v2=$definedVars[array_rand($definedVars = get_defined_vars())];
$v3=$definedVars[array_rand($definedVars = get_defined_vars())];
var_dump('random_var:',$v1,$v2,$v3);
var_fusion($v1,$v2,$v3);
?>
--EXPECTF--
object(A)#1 (1) {
  ["prop"]=>
  int(42)
}
array(1) {
  ["file"]=>
  array(6) {
    ["name"]=>
    array(3) {
      [0]=>
      string(9) "file1.txt"
      [2]=>
      string(9) "file2.txt"
      [3]=>
      string(9) "file3.txt"
    }
    ["full_path"]=>
    array(3) {
      [0]=>
      string(9) "file1.txt"
      [2]=>
      string(9) "file2.txt"
      [3]=>
      string(9) "file3.txt"
    }
    ["type"]=>
    array(3) {
      [0]=>
      string(16) "text/plain-file1"
      [2]=>
      string(16) "text/plain-file2"
      [3]=>
      string(16) "text/plain-file3"
    }
    ["tmp_name"]=>
    array(3) {
      [0]=>
      string(%d) "%s"
      [2]=>
      string(%d) "%s"
      [3]=>
      string(%d) "%s"
    }
    ["error"]=>
    array(3) {
      [0]=>
      int(0)
      [2]=>
      int(0)
      [3]=>
      int(0)
    }
    ["size"]=>
    array(3) {
      [0]=>
      int(1)
      [2]=>
      int(1)
      [3]=>
      int(1)
    }
  }
}
array(0) {
}
