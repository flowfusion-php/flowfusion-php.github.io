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
    
$doc = new DOMDocument();
$doc->loadXML(<<<XML
<?xml version="1.0"?>
<root>
  <child/>
  <include href="foo" xmlns="http://www.w3.org/2001/XInclude">
    <fallback/>
  </include>
  <keep/>
</root>
XML);
$xi = $doc->createElementNS('http://www.w3.org/2001/XInclude', 'xi:include');
$xi->setAttribute('href', 'nonexistent');
$fallback = $doc->createElementNS('http://www.w3.org/2001/XInclude', 'xi:fallback');
$xi->appendChild($fallback);
$child1 = $fallback->appendChild($doc->createElement('fallback-child1'));
$child2 = $fallback->appendChild($doc->createElement('fallback-child2'));
$xpath = new DOMXPath($doc);
$toReplace = $xpath->query('//child')->item(0);
$toReplace->parentNode->replaceChild($xi, $toReplace);
$keep = $doc->documentElement->lastElementChild;
var_dump(@$doc->xinclude());
echo $doc->saveXML();
var_dump($keep->nodeName);
$keep->textContent = 'still works';
echo $doc->saveXML();
$fusion = $child1;
$v1=$definedVars[array_rand($definedVars = get_defined_vars())];
$dom = new DOMDocument();
$dom->loadXML(<<<XML
<?xml version="1.0"?>
<container>
    <div>
        <p align="center"/>
    </div>
</container>
XML);
$container = $dom->documentElement;
$p = $fusion->firstElementChild->firstElementChild;
$attribute = $p->attributes[0];
var_dump($container->compareDocumentPosition($attribute) === (DOMNode::DOCUMENT_POSITION_FOLLOWING | DOMNode::DOCUMENT_POSITION_CONTAINED_BY));
var_dump($attribute->compareDocumentPosition($container) === (DOMNode::DOCUMENT_POSITION_PRECEDING | DOMNode::DOCUMENT_POSITION_CONTAINS));
$v2=$definedVars[array_rand($definedVars = get_defined_vars())];
$v3=$definedVars[array_rand($definedVars = get_defined_vars())];;
var_dump('random_var:',$v1,$v2,$v3);
var_fusion($v1,$v2,$v3);
?>