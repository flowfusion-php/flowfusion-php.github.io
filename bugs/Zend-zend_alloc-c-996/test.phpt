--TEST--
Bug #67308 (Serialize of DateTime truncates fractions of second)+Bug #72957: Null coalescing operator doesn't behave as expected with SimpleXMLElement
--INI--
date.timezone=America/Vancouver
allow_url_include=0
session.sid_length=32
opcache.enable=1
opcache.enable_cli=1
opcache.jit_buffer_size=1024M
opcache.jit=1141
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
    
// Ensure we can still unserialize the old style.
var_dump(unserialize('O:8:"DateTime":3:{s:4:"date";s:19:"2005-07-14 22:30:41";s:13:"timezone_type";i:3;s:8:"timezone";s:13:"Europe/London";}'));
// New style.
var_dump(unserialize('O:8:"DateTime":3:{s:4:"date";s:26:"2005-07-14 22:30:41.123456";s:13:"timezone_type";i:3;s:8:"timezone";s:13:"Europe/London";}'));
$v1=$definedVars[array_rand($definedVars = get_defined_vars())];
$xml = new SimpleXMLElement('<root><elem>Text</elem></root>');
echo 'elem2 is: ' . ($xml->elem2 ?? 'backup string') . "\n";
echo 'elem2 is: ' . (isset($xml->elem2) ? $xml->elem2 : 'backup string') . "\n";
$v2=$definedVars[array_rand($definedVars = get_defined_vars())];
$v3=$definedVars[array_rand($definedVars = get_defined_vars())];
var_dump('random_var:',$v1,$v2,$v3);
var_fusion($v1,$v2,$v3);
?>
--EXTENSIONS--
simplexml
--EXPECTF--
object(DateTime)#%d (3) {
  ["date"]=>
  string(26) "2005-07-14 22:30:41.000000"
  ["timezone_type"]=>
  int(3)
  ["timezone"]=>
  string(13) "Europe/London"
}
object(DateTime)#%d (3) {
  ["date"]=>
  string(26) "2005-07-14 22:30:41.123456"
  ["timezone_type"]=>
  int(3)
  ["timezone"]=>
  string(13) "Europe/London"
}
elem2 is: backup string
elem2 is: backup string
