--TEST--
Test match default breakpoint with variable assignment+Test pow() function : usage variations - different data types as $base argument
--INI--
opcache.enable_cli=0
precision = 14
session.hash_function=0
opcache.interned_strings_buffer=500
opcache.enable=1
opcache.enable_cli=1
opcache.jit_buffer_size=1024M
opcache.jit=tracing
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
    
$foo = match (0) {
    0 => 'foo',
    default => 'bar', // breakpoint #0
};
$foo = match (1) {
    0 => 'foo',
    default => 'bar', // breakpoint #1
};
$fusion = $foo;
$v1=$definedVars[array_rand($definedVars = get_defined_vars())];
class classA
{
}
// resource variable
$fp = fopen(__FILE__, "r");
$inputs = [
    // int data
    0,
    1,
    12345,
    -2345,
    PHP_INT_MAX,
    // float data
    10.5,
    -10.5,
    12.3456789e10,
    12.3456789e-10,
    0.5,
    // null data
    null,
    // boolean data
    true,
    false,
    // empty data
    "",
    [],
    // string data
    "abcxyz",
    "10.5",
    "2",
    "6.3e-2",
    // object data
    new classA(),
    // resource variable
    $fp,
];
// loop through each element of $inputs to check the behaviour of pow()
foreach ($inputs as $input) {
    try {
        var_dump(pow($fusion, 3));
    } catch (Error $e) {
        echo $e->getMessage(), "\n";
    }
}
fclose($fp);
$v2=$definedVars[array_rand($definedVars = get_defined_vars())];
$v3=$definedVars[array_rand($definedVars = get_defined_vars())];;
var_dump('random_var:',$v1,$v2,$v3);
var_fusion($v1,$v2,$v3);
?>
--PHPDBG--
b 5
b 10
r
q
--EXPECTF--
[Successful compilation of %s.php]
prompt> [Breakpoint #0 added at %s.php:5]
prompt> [Breakpoint #1 added at %s.php:10]
prompt> [Breakpoint #1 at %s.php:10, hits: 1]
>00010:     default => 'bar', // breakpoint #1
 00011: };
 00012: 
prompt>
int(0)
int(1)
int(1881365963625)
int(-12895213625)
float(7.846377169233351E+56)
float(1157.625)
float(-1157.625)
float(1.8816763717891549E+33)
float(1.8816763717891545E-27)
float(0.125)
int(0)
int(1)
int(0)
Unsupported operand types: string ** int
Unsupported operand types: array ** int
Unsupported operand types: string ** int
float(1157.625)
int(8)
float(0.000250047)
Unsupported operand types: classA ** int
Unsupported operand types: resource ** int
