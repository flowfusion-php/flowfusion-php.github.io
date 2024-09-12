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
    
function a ($field_type, $allowed_values) {
    $settings = [
        'list_string' => [
            'allowed_values' => $allowed_values,
        ],
    ];
    return $settings[$field_type];
}
var_dump(a("list_string", ["xxx"]));
$script1_dataflow = $settings;
$script1_connect=$script1_dataflow;
$dom = Dom\XMLDocument::createFromString('<root xmlns:p1="u1"><child xmlns:p2="u1"><p1:child2/></child></root>');
echo $dom->saveXml(), "\n";
$dom = Dom\XMLDocument::createFromString('<root xmlns:p1="u1"><child xmlns:p2="u1"></child></root>');
$script1_dataflow = $dom->documentElement;
$child2 = $root->ownerDocument->createElementNS('u1', 'child2');
$root->firstChild->appendChild($child2);
echo $dom->saveXml(), "\n";
$script2_connect=$script1_dataflow;
$random_var=$GLOBALS[array_rand($GLOBALS)];
var_dump('random_var:',$random_var);
var_fusion($script1_connect, $script2_connect, $random_var);
?>
