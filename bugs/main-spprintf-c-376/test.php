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
    
try {
    Dom\HTMLDocument::createFromString(file_get_contents(__DIR__ . '/gb18030_without_charset.html'), overrideEncoding: 'nonexistent');
} catch (ValueError $e) {
    echo $e->getMessage(), "\n";
}
// The override encoding matches with the document encoding attribute
$dom = Dom\HTMLDocument::createFromString(file_get_contents(__DIR__ . '/gb18030_without_charset.html'), overrideEncoding: 'GB18030');
var_dump($dom->documentElement->lastChild->textContent);
var_dump($dom->charset);
// The override encoding mismatches with the document encoding attribute
$dom = Dom\HTMLDocument::createFromString(file_get_contents(__DIR__ . '/fallback_encoding.html'), overrideEncoding: 'Windows-1252');
var_dump($dom->documentElement->lastChild->textContent);
var_dump($dom->charset);
$fusion = $dom;
$v1=$definedVars[array_rand($definedVars = get_defined_vars())];
/*
 * Reference two arrays to each other then call reset() to test position of
 * internal pointer in both arrays
 */
echo "*** Testing reset() : usage variations ***\n";
$array1 = array ('zero', 'one', 'two');
echo "\n-- Initial position of internal pointer --\n";
var_dump(current($fusion));
// Test that when two variables are referenced to one another
// the internal pointer is the same for both
$array2 = &$array1;
next($array1);
echo "\n-- Position after calling next() --\n";
echo "\$array1: ";
var_dump(current($array1));
echo "\$array2: ";
var_dump(current($array2));
echo "\n-- Position after calling reset() --\n";
var_dump(reset($array1));
echo "\$array1: ";
var_dump(current($array1));
echo "\$array2: ";
var_dump(current($array2));
$v2=$definedVars[array_rand($definedVars = get_defined_vars())];
$v3=$definedVars[array_rand($definedVars = get_defined_vars())];
var_dump('random_var:',$v1,$v2,$v3);
var_fusion($v1,$v2,$v3);
?>
