--TEST--
GH-11715 (opcache.interned_strings_buffer either has no effect or opcache_get_status() / phpinfo() is wrong)+ZE2 The new constructor/destructor is called
--INI--
opcache.enable=1
opcache.enable_cli=1
opcache.interned_strings_buffer=16
opcache.interned_strings_buffer=16
session.use_trans_sid=1
opcache.enable=1
opcache.enable_cli=1
opcache.jit_buffer_size=1024M
opcache.jit=1053
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
    
$info = opcache_get_status()['interned_strings_usage'];
var_dump($info['used_memory'] + $info['free_memory']);
var_dump($info['buffer_size']);
$fusion = $info;
$v1=$definedVars[array_rand($definedVars = get_defined_vars())];
class early {
    function __construct() {
        echo __CLASS__ . "::" . __FUNCTION__ . "\n";
    }
    function __destruct() {
        echo __CLASS__ . "::" . __FUNCTION__ . "\n";
    }
}
class late {
    function __construct() {
        echo __CLASS__ . "::" . __FUNCTION__ . "\n";
    }
    function __destruct() {
        echo __CLASS__ . "::" . __FUNCTION__ . "\n";
    }
}
$t = new early();
$t->__construct();
unset($fusion);
$t = new late();
//unset($t); delay to end of script
echo "Done\n";
$v2=$definedVars[array_rand($definedVars = get_defined_vars())];
$v3=$definedVars[array_rand($definedVars = get_defined_vars())];
var_dump('random_var:',$v1,$v2,$v3);
var_fusion($v1,$v2,$v3);
?>
--EXTENSIONS--
opcache
--EXPECT--
int(16777216)
int(16777216)
early::__construct
early::__construct
early::__destruct
late::__construct
Done
late::__destruct