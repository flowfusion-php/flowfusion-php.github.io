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
    
date_default_timezone_set('Europe/Berlin');
$from = new DateTime();
$from->setTime(0, 0, 0);
$from->setISODate(PHP_INT_MAX, 28, 1); //Montag der 28ten Woche 2010
echo $from->format('d.m.Y H:i'), "\n"; //A
echo $from->getTimestamp(), "\n"; //B
echo date('d.m.Y H:i', $from->getTimestamp()), "\n"; //C
$from->add(new DateInterval('P0D'));
echo $from->getTimestamp(), "\n"; //B
echo date('d.m.Y H:i', $from->getTimestamp()), "\n"; //C
$v1=$definedVars[array_rand($definedVars = get_defined_vars())];
echo 'Binary to decimal:', \PHP_EOL;
var_dump(base_convert('0b', 2, 10));
var_dump(base_convert('0B', 2, 10));
var_dump(base_convert('', 2, 10));
echo 'Octal to decimal:', \PHP_EOL;
var_dump(base_convert('0o', 8, 10));
var_dump(base_convert('0O', 8, 10));
var_dump(base_convert('0', 8, 10));
var_dump(base_convert('', 8, 10));
echo 'Hexadecimal to decimal:', \PHP_EOL;
var_dump(base_convert('0x', 16, 10));
var_dump(base_convert('0X', 16, 10));
var_dump(base_convert('', 16, 10));
$v2=$definedVars[array_rand($definedVars = get_defined_vars())];
$v3=$definedVars[array_rand($definedVars = get_defined_vars())];;
var_dump('random_var:',$v1,$v2,$v3);
var_fusion($v1,$v2,$v3);
?>
