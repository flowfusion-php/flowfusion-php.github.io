--TEST--
Test multiple watch elements pointing to the same watchpoint+Test for bug #65548: Comparison for DateTimeImmutable doesn't work
--INI--
date.timezone = UTC
session.save_handler=qwerty
error_reporting=E_ALL & ~E_DEPRECATED
--SKIPIF--
<?php
if (PHP_INT_SIZE == 4) {
    die("xfail There may be flaws in the implementation of watchpoints that cause failures");
}
if (getenv('SKIP_ASAN')) {
    die("skip intentionally causes segfaults");
}
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
    
$a = [0];
$a[0] = 1;
$b = &$a;
$a[0] = 2;
$a[1] = 3;
$c = [1];
$b = &$c;
$fusion = $a;
$v1=$definedVars[array_rand($definedVars = get_defined_vars())];
$iToday = new DateTimeImmutable('today');
$iTomorrow = new DateTimeImmutable('tomorrow');
$mToday = new DateTime('today');
$mTomorrow = new DateTime('tomorrow');
var_dump($iToday < $iTomorrow);
var_dump($iToday == $iTomorrow);
var_dump($iToday > $iTomorrow);
var_dump($iToday == $mToday);
var_dump($iToday === $mToday);
var_dump($iToday < $mTomorrow);
var_dump($iToday == $fusion);
var_dump($iToday > $mTomorrow);
$v2=$definedVars[array_rand($definedVars = get_defined_vars())];
$v3=$definedVars[array_rand($definedVars = get_defined_vars())];
var_dump('random_var:',$v1,$v2,$v3);
var_fusion($v1,$v2,$v3);
?>
--PHPDBG--
b 4
r
w $a[0]
w r $b
c
q
--EXPECTF--
[Successful compilation of %s]
prompt> [Breakpoint #0 added at %s:4]
prompt> [Breakpoint #0 at %s:4, hits: 1]
>00004: $a[0] = 1;
 00005: $b = &$a;
 00006: $a[0] = 2;
prompt> [Added watchpoint #0 for $a[0]]
prompt> [Added recursive watchpoint #1 for $b]
prompt> [Breaking on watchpoint $a[0]]
Old value: 0
New value: 1
>00005: $b = &$a;
 00006: $a[0] = 2;
 00007: $a[1] = 3;
prompt> [Breaking on watchpoint $b]
Old value: 
New value (reference): Array ([0] => 1)
>00006: $a[0] = 2;
 00007: $a[1] = 3;
 00008: $c = [1];
prompt> [Breaking on watchpoint $a[0]]
Old value: 1
New value: 2
>00007: $a[1] = 3;
 00008: $c = [1];
 00009: $b = &$c;
prompt> [Element 1 has been added to watchpoint]
[Breaking on watchpoint $b[]]
1 elements were added to the array
>00008: $c = [1];
 00009: $b = &$c;
 00010: 
prompt> [Breaking on watchpoint $b]
Old value inaccessible or destroyed
New value (reference): Array ([0] => 2,[1] => 3)
>00009: $b = &$c;
 00010: 
prompt> [Breaking on watchpoint $b]
Old value inaccessible or destroyed
New value (reference): Array ([0] => 1)
>00010: 
prompt> [$b has been removed, removing watchpoint recursively]
[$a[0] has been removed, removing watchpoint]
[Script ended normally]
prompt> 
bool(true)
bool(false)
bool(false)
bool(true)
bool(false)
bool(true)
bool(false)
bool(false)
