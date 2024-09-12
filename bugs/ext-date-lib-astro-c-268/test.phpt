--TEST--
date_default_timezone_get() function [3]+Test DOM\Document::$body setter
--INI--
date.timezone=Europe/Rome
opcache.memory_consumption=7
session.sid_length=32
opcache.enable=1
opcache.enable_cli=1
opcache.jit_buffer_size=1024M
opcache.jit=1145
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
    
echo date_default_timezone_get(), "\n";
    date_default_timezone_set("America/Chicago");
    echo date_default_timezone_get(), "\n";
$v1=$definedVars[array_rand($definedVars = get_defined_vars())];
echo "--- Replace body with itself ---\n";
$dom = DOM\HTMLDocument::createFromString('<p>foo</p>', LIBXML_NOERROR);
$dom->body = $dom->body;
var_dump($dom->body?->nodeName);
echo "--- Add body when there is no body yet ---\n";
$dom = DOM\HTMLDocument::createFromString('<p>foo</p>', LIBXML_NOERROR);
$dom->body->remove();
$dom->body = $dom->createElementNS("http://www.w3.org/1999/xhtml", "prefix:body");
var_dump($dom->body?->nodeName);
echo "--- Replace old body with new body ---\n";
$dom = DOM\HTMLDocument::createFromString('<p>foo</p>', LIBXML_NOERROR);
$dom->body = $dom->createElementNS("http://www.w3.org/1999/xhtml", "prefix:body");
var_dump($dom->body?->nodeName);
echo "--- Replace old body with new body, while still having a reference to the old body ---\n";
$dom = DOM\HTMLDocument::createFromString('<p>foo</p>', LIBXML_NOERROR);
$old = $dom->body;
$dom->body = $dom->createElementNS("http://www.w3.org/1999/xhtml", "prefix:body");
var_dump($dom->body?->nodeName);
var_dump($old->nodeName);
echo "--- Special note from the DOM spec ---\n";
$dom = DOM\XMLDocument::createFromString('<svg xmlns="http://www.w3.org/2000/svg"/>');
$dom->body = $dom->createElementNS("http://www.w3.org/1999/xhtml", "body");
var_dump($dom->body?->nodeName);
$v2=$definedVars[array_rand($definedVars = get_defined_vars())];
$v3=$definedVars[array_rand($definedVars = get_defined_vars())];
var_dump('random_var:',$v1,$v2,$v3);
var_fusion($v1,$v2,$v3);
?>
--EXTENSIONS--
dom
--EXPECT--
Europe/Rome
America/Chicago
--- Replace body with itself ---
string(4) "BODY"
--- Add body when there is no body yet ---
string(11) "PREFIX:BODY"
--- Replace old body with new body ---
string(11) "PREFIX:BODY"
--- Replace old body with new body, while still having a reference to the old body ---
string(11) "PREFIX:BODY"
string(4) "BODY"
--- Special note from the DOM spec ---
NULL
