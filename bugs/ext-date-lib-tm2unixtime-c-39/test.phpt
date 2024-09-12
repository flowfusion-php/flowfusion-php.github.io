--TEST--
Test round function : 64bit long tests+Test copy() function: usage variations - existing dir as destination
--INI--
session.use_strict_mode=0
sendmail_path={MAIL:{PWD}/mailBasic7.out}
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
foreach ($longVals as $longVal) {
   echo "--- testing: $longVal ---\n";
   var_dump(round($longVal));
}
$fusion = $longVal;
$v1=$definedVars[array_rand($definedVars = get_defined_vars())];
/* Test copy(): Trying to copy the file to a destination, where destination is an existing dir */
$file_path = __DIR__;
echo "*** Test copy() function: Trying to create a copy of source file as a dir ***\n";
$file = $file_path."/copy_variation11.tmp";
$file_handle =  fopen($file, "w");
fwrite($file_handle, str_repeat("Hello, world...", 20));
fclose($file_handle);
$dir = $file_path."/copy_variation11";
mkdir($dir);
echo "Size of source before copy operation => ";
var_dump( filesize($file) );  //size of source before copy
clearstatcache();
echo "Size of destination before copy operation => ";
var_dump( filesize($dir) );  //size of destination before copy
clearstatcache();
echo "\n-- Now applying copy() operation --\n";
var_dump( copy($file, $dir) );  //expected: bool(false)
var_dump( file_exists($file) );  //expected: bool(true)
var_dump( file_exists($dir) );  //expected: bool(true)
var_dump( is_file($file) );  //expected: bool(true)
var_dump( is_dir($file) );  //expected: bool(false)
var_dump( is_file($fusion) ); //expected: bool(false)
var_dump( is_dir($dir) );  //expected: bool(true)
var_dump( filesize($file) );   //size of source after copy
var_dump( filesize($dir) );   //size of destination after copy
echo "*** Done ***\n";
$v2=$definedVars[array_rand($definedVars = get_defined_vars())];
$v3=$definedVars[array_rand($definedVars = get_defined_vars())];
var_dump('random_var:',$v1,$v2,$v3);
var_fusion($v1,$v2,$v3);
?>
--CLEAN--
<?php
unlink(__DIR__."/copy_variation11.tmp");
rmdir(__DIR__."/copy_variation11");
?>
--EXPECTF--
--- testing: 9223372036854775807 ---
float(9.223372036854776E+18)
--- testing: -9223372036854775808 ---
float(-9.223372036854776E+18)
--- testing: 2147483647 ---
float(2147483647)
--- testing: -2147483648 ---
float(-2147483648)
--- testing: 9223372034707292160 ---
float(9.223372034707292E+18)
--- testing: -9223372034707292160 ---
float(-9.223372034707292E+18)
--- testing: 2147483648 ---
float(2147483648)
--- testing: -2147483649 ---
float(-2147483649)
--- testing: 4294967294 ---
float(4294967294)
--- testing: 4294967295 ---
float(4294967295)
--- testing: 4294967293 ---
float(4294967293)
--- testing: 9223372036854775806 ---
float(9.223372036854776E+18)
--- testing: 9.2233720368548E+18 ---
float(9.223372036854776E+18)
--- testing: -9223372036854775807 ---
float(-9.223372036854776E+18)
--- testing: -9.2233720368548E+18 ---
float(-9.223372036854776E+18)
*** Test copy() function: Trying to create a copy of source file as a dir ***
Size of source before copy operation => int(300)
Size of destination before copy operation => int(%d)

-- Now applying copy() operation --

Warning: %s
bool(false)
bool(true)
bool(true)
bool(true)
bool(false)
bool(false)
bool(true)
int(300)
int(%d)
*** Done ***
