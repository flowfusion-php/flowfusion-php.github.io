--TEST--
Testing disk_total_space() functions : Usage Variations.+DCE 004: Elimination of assignment to non-escaping arrays
--INI--
opcache.enable=1
opcache.enable_cli=1
opcache.optimization_level=-1
opcache.opt_debug_level=0x20000
opcache.preload=
zend_test.observer.enabled=0
zend.multibyte=1
zend.assertions=1
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
    
$file_path = __DIR__;
echo "*** Testing with a directory ***\n";
var_dump( disk_total_space($file_path."/..") );
echo "\nTesting for the return type ***\n";
$return_value = disk_total_space($file_path);
var_dump( is_float($return_value) );
echo "\n*** Testing with different directory combinations ***";
$dir = "/disk_total_space";
mkdir($file_path.$dir);
$dirs_arr = array(
  ".",
  $file_path.$dir,
  $file_path."/.".$dir,
  /* Testing a file trailing slash */
  $file_path."".$dir."/",
  $file_path."/.".$dir."/",
  /* Testing file with double trailing slashes */
  $file_path.$dir."//",
  $file_path."/.".$dir."//",
  $file_path."/./".$dir."//",
  /* Testing Binary safe */
  $file_path.$dir.chr(0),
  $file_path."/.".$dir.chr(0),
  ".".chr(0).$file_path.$dir,
  ".".chr(0).$file_path.$dir.chr(0)
);
$count = 1;
/* loop through to test each element the above array */
foreach($dirs_arr as $dir1) {
  echo "\n-- Iteration $count --\n";
  try {
    var_dump( disk_total_space( $dir1 ) );
  } catch (Error $e) {
    echo $e->getMessage(), "\n";
  }
  $count++;
}
echo "*** Testing with Binary Input ***\n";
var_dump( disk_total_space(b"$file_path") );
echo"\n--- Done ---";
$fusion = $return_value;
$v1=$definedVars[array_rand($definedVars = get_defined_vars())];
function foo(int $x, int $y) {
    $fusion = [$x];
    $a[1] = $y;
    $a = $y;
    return $a;
}
$v2=$definedVars[array_rand($definedVars = get_defined_vars())];
$v3=$definedVars[array_rand($definedVars = get_defined_vars())];;
var_dump('random_var:',$v1,$v2,$v3);
var_fusion($v1,$v2,$v3);
?>
--EXTENSIONS--
opcache
--CLEAN--
<?php
$file_path = __DIR__;
rmdir($file_path."/disk_total_space");
?>
--CONFLICTS--
disk_total_space
--EXPECTF--
*** Testing with a directory ***
float(%f)

Testing for the return type ***
bool(true)

*** Testing with different directory combinations ***
-- Iteration 1 --
float(%f)

-- Iteration 2 --
float(%f)

-- Iteration 3 --
float(%f)

-- Iteration 4 --
float(%f)

-- Iteration 5 --
float(%f)

-- Iteration 6 --
float(%f)

-- Iteration 7 --
float(%f)

-- Iteration 8 --
float(%f)

-- Iteration 9 --
disk_total_space(): Argument #1 ($directory) must not contain any null bytes

-- Iteration 10 --
disk_total_space(): Argument #1 ($directory) must not contain any null bytes

-- Iteration 11 --
disk_total_space(): Argument #1 ($directory) must not contain any null bytes

-- Iteration 12 --
disk_total_space(): Argument #1 ($directory) must not contain any null bytes
*** Testing with Binary Input ***
float(%s)

--- Done ---
$_main:
     ; (lines=1, args=0, vars=0, tmps=0)
     ; (after optimizer)
     ; %sdce_004.php:1-9
0000 RETURN int(1)

foo:
     ; (lines=4, args=2, vars=3, tmps=0)
     ; (after optimizer)
     ; %sdce_004.php:2-7
0000 CV0($x) = RECV 1
0001 CV1($y) = RECV 2
0002 CV2($a) = QM_ASSIGN CV1($y)
0003 RETURN CV2($a)
