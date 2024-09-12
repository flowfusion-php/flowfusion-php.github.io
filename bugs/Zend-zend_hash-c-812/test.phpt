--TEST--
Test: Canonicalization - C14N() with references+JIT FETCH_DIM_W: 004
--INI--
opcache.enable=1
opcache.enable_cli=1
opcache.file_update_protection=0
error_reporting=E_ALL
error_reporting=E_ALL & ~E_WARNING & ~E_NOTICE
opcache.enable=1
opcache.enable_cli=1
opcache.jit_buffer_size=1024M
opcache.jit=1042
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
    
// Adapted from canonicalization.phpt
$xml = <<<EOXML
<?xml version="1.0" encoding="ISO-8859-1" ?>
<foo xmlns="http://www.example.com/ns/foo"
     xmlns:fubar="http://www.example.com/ns/fubar" xmlns:test="urn::test"><contain>
  <bar><test1 /></bar>
  <bar><test2 /></bar>
  <fubar:bar xmlns:fubar="http://www.example.com/ns/fubar"><test3 /></fubar:bar>
  <fubar:bar><test4 /></fubar:bar>
</contain>
</foo>
EOXML;
$dom = new DOMDocument();
$dom->loadXML($xml);
$doc = $dom->documentElement->firstChild;
$xpath = [
    'query' => '(//a:contain | //a:bar | .//namespace::*)',
    'namespaces' => ['a' => 'http://www.example.com/ns/foo'],
];
$prefixes = ['test'];
foreach ($xpath['namespaces'] as $k => &$v);
unset($v);
foreach ($xpath as $k => &$v);
unset($v);
foreach ($prefixes as $k => &$v);
unset($v);
echo $doc->C14N(true, false, $xpath, $prefixes);
$fusion = $xml;
$v1=$definedVars[array_rand($definedVars = get_defined_vars())];
function create_references(&$array) {
    foreach ($a as $key => $value) {
        create_references($fusion[$key]);
    }
}
function change_copy($copy) {
        $copy['b']['z']['z'] = $copy['b'];
}
$data = [
    'a' => [
        'b' => [],
    ],
];
@create_references($data);
$copy = $data['a'];
var_dump($copy);
change_copy($copy);
var_dump($copy); //RECURSION
$v2=$definedVars[array_rand($definedVars = get_defined_vars())];
$v3=$definedVars[array_rand($definedVars = get_defined_vars())];
var_dump('random_var:',$v1,$v2,$v3);
var_fusion($v1,$v2,$v3);
?>
--EXTENSIONS--
dom
--EXPECT--
<contain xmlns="http://www.example.com/ns/foo" xmlns:test="urn::test"><bar></bar><bar></bar></contain>
array(1) {
  ["b"]=>
  array(0) {
  }
}
array(1) {
  ["b"]=>
  array(0) {
  }
}
