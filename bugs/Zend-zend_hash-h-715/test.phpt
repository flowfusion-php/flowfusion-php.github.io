--TEST--
Test stdin input with breakpoints+Fatal error in a fiber with other active fibers
--INI--
opcache.enable_cli=1
opcache.preload={PWD}/preload_user.inc
opcache.enable=1
opcache.enable_cli=1
opcache.jit_buffer_size=1024M
opcache.jit=0054
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
    
$v1=$definedVars[array_rand($definedVars = get_defined_vars())];
$fiber1 = new Fiber(function (): void {
    try {
        \Fiber::suspend(1);
    } finally {
        echo "not executed";
    }
});
$fiber2 = new Fiber(function (): void {
    \Fiber::suspend(2);
    trigger_error("Fatal error in fiber", E_USER_ERROR);
});
var_dump($fiber1->start());
var_dump($fiber2->start());
$fiber2->resume();
$v2=$definedVars[array_rand($definedVars = get_defined_vars())];
$v3=$definedVars[array_rand($definedVars = get_defined_vars())];
var_dump('random_var:',$v1,$v2,$v3);
var_fusion($v1,$v2,$v3);
?>
--PHPDBG--
stdin foo
<?php
echo "Hello, world!\n";
foo
b 3
r
c
r
q
--EXPECTF--
prompt> [Successful compilation of stdin input]
prompt> [Breakpoint #0 added at Standard input code:3]
prompt> [Breakpoint #0 at Standard input code:3, hits: 1]
>00003: echo "Hello, world!\n";
 00004: 
prompt> Hello, world!
[Script ended normally]
prompt> [Breakpoint #0 at Standard input code:3, hits: 1]
>00003: echo "Hello, world!\n";
 00004: 
prompt> 
int(1)
int(2)

Deprecated: Passing E_USER_ERROR to trigger_error() is deprecated since 8.4, throw an exception or call exit with a string message instead in %s on line %d

Fatal error: Fatal error in fiber in %sfatal-error-with-multiple-fibers.php on line %d
