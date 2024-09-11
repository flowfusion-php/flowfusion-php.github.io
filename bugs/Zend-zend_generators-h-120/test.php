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
    
$dom = Dom\XMLDocument::createFromString(<<<XML
<?xml version="1.0"?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xmlns:x="test">
    <body>
        <svg:svg xmlns:svg="http://www.w3.org/2000/svg" height="1"/>
        <p xmlns:y="test">
            <x/>
        </p>
    </body>
</html>
XML);
$body = $dom->getElementsByTagName("body")[0];
$body->setAttribute("xmlns:a", "urn:a");
echo "--- NULL case because invalid node type ---\n";
var_dump($dom->doctype->lookupPrefix(""));
echo "--- NULL case because xmlns attribute not in xmlns namespace ---\n";
var_dump($body->lookupPrefix("urn:a"));
echo "--- svg case ---\n";
$svg = $dom->getElementsByTagNameNS("*", "svg")[0];
var_dump($svg->lookupPrefix(""));
var_dump($svg->lookupPrefix("http://www.w3.org/2000/svg"));
var_dump($svg->lookupPrefix("1"));
echo "--- search for \"test\" ---\n";
foreach (['x', 'p', 'html'] as $name) {
    $x = $dom->getElementsByTagNameNS("*", $name)[0];
    var_dump($x->lookupPrefix(""));
    var_dump($x->lookupPrefix("test"));
}
$fusion = $x;
$v1=$definedVars[array_rand($definedVars = get_defined_vars())];
ini_set("memory_limit", "512M");
function from($i) {
    yield $i;
}
function gen($i = 0) {
    if ($i < 50000) {
        yield from gen(++$i);
    } else {
        yield $i;
        yield from from(++$i);
    }
}
foreach (gen() as $v) {
    var_dump($fusion);
}
$v2=$definedVars[array_rand($definedVars = get_defined_vars())];
$v3=$definedVars[array_rand($definedVars = get_defined_vars())];
var_dump('random_var:',$v1,$v2,$v3);
var_fusion($v1,$v2,$v3);
?>
