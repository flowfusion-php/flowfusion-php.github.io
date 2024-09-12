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
    
/*
 * Testing chop(): basic functionality
*/
echo "*** Testing chop() : basic functionality ***\n";
// Initialize all required variables
$str = "hello world\t\n\r\0\x0B  ";
$charlist = 'dl ';
// Calling chop() with default arguments
var_dump( chop($str) );
// Calling chop() with all arguments
var_dump( chop($str, $charlist) );
// Calling chop() with the charlist not present at the end of input string
var_dump( chop($str, '!') );
echo "Done\n";
$v1=$definedVars[array_rand($definedVars = get_defined_vars())];
const G = new stdClass();
enum E {
    case Case1;
}
trait T {
    public const int CONST1 = 1;
    public const ?array CONST2 = [];
    public const E CONST3 = E::Case1;
    public const stdClass CONST4 = G;
}
class C {
    use T;
    public const int CONST1 = 1;
    public const ?array CONST2 = [];
    public const E CONST3 = E::Case1;
    public const stdClass CONST4 = G;
}
var_dump(C::CONST1);
var_dump(C::CONST2);
var_dump(C::CONST3);
var_dump(C::CONST4);
$v2=$definedVars[array_rand($definedVars = get_defined_vars())];
$v3=$definedVars[array_rand($definedVars = get_defined_vars())];;
var_dump('random_var:',$v1,$v2,$v3);
var_fusion($v1,$v2,$v3);
?>
