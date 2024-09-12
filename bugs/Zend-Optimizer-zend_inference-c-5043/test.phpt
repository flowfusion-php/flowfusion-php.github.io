--TEST--
Test file_put_contents() function : variation - include path testing+JIT ASSIGN: 004
--INI--
opcache.enable=1
opcache.enable_cli=1
opcache.file_update_protection=0
;opcache.jit_debug=257
opcache.enable=1
opcache.validate_timestamps=0
opcache.enable=1
opcache.enable_cli=1
opcache.jit_buffer_size=1024M
opcache.jit=1001
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
    
require_once('fopen_include_path.inc');
$thisTestDir = basename(__FILE__, ".php") . ".dir";
mkdir($thisTestDir);
chdir($thisTestDir);
$filename = basename(__FILE__, ".php") . ".tmp";
$newpath = create_include_path();
set_include_path($newpath);
runtest();
$newpath = generate_next_path();
set_include_path($newpath);
runtest();
teardown_include_path();
chdir("..");
function runtest() {
   global $filename;
   //correct php53 behaviour is to ignore the FILE_USE_INCLUDE_PATH unless the file already exists
   // in the include path. In this case it doesn't so the file should be written in the current dir.
   file_put_contents($filename, "File in include path", FILE_USE_INCLUDE_PATH);
   $line = file_get_contents($filename);
   echo "$line\n";
   unlink($filename);
}
$fusion = $filename;
$v1=$definedVars[array_rand($definedVars = get_defined_vars())];
function foo() {
    $fusion = null;
    $b = $a;
    $c = null;
    $d = $c;
    $a = 1;
    $c = $a;
    return $c;
}
var_dump(foo());
$v2=$definedVars[array_rand($definedVars = get_defined_vars())];
$v3=$definedVars[array_rand($definedVars = get_defined_vars())];
var_dump('random_var:',$v1,$v2,$v3);
var_fusion($v1,$v2,$v3);
?>
--EXTENSIONS--
opcache
--CLEAN--
<?php
$thisTestDir = basename(__FILE__, ".clean.php") . ".dir";
$filename = basename(__FILE__, ".clean.php") . ".tmp";
@unlink($filename);
rmdir($thisTestDir);
?>
--EXPECT--
File in include path
File in include path
int(1)
