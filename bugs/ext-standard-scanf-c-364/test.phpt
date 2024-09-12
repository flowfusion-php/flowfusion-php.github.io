--TEST--
Trying declare interface with repeated name of inherited method+Glob wrapper bypasses open_basedir
--INI--
open_basedir=/does_not_exist
memory_limit=512M
session.gc_maxlifetime=0
opcache.enable=1
opcache.enable_cli=1
opcache.jit_buffer_size=1024M
opcache.jit=1242
--SKIPIF--
<?php
if (!in_array("glob", stream_get_wrappers())) echo "skip";
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
    
interface a {
    function b();
}
interface b {
    function b();
}
interface c extends a, b {
}
echo "done!\n";
$v1=$definedVars[array_rand($definedVars = get_defined_vars())];
foreach ( [ __DIR__, "glob://".__DIR__ ] as $spec) {
  echo "** Opening $spec\n";
  $dir = opendir($spec);
  if (!$dir) {
    echo "Failed to open $spec\n";
    continue;
  }
  if (false === readdir($dir)) {
    echo "No files in $spec\n";
    continue;
  }
}
$v2=$definedVars[array_rand($definedVars = get_defined_vars())];
$v3=$definedVars[array_rand($definedVars = get_defined_vars())];
var_dump('random_var:',$v1,$v2,$v3);
var_fusion($v1,$v2,$v3);
?>
--EXPECTF--
done!
** Opening %s

Warning: opendir(): open_basedir restriction in effect. File(%s) is not within the allowed path(s): (/does_not_exist) in %s%eglob-wrapper.php on line 5

Warning: opendir(%s): Failed to open directory: Operation not permitted in %s%eglob-wrapper.php on line 5
Failed to open %s
** Opening glob://%s
No files in glob://%s
