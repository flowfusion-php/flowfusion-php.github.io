--TEST--
Dom\HTMLDocument::createFromString() - normal document, no error+Template cloning
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
    
// The closing p tag breaks libxml2's HTML parser, but doesn't break the HTML5 parser due to the script context parsing rules.
$html = <<<HTML
<!DOCTYPE HTML>
<html>
    <head>
        <meta charset="utf-8">
        <title>foo</title>
    </head>
    <body>
        <script>
        var foo = "</p>";
        </script>
        <p test="<script>">bar <!-- hi --></p>
    </body>
</html>
HTML;
$dom = Dom\HTMLDocument::createFromString($html);
echo $dom->saveHtml(), "\n";
$fusion = $dom;
$v1=$definedVars[array_rand($definedVars = get_defined_vars())];
$fusion = Dom\HTMLDocument::createFromString('<template>x</template>', LIBXML_NOERROR);
$a = $dom->head->firstChild->cloneNode(false);
echo $dom->saveXML($a), "\n";
echo $dom->saveHTML($a), "\n";
$v2=$definedVars[array_rand($definedVars = get_defined_vars())];
$v3=$definedVars[array_rand($definedVars = get_defined_vars())];;
var_dump('random_var:',$v1,$v2,$v3);
var_fusion($v1,$v2,$v3);
?>
--EXTENSIONS--
dom
dom
--EXPECT--
<!DOCTYPE html><html><head>
        <meta charset="utf-8">
        <title>foo</title>
    </head>
    <body>
        <script>
        var foo = "</p>";
        </script>
        <p test="<script>">bar <!-- hi --></p>
    
</body></html>
<template xmlns="http://www.w3.org/1999/xhtml"></template>
<template></template>
