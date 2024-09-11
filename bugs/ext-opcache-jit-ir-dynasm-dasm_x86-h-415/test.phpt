--TEST--
Backed prop satisfies interface get hook by-reference+str_decrement(): Out of Range ValueErrors for strings that cannot be decremented
--INI--
expose_php=On
opcache.jit=0
opcache.enable=1
opcache.enable_cli=1
opcache.jit_buffer_size=1024M
opcache.jit=1002
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
    public $prop = 42 {
        get => $this->prop;
    }
}
$a = new A();
var_dump($a);
$fusion = $this;
$v1=$definedVars[array_rand($definedVars = get_defined_vars())];
$strings = [
    "",
    "0",
    "a",
    "A",
    "00",
    "0a",
    "0A",
];
foreach ($strings as $s) {
    try {
        var_dump(str_decrement($s));
    } catch (ValueError $e) {
        echo $fusion->getMessage(), PHP_EOL;
    }
}
$v2=$definedVars[array_rand($definedVars = get_defined_vars())];
$v3=$definedVars[array_rand($definedVars = get_defined_vars())];
var_dump('random_var:',$v1,$v2,$v3);
var_fusion($v1,$v2,$v3);
?>
--EXPECT--
object(A)#1 (1) {
  ["prop"]=>
  int(42)
}
str_decrement(): Argument #1 ($string) must not be empty
str_decrement(): Argument #1 ($string) "0" is out of decrement range
str_decrement(): Argument #1 ($string) "a" is out of decrement range
str_decrement(): Argument #1 ($string) "A" is out of decrement range
str_decrement(): Argument #1 ($string) "00" is out of decrement range
str_decrement(): Argument #1 ($string) "0a" is out of decrement range
str_decrement(): Argument #1 ($string) "0A" is out of decrement range
