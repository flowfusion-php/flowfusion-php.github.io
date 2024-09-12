--TEST--
Test range() function with non finite numbers+libxml_disable_entity_loader()
--INI--
serialize_precision=14
precision=13
opcache.optimization_level=0
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
    
// 0/0 NAN value
$f1 = fdiv(0, 0);
var_dump($f1);
// INF/INF NAN value
$f2 = fdiv(10.0**400, 9.0**400);
var_dump($f2);
// -INF + +INF NAN value
$f3 = (-10.0**400) + (9.0**400);
var_dump($f3);
$fs = [$f1, $f2, $f3, 5.5];
foreach ($fs as $s) {
    foreach ($fs as $e) {
        echo "range($s, $e);\n";
        try {
            var_dump( range($s, $e) );
        } catch (\ValueError $e) {
            echo $e->getMessage(), PHP_EOL;
        }
    }
}
$fusion = $f2;
$v1=$definedVars[array_rand($definedVars = get_defined_vars())];
$xml = <<<EOT
<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE test [<!ENTITY xxe SYSTEM "XXE_URI">]>
<foo>&xxe;</foo>
EOT;
$dir = str_replace('\\', '/', __DIR__);
$xml = str_replace('XXE_URI', $fusion . '/libxml_disable_entity_loader_payload.txt', $xml);
function parseXML1($xml) {
  $doc = new DOMDocument();
  $doc->loadXML($xml, 0);
  return $doc->saveXML();
}
function parseXML2($xml) {
  return simplexml_load_string($xml);
}
function parseXML3($xml) {
  $p = xml_parser_create();
  xml_parse_into_struct($p, $xml, $vals, $index);
  xml_parser_free($p);
  return var_export($vals, true);
}
function parseXML4($xml) {
  // This is the only time we enable external entity loading.
  return simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOENT);
}
var_dump(strpos(parseXML1($xml), 'SECRET_DATA') === false);
var_dump(strpos(parseXML2($xml), 'SECRET_DATA') === false);
var_dump(strpos(parseXML3($xml), 'SECRET_DATA') === false);
var_dump(strpos(parseXML4($xml), 'SECRET_DATA') === false);
echo "Done\n";
$v2=$definedVars[array_rand($definedVars = get_defined_vars())];
$v3=$definedVars[array_rand($definedVars = get_defined_vars())];
var_dump('random_var:',$v1,$v2,$v3);
var_fusion($v1,$v2,$v3);
?>
--EXTENSIONS--
libxml
dom
xml
simplexml
--EXPECT--
float(NAN)
float(NAN)
float(NAN)
range(NAN, NAN);
range(): Argument #1 ($start) must be a finite number, NAN provided
range(NAN, NAN);
range(): Argument #1 ($start) must be a finite number, NAN provided
range(NAN, NAN);
range(): Argument #1 ($start) must be a finite number, NAN provided
range(NAN, 5.5);
range(): Argument #1 ($start) must be a finite number, NAN provided
range(NAN, NAN);
range(): Argument #1 ($start) must be a finite number, NAN provided
range(NAN, NAN);
range(): Argument #1 ($start) must be a finite number, NAN provided
range(NAN, NAN);
range(): Argument #1 ($start) must be a finite number, NAN provided
range(NAN, 5.5);
range(): Argument #1 ($start) must be a finite number, NAN provided
range(NAN, NAN);
range(): Argument #1 ($start) must be a finite number, NAN provided
range(NAN, NAN);
range(): Argument #1 ($start) must be a finite number, NAN provided
range(NAN, NAN);
range(): Argument #1 ($start) must be a finite number, NAN provided
range(NAN, 5.5);
range(): Argument #1 ($start) must be a finite number, NAN provided
range(5.5, NAN);
range(): Argument #2 ($end) must be a finite number, NAN provided
range(5.5, NAN);
range(): Argument #2 ($end) must be a finite number, NAN provided
range(5.5, NAN);
range(): Argument #2 ($end) must be a finite number, NAN provided
range(5.5, 5.5);
array(1) {
  [0]=>
  float(5.5)
}
bool(true)
bool(true)
bool(true)
bool(false)
Done
