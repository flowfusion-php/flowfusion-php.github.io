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
    
function spl_examples_autoload($classname)
{
    include(__DIR__ . '/' . strtolower($classname) . '.inc');
}
spl_autoload_register('spl_examples_autoload');
function test($a, $b, $identical = false)
{
    var_dump(DualIterator::compareIterators(
                    new RecursiveArrayIterator($a),
                    new RecursiveArrayIterator($b),
                    $identical));
}
test(array(1,2,3), array(1,2,3));
test(array(1,2,3), array(1,2));
test(array(1,array(21,22),3), array(1,array(21,22),3));
test(array(1,array(21,22),3), array(1,array(21,22,23),3));
test(array(1,array(21,22),3), array(1,array(21,22,3)));
test(array(1,array(21,22),3), array(1,array(21),array(22),3));
test(array(1,2,3), array(1,"2",3), false);
test(array(1,2,3), array(1,"2",3), true);
test(array(1,array(21,22),3), array(1,array(21,"22"),3), false);
test(array(1,array(21,22),3), array(1,array(21,"22"),3), true);
$v1=$definedVars[array_rand($definedVars = get_defined_vars())];
class MyDateTimeZone extends DateTimeZone
{
    public function getTransitions(): array|false
    {
    }
}
$v2=$definedVars[array_rand($definedVars = get_defined_vars())];
$v3=$definedVars[array_rand($definedVars = get_defined_vars())];;
var_dump('random_var:',$v1,$v2,$v3);
var_fusion($v1,$v2,$v3);
?>
