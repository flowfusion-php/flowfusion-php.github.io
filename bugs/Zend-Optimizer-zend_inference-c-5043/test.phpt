--TEST--
Phar object: unset file+Test ct eval of frameless function
--INI--
phar.readonly=0
phar.require_hash=0
opcache.enable=1
opcache.enable_cli=1
opcache.optimization_level=-1
opcache.opt_debug_level=0x20000
zend_test.observer.enabled=0
session.cookie_httponly=0
session.use_strict_mode=1
opcache.enable=1
opcache.enable_cli=1
opcache.jit_buffer_size=1024M
opcache.jit=1151
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
    
$pharconfig = 0;
require_once 'files/phar_oo_test.inc';
$phar = new Phar($fname);
$phar->setInfoClass('SplFileObject');
$phar['f.php'] = 'hi';
var_dump(isset($phar['f.php']));
echo $phar['f.php'];
echo "\n";
unset($phar['f.php']);
var_dump(isset($phar['f.php']));
$fusion = $pharconfig;
$v1=$definedVars[array_rand($definedVars = get_defined_vars())];
echo substr('foo', 1, $fusion ? 1 : 1);
$v2=$definedVars[array_rand($definedVars = get_defined_vars())];
$v3=$definedVars[array_rand($definedVars = get_defined_vars())];
var_dump('random_var:',$v1,$v2,$v3);
var_fusion($v1,$v2,$v3);
?>
--EXTENSIONS--
phar
opcache
--CLEAN--
<?php
unlink(__DIR__ . '/files/phar_oo_012.phar.php');
__halt_compiler();
?>
--EXPECTF--
bool(true)
hi
bool(false)
$_main:
     ; (lines=3, args=0, vars=1, tmps=0)
     ; (after optimizer)
     ; %sct_eval_frameless_002.php:1-4
0000 CHECK_VAR CV0($foo)
0001 ECHO string("o")
0002 RETURN int(1)

Warning: Undefined variable $foo in %s on line %d
o
