--TEST--
Test fprintf() function (variation - 8)+Cleaning must preserve breakpoints
--INI--
opcache.enable_cli=0
opcache.jit_buffer_size=64M
session.cookie_httponly=0
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
    
$int_variation = array( "%d", "%-d", "%+d", "%7.2d", "%-7.2d", "%07.2d", "%-07.2d", "%'#7.2d" );
$int_numbers = array( 0, 1, -1, 2.7, -2.7, 23333333, -23333333, "1234" );
/* creating dumping file */
$data_file = __DIR__ . '/fprintf_variation_008_64bit.txt';
if (!($fp = fopen($data_file, 'wt')))
   return;
/* hexadecimal type variations */
fprintf($fp, "\n*** Testing fprintf() for hexadecimals ***\n");
foreach( $int_numbers as $hexa_num ) {
 fprintf( $fp, "\n");
 fprintf( $fp, "%x", $hexa_num );
}
fclose($fp);
print_r(file_get_contents($data_file));
echo "\nDone";
unlink($data_file);
$v1=$definedVars[array_rand($definedVars = get_defined_vars())];
echo 1;
echo 2;
echo 3;
foo();
function foo() {
	echo 4;
}
$v2=$definedVars[array_rand($definedVars = get_defined_vars())];
$v3=$definedVars[array_rand($definedVars = get_defined_vars())];
var_dump('random_var:',$v1,$v2,$v3);
var_fusion($v1,$v2,$v3);
?>
--PHPDBG--
b 4
b foo
r
c
clean
y
c
r
c
q
--EXPECTF--
*** Testing fprintf() for hexadecimals ***

0
1
ffffffffffffffff
2
fffffffffffffffe
16409d5
fffffffffe9bf62b
4d2
Done
[Successful compilation of %s]
prompt> [Breakpoint #0 added at %s:4]
prompt> [Breakpoint #1 added at foo]
prompt> 1
[Breakpoint #0 at %s:4, hits: 1]
>00004: echo 2;
 00005: echo 3;
 00006: foo();
prompt> 23
[Breakpoint #1 in foo() at %s:9, hits: 1]
>00009: 	echo 4;
 00010: }
 00011: 
prompt> Do you really want to clean your current environment? (type y or n): Cleaning Execution Environment
Classes    %d
Functions  %d
Constants  %d
Includes   0
prompt> [Not running]
prompt> 1
[Breakpoint #0 at %s:4, hits: 1]
>00004: echo 2;
 00005: echo 3;
 00006: foo();
prompt> 23
[Breakpoint #1 in foo() at %s:9, hits: 1]
>00009: 	echo 4;
 00010: }
 00011: 
prompt> 4
[Script ended normally]
prompt> 
