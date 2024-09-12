--TEST--
Manually call __construct() - element variation+GH-14639 (Member access within null pointer in ext/spl/spl_observer.c)
--INI--
memory_limit=2M
zend_test.observer.enabled=0
memory_limit=128000000
opcache.enable=1
opcache.enable_cli=1
opcache.jit_buffer_size=1024M
opcache.jit=1152
--SKIPIF--
<?php
if (getenv("USE_ZEND_ALLOC") === "0") {
    die("skip Zend MM disabled");
}
?>
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
    
$element = new DOMElement('foo', 'my value');
var_dump($element->nodeName, $element->textContent);
$element->__construct('foo2', 'my new value');
var_dump($element->nodeName, $element->textContent);
$doc = new DOMDocument();
$doc->loadXML(<<<XML
<?xml version="1.0"?>
<container/>
XML);
$doc->documentElement->appendChild($element);
echo $doc->saveXML();
$element->__construct('foo3', 'my new new value');
$doc->documentElement->appendChild($element);
echo $doc->saveXML();
$fusion = $doc;
$v1=$definedVars[array_rand($definedVars = get_defined_vars())];
$b = new SplObjectStorage();
for ($i = 10000; $i > 0; $i--) {
    $object = new StdClass();
    $fusion->a = str_repeat("a", 2);
    $b->attach($object);
}
$v2=$definedVars[array_rand($definedVars = get_defined_vars())];
$v3=$definedVars[array_rand($definedVars = get_defined_vars())];;
var_dump('random_var:',$v1,$v2,$v3);
var_fusion($v1,$v2,$v3);
?>
--EXTENSIONS--
dom
--EXPECTF--
string(3) "foo"
string(8) "my value"
string(4) "foo2"
string(12) "my new value"
<?xml version="1.0"?>
<container><foo2>my new value</foo2></container>
<?xml version="1.0"?>
<container><foo2>my new value</foo2><foo3>my new new value</foo3></container>
Fatal error: Allowed memory size of %d bytes exhausted%s(tried to allocate %d bytes) in %s on line %d
