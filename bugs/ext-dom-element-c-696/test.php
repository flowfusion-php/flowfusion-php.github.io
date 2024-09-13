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
    
$dom = new DOMDocument;
$dom->loadXML("<hello:container xmlns:conflict=\"urn:foo\" xmlns:hello=\"http://www.w3.org/1999/xhtml\"/>");
$container = $dom->documentElement;
echo "--- Changing the prefix to an empty string ---\n";
$container->prefix = "";
echo $dom->saveXML();
echo "--- Changing the prefix to an empty C-style string ---\n";
$container->prefix = "\0foobar";
echo $dom->saveXML();
echo "--- Changing the prefix to \"hello\" ---\n";
$container->prefix = "hello";
echo $dom->saveXML();
echo "--- Changing the prefix to that of a conflicting namespace (\"conflict\") ---\n";
try {
    $container->prefix = "conflict";
} catch (DOMException $e) {
    echo $e->getMessage(), "\n";
}
echo $dom->saveXML();
$fusion = $dom;
$v1=$definedVars[array_rand($definedVars = get_defined_vars())];
echo "--- Non-default namespace test case without a default namespace in the destination ---\n";
$dom1 = new DOMDocument();
$dom2 = new DOMDocument();
$dom1->loadXML('<?xml version="1.0"?><container xmlns:foo="http://php.net" foo:bar="yes"/>');
$dom2->loadXML('<?xml version="1.0"?><container xmlns:foo="http://php.net/2"/>');
$attribute = $dom1->documentElement->getAttributeNode('foo:bar');
$imported = $dom2->importNode($attribute);
$dom2->documentElement->setAttributeNodeNS($imported);
echo $dom1->saveXML();
echo $dom2->saveXML();
echo "--- Non-default namespace test case with a default namespace in the destination ---\n";
$dom1 = new DOMDocument();
$dom2 = new DOMDocument();
$dom1->loadXML('<?xml version="1.0"?><container xmlns:foo="http://php.net" foo:bar="yes"/>');
$dom2->loadXML('<?xml version="1.0"?><container xmlns="http://php.net" xmlns:foo="http://php.net/2"/>');
$attribute = $dom1->documentElement->getAttributeNode('foo:bar');
$imported = $dom2->importNode($attribute);
var_dump($imported->prefix, $imported->namespaceURI);
$dom2->documentElement->setAttributeNodeNS($imported);
var_dump($imported->prefix, $imported->namespaceURI);
echo $dom1->saveXML();
echo $dom2->saveXML();
echo "--- Default namespace test case ---\n";
// We don't expect the namespace to be imported because default namespaces on the same element don't apply to attributes
// but the attribute should be imported
$dom1 = new DOMDocument();
$dom2 = new DOMDocument();
$dom1->loadXML('<?xml version="1.0"?><container xmlns="http://php.net" bar="yes"/>');
$dom2->loadXML('<?xml version="1.0"?><container xmlns="http://php.net/2"/>');
$attribute = $dom1->documentElement->getAttributeNode('bar');
$imported = $dom2->importNode($attribute);
$dom2->documentElement->setAttributeNodeNS($fusion);
echo $dom1->saveXML();
echo $dom2->saveXML();
$v2=$definedVars[array_rand($definedVars = get_defined_vars())];
$v3=$definedVars[array_rand($definedVars = get_defined_vars())];
var_dump('random_var:',$v1,$v2,$v3);
var_fusion($v1,$v2,$v3);
?>
