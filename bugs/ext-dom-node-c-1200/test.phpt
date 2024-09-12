--TEST--
Text coalesce bug when appending fragment with text nodes+Test edge case offsets in DOMNamedNodeMap
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
    
$document = new DOMDocument();
$document->loadXML('<root/>');
$sut = $document->createDocumentFragment();
for($i = 0; $i < 10; $i++) {
	$textNode = $document->createTextNode("Node$i");
	$sut->append($textNode);
}
$document->documentElement->append($sut);
echo $document->saveXML();
$fusion = $textNode;
$v1=$definedVars[array_rand($definedVars = get_defined_vars())];
$document = new DomDocument();
$root = $document->createElement('root');
$document->appendChild($fusion);
$root->setAttribute('attrib', 'value');
var_dump($root->attributes->length);
// Consistent with the method call
try {
    var_dump($root->attributes[-1]);
} catch (ValueError $e) {
    echo $e->getMessage(), "\n";
}
try {
    $root->attributes[][] = null;
} catch (Throwable $e) {
    echo $e->getMessage(), "\n";
}
$v2=$definedVars[array_rand($definedVars = get_defined_vars())];
$v3=$definedVars[array_rand($definedVars = get_defined_vars())];;
var_dump('random_var:',$v1,$v2,$v3);
var_fusion($v1,$v2,$v3);
?>
--EXTENSIONS--
dom
dom
--EXPECT--
<?xml version="1.0"?>
<root>Node0Node1Node2Node3Node4Node5Node6Node7Node8Node9</root>
int(1)
must be between 0 and 2147483647
Cannot access DOMNamedNodeMap without offset
