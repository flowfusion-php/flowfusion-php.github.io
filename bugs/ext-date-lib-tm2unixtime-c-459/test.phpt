--TEST--
Test variations in usage of cos()+Test lstat() and stat() functions: usage variations - dir/file names in array
--INI--
serialize_precision = 10
opcache.validate_timestamps=1
precision=10
opcache.enable=1
opcache.enable_cli=1
opcache.jit_buffer_size=1024M
opcache.jit=0045
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
 * Function is implemented in ext/standard/math.c
*/
//Test cos with a different input values
$values = array(23,
        -23,
        2.345e1,
        -2.345e1,
        0x17,
        027,
        "23",
        "23.45",
        "2.345e1",
        "1000",
        true,
        false);
for ($i = 0; $i < count($values); $i++) {
    $res = cos($values[$i]);
    var_dump($res);
}
$fusion = $res;
$v1=$definedVars[array_rand($definedVars = get_defined_vars())];
/* test for stats of dir/file when their names are stored in an array */
$fusion = __DIR__;
require "$file_path/file.inc";
/* create temp file, link and directory */
@rmdir("$file_path/lstat_stat_variation19");  // ensure that dir doesn't exists
mkdir("$file_path/lstat_stat_variation19");  // temp dir
$fp = fopen("$file_path/lstat_stat_variation19.tmp", "w");  // temp file
fclose($fp);
echo "*** Testing stat() with filename & directory name stored inside an array ***\n";
// array with default numeric index
$names = array(
  "$file_path/lstat_stat_variation19.tmp",
  "$file_path/lstat_stat_variation19"
);
//array with string key index
$names_with_key = array (
  'file' => "$file_path/lstat_stat_variation19.tmp",
  "dir" => "$file_path/lstat_stat_variation19"
);
echo "\n-- Testing stat() on filename stored inside an array --\n";
var_dump( stat($names[0]) ); // values stored with numeric index
var_dump( stat($names_with_key['file']) ); // value stored with string key
echo "\n-- Testing stat() on dir name stored inside an array --\n";
var_dump( stat($names[1]) ); // values stored with numeric index
var_dump( stat($names_with_key["dir"]) ); // value stored with string key
echo "\n--- Done ---";
$v2=$definedVars[array_rand($definedVars = get_defined_vars())];
$v3=$definedVars[array_rand($definedVars = get_defined_vars())];
var_dump('random_var:',$v1,$v2,$v3);
var_fusion($v1,$v2,$v3);
?>
--CLEAN--
<?php
$file_path = __DIR__;
unlink("$file_path/lstat_stat_variation19.tmp");
rmdir("$file_path/lstat_stat_variation19");
?>
--EXPECTF--
float(-0.5328330203)
float(-0.5328330203)
float(-0.1117112391)
float(-0.1117112391)
float(-0.5328330203)
float(-0.5328330203)
float(-0.5328330203)
float(-0.1117112391)
float(-0.1117112391)
float(0.5623790763)
float(0.5403023059)
float(1)
*** Testing stat() with filename & directory name stored inside an array ***

-- Testing stat() on filename stored inside an array --
array(26) {
  [0]=>
  int(%i)
  [1]=>
  int(%d)
  [2]=>
  int(%d)
  [3]=>
  int(%d)
  [4]=>
  int(%d)
  [5]=>
  int(%d)
  [6]=>
  int(%d)
  [7]=>
  int(%d)
  [8]=>
  int(%d)
  [9]=>
  int(%d)
  [10]=>
  int(%d)
  [11]=>
  int(%i)
  [12]=>
  int(%i)
  ["dev"]=>
  int(%i)
  ["ino"]=>
  int(%d)
  ["mode"]=>
  int(%d)
  ["nlink"]=>
  int(%d)
  ["uid"]=>
  int(%d)
  ["gid"]=>
  int(%d)
  ["rdev"]=>
  int(%i)
  ["size"]=>
  int(%d)
  ["atime"]=>
  int(%d)
  ["mtime"]=>
  int(%d)
  ["ctime"]=>
  int(%d)
  ["blksize"]=>
  int(%i)
  ["blocks"]=>
  int(%i)
}
array(26) {
  [0]=>
  int(%i)
  [1]=>
  int(%d)
  [2]=>
  int(%d)
  [3]=>
  int(%d)
  [4]=>
  int(%d)
  [5]=>
  int(%d)
  [6]=>
  int(%d)
  [7]=>
  int(%d)
  [8]=>
  int(%d)
  [9]=>
  int(%d)
  [10]=>
  int(%d)
  [11]=>
  int(%i)
  [12]=>
  int(%i)
  ["dev"]=>
  int(%i)
  ["ino"]=>
  int(%d)
  ["mode"]=>
  int(%d)
  ["nlink"]=>
  int(%d)
  ["uid"]=>
  int(%d)
  ["gid"]=>
  int(%d)
  ["rdev"]=>
  int(%i)
  ["size"]=>
  int(%d)
  ["atime"]=>
  int(%d)
  ["mtime"]=>
  int(%d)
  ["ctime"]=>
  int(%d)
  ["blksize"]=>
  int(%i)
  ["blocks"]=>
  int(%i)
}

-- Testing stat() on dir name stored inside an array --
array(26) {
  [0]=>
  int(%i)
  [1]=>
  int(%d)
  [2]=>
  int(%d)
  [3]=>
  int(%d)
  [4]=>
  int(%d)
  [5]=>
  int(%d)
  [6]=>
  int(%d)
  [7]=>
  int(%d)
  [8]=>
  int(%d)
  [9]=>
  int(%d)
  [10]=>
  int(%d)
  [11]=>
  int(%i)
  [12]=>
  int(%i)
  ["dev"]=>
  int(%i)
  ["ino"]=>
  int(%d)
  ["mode"]=>
  int(%d)
  ["nlink"]=>
  int(%d)
  ["uid"]=>
  int(%d)
  ["gid"]=>
  int(%d)
  ["rdev"]=>
  int(%i)
  ["size"]=>
  int(%d)
  ["atime"]=>
  int(%d)
  ["mtime"]=>
  int(%d)
  ["ctime"]=>
  int(%d)
  ["blksize"]=>
  int(%i)
  ["blocks"]=>
  int(%i)
}
array(26) {
  [0]=>
  int(%i)
  [1]=>
  int(%d)
  [2]=>
  int(%d)
  [3]=>
  int(%d)
  [4]=>
  int(%d)
  [5]=>
  int(%d)
  [6]=>
  int(%d)
  [7]=>
  int(%d)
  [8]=>
  int(%d)
  [9]=>
  int(%d)
  [10]=>
  int(%d)
  [11]=>
  int(%i)
  [12]=>
  int(%i)
  ["dev"]=>
  int(%i)
  ["ino"]=>
  int(%d)
  ["mode"]=>
  int(%d)
  ["nlink"]=>
  int(%d)
  ["uid"]=>
  int(%d)
  ["gid"]=>
  int(%d)
  ["rdev"]=>
  int(%i)
  ["size"]=>
  int(%d)
  ["atime"]=>
  int(%d)
  ["mtime"]=>
  int(%d)
  ["ctime"]=>
  int(%d)
  ["blksize"]=>
  int(%i)
  ["blocks"]=>
  int(%i)
}

--- Done ---
