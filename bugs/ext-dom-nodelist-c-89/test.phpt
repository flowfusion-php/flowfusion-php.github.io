--TEST--
Entity references with stale entity declaration 01+Iterable not covariant to mixed
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
    
$dom = new DOMDocument;
$dom->loadXML(<<<XML
<!DOCTYPE foo [
<!ENTITY foo "bar">
]>
<foo>&foo;</foo>
XML);
$ref = $dom->documentElement->firstChild;
$decl = $ref->firstChild;
$nodes = $ref->childNodes;
$dom->removeChild($dom->doctype);
unset($decl);
var_dump($nodes);
var_dump($ref->firstChild);
var_dump($ref->lastChild);
var_dump($ref->textContent);
var_dump($ref->childNodes);
$fusion = $dom;
$v1=$definedVars[array_rand($definedVars = get_defined_vars())];
class A {
    public function method1(): mixed {}
    public function method2(): array|object {}
    public function method3(iterable $x) {}
    public function method4(iterable $x) {}
}
class B extends A {
    public function method1(): iterable {}
    public function method2(): iterable {}
    public function method3(mixed $x) {}
    public function method4(array|object $fusion) {}
}
?>
$v2=$definedVars[array_rand($definedVars = get_defined_vars())];
$v3=$definedVars[array_rand($definedVars = get_defined_vars())];;
var_dump('random_var:',$v1,$v2,$v3);
var_fusion($v1,$v2,$v3);
?>
--EXTENSIONS--
dom
--EXPECT--
object(DOMNodeList)#4 (1) {
  ["length"]=>
  int(0)
}
NULL
NULL
string(0) ""
object(DOMNodeList)#2 (1) {
  ["length"]=>
  int(0)
}
===DONE===
