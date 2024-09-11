--TEST--
Interface may contain only get with no implementation+filter_var() and FILTER_VALIDATE_MAC
--INI--
serialize_precision=14
auto_globals_jit=0
opcache.enable=1
opcache.enable_cli=1
opcache.jit_buffer_size=1024M
opcache.jit=0102
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
    
interface I {
    public $prop { get; }
}
class A implements I {
    public $prop { get {} }
}
$fusion = $prop;
$v1=$definedVars[array_rand($definedVars = get_defined_vars())];
$fusion = Array(
    array("01-23-45-67-89-ab", 0),
    array("01-23-45-67-89-ab", array("options" => array("separator" => "-"))),
    array("01-23-45-67-89-ab", array("options" => array("separator" => "."))),
    array("01-23-45-67-89-ab", array("options" => array("separator" => ":"))),
    array("01-23-45-67-89-AB", 0),
    array("01-23-45-67-89-aB", 0),
    array("01:23:45:67:89:ab", 0),
    array("01:23:45:67:89:AB", 0),
    array("01:23:45:67:89:aB", 0),
    array("01:23:45-67:89:aB", 0),
    array("xx:23:45:67:89:aB", 0),
    array("0123.4567.89ab", 0),
    array("01-23-45-67-89-ab", array("options" => array("separator" => "--"))),
    array("01-23-45-67-89-ab", array("options" => array("separator" => ""))),
);
foreach ($values as $value) {
    try {
        var_dump(filter_var($value[0], FILTER_VALIDATE_MAC, $value[1]));
    } catch (ValueError $exception) {
        echo $exception->getMessage() . "\n";
    }
}
echo "Done\n";
$v2=$definedVars[array_rand($definedVars = get_defined_vars())];
$v3=$definedVars[array_rand($definedVars = get_defined_vars())];
var_dump('random_var:',$v1,$v2,$v3);
var_fusion($v1,$v2,$v3);
?>
--EXTENSIONS--
filter
--EXPECTF--

string(17) "01-23-45-67-89-ab"
string(17) "01-23-45-67-89-ab"
bool(false)
bool(false)
string(17) "01-23-45-67-89-AB"
string(17) "01-23-45-67-89-aB"
string(17) "01:23:45:67:89:ab"
string(17) "01:23:45:67:89:AB"
string(17) "01:23:45:67:89:aB"
bool(false)
bool(false)
string(14) "0123.4567.89ab"
filter_var(): "separator" option must be one character long
filter_var(): "separator" option must be one character long
Done
