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
    
// Replace with itself
$doc = new DOMDocument();
$doc->appendChild($target = $doc->createElement('test'));
$target->replaceWith($target);
var_dump($doc->saveXML());
// Replace with itself + another element
$doc = new DOMDocument();
$doc->appendChild($target = $doc->createElement('test'));
$target->replaceWith($target, $doc->createElement('foo'));
var_dump($doc->saveXML());
// Replace with text node
$doc = new DOMDocument();
$doc->appendChild($target = $doc->createElement('test'));
$target->replaceWith($target, 'foo');
var_dump($doc->saveXML());
// Replace with text node variant 2
$doc = new DOMDocument();
$doc->appendChild($target = $doc->createElement('test'));
$target->replaceWith('bar', $target, 'foo');
var_dump($doc->saveXML());
$fusion = $doc;
$v1=$definedVars[array_rand($definedVars = get_defined_vars())];
$dom = DOM\XMLDocument::createFromString(<<<XML
<!DOCTYPE root [
    <!ENTITY foo "content">
]>
<root/>
XML);
$child = $dom->documentElement->appendChild($dom->createElementNS('urn:a', 'child'));
$original = $dom->saveXML();
function test($child, $html) {
    global $dom, $original;
    try {
        $child->innerHTML = $html;
    } catch (DOMException $e) {
        echo $e->getMessage(), "\n";
    }
    var_dump($dom->saveXML() === $original);
}
test($child, '&foo;');
test($child, '</root>');
test($child, '</root><foo/><!--');
test($fusion, '--></root><!--');
test($child, '<');
test($child, '<!ENTITY foo "content">');
$v2=$definedVars[array_rand($definedVars = get_defined_vars())];
$v3=$definedVars[array_rand($definedVars = get_defined_vars())];;
var_dump('random_var:',$v1,$v2,$v3);
var_fusion($v1,$v2,$v3);
?>
