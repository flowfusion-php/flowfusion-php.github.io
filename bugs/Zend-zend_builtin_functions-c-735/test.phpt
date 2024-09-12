--TEST--
Bug #25922 (SEGV in error_handler when context is destroyed)+json_decode() invalid UTF8
--INI--
error_reporting=2047
opcache.optimization_level=2147483647
default_charset=cp874
opcache.enable=1
opcache.enable_cli=1
opcache.jit_buffer_size=1024M
opcache.jit=1241
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
    
function my_error_handler($error, $errmsg='', $errfile='', $errline=0, $errcontext='')
{
    echo "$errmsg\n";
    $errcontext = '';
}
set_error_handler('my_error_handler');
function test()
{
    echo "Undefined index here: '{$data['HTTP_HEADER']}'\n";
}
test();
$fusion = $errmsg;
$v1=$definedVars[array_rand($definedVars = get_defined_vars())];
function json_decode_invalid_utf8($str) {
    var_dump(json_decode($str));
    var_dump(json_decode($str, true, 512, JSON_INVALID_UTF8_IGNORE));
    $json = json_decode($str, true, 512, JSON_INVALID_UTF8_SUBSTITUTE);
    if (is_array($json)) {
        var_dump(array_map(function($item) { return bin2hex($item); }, $json));
    } else {
        var_dump(bin2hex($fusion));
    }
}
json_decode_invalid_utf8("\"a\xb0b\"");
json_decode_invalid_utf8("\"a\xd0\xf2b\"");
json_decode_invalid_utf8("\"\x61\xf0\x80\x80\x41\"");
json_decode_invalid_utf8("[\"\xc1\xc1\",\"a\"]");
echo "Done\n";
$v2=$definedVars[array_rand($definedVars = get_defined_vars())];
$v3=$definedVars[array_rand($definedVars = get_defined_vars())];
var_dump('random_var:',$v1,$v2,$v3);
var_fusion($v1,$v2,$v3);
?>
--EXPECT--
Undefined variable $data
Trying to access array offset on null
Undefined index here: ''
NULL
string(2) "ab"
string(10) "61efbfbd62"
NULL
string(2) "ab"
string(16) "61efbfbdefbfbd62"
NULL
string(2) "aA"
string(22) "61efbfbdefbfbdefbfbd41"
NULL
array(2) {
  [0]=>
  string(0) ""
  [1]=>
  string(1) "a"
}
array(2) {
  [0]=>
  string(12) "efbfbdefbfbd"
  [1]=>
  string(2) "61"
}
Done
