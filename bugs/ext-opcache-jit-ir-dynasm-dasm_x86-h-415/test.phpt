--TEST--
DateTime::diff() days -- fall type3 type3+Plain prop satisfies interface get hook by-reference
--INI--
session.auto_start = 0
// have to put the absolute path here.
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
    
require 'examine_diff.inc';
define('PHPT_DATETIME_SHOW', PHPT_DATETIME_SHOW_DAYS);
require 'DateTime_data-fall-type3-type3.inc';
$v1=$definedVars[array_rand($definedVars = get_defined_vars())];
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
$v2=$definedVars[array_rand($definedVars = get_defined_vars())];
$v3=$definedVars[array_rand($definedVars = get_defined_vars())];;
var_dump('random_var:',$v1,$v2,$v3);
var_fusion($v1,$v2,$v3);
?>
--CREDITS--
Daniel Convissor <danielc@php.net>
--EXPECT--
test_time_fall_type3_prev_type3_prev: DAYS: **33**
test_time_fall_type3_prev_type3_dt: DAYS: **0**
test_time_fall_type3_prev_type3_redodt: DAYS: **0**
test_time_fall_type3_prev_type3_redost: DAYS: **0**
test_time_fall_type3_prev_type3_st: DAYS: **0**
test_time_fall_type3_prev_type3_post: DAYS: **2**
test_time_fall_type3_dt_type3_prev: DAYS: **0**
test_time_fall_type3_dt_type3_dt: DAYS: **0**
test_time_fall_type3_dt_type3_redodt: DAYS: **0**
test_time_fall_type3_dt_type3_redost: DAYS: **0**
test_time_fall_type3_dt_type3_st: DAYS: **0**
test_time_fall_type3_dt_type3_post: DAYS: **1**
test_time_fall_type3_redodt_type3_prev: DAYS: **0**
test_time_fall_type3_redodt_type3_dt: DAYS: **0**
test_time_fall_type3_redodt_type3_redodt: DAYS: **0**
test_time_fall_type3_redodt_type3_redost: DAYS: **0**
test_time_fall_type3_redodt_type3_st: DAYS: **0**
test_time_fall_type3_redodt_type3_post: DAYS: **1**
test_time_fall_type3_redost_type3_prev: DAYS: **0**
test_time_fall_type3_redost_type3_dt: DAYS: **0**
test_time_fall_type3_redost_type3_redodt: DAYS: **0**
test_time_fall_type3_redost_type3_redost: DAYS: **0**
test_time_fall_type3_redost_type3_st: DAYS: **0**
test_time_fall_type3_redost_type3_post: DAYS: **1**
test_time_fall_type3_st_type3_prev: DAYS: **0**
test_time_fall_type3_st_type3_dt: DAYS: **0**
test_time_fall_type3_st_type3_redodt: DAYS: **0**
test_time_fall_type3_st_type3_redost: DAYS: **0**
test_time_fall_type3_st_type3_st: DAYS: **0**
test_time_fall_type3_st_type3_post: DAYS: **1**
test_time_fall_type3_post_type3_prev: DAYS: **2**
test_time_fall_type3_post_type3_dt: DAYS: **1**
test_time_fall_type3_post_type3_redodt: DAYS: **1**
test_time_fall_type3_post_type3_redost: DAYS: **1**
test_time_fall_type3_post_type3_st: DAYS: **1**
test_time_fall_type3_post_type3_post: DAYS: **0**
test_time_fall_type3_dtsec_type3_stsec: DAYS: **0**
test_time_fall_type3_stsec_type3_dtsec: DAYS: **0**
object(A)#1 (1) {
  ["prop"]=>
  int(42)
}
