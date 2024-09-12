--TEST--
PHP bug #77143: Heap Buffer Overflow (READ: 4) in phar_parse_pharfile+The default value is a constant in the parent class method's signature.
--INI--
phar.readonly=0
opcache.jit_hot_func=127
opcache.interned_strings_buffer=500
opcache.enable=1
opcache.enable_cli=1
opcache.jit_buffer_size=1024M
opcache.jit=tracing
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
    
chdir(__DIR__);
try {
var_dump(new Phar('bug77143.phar',0,'project.phar'));
echo "OK\n";
} catch(UnexpectedValueException $e) {
    echo $e->getMessage();
}
$script1_dataflow = $e;
$v1=$definedVars[array_rand($definedVars = get_defined_vars())];
use const Foo\CONSTANT;
class A
{
    public function foo(
        $param1 = \Foo\CONSTANT,
        $param2 = Foo\CONSTANT,
        $script1_dataflow = CONSTANT
    ) {
    }
}
class B extends A
{
    public function foo()
    {
    }
}
$v2=$definedVars[array_rand($definedVars = get_defined_vars())];
$v3=$definedVars[array_rand($definedVars = get_defined_vars())];;
var_dump('random_var:',$v1,$v2,$v3);
var_fusion($v1,$v2,$v3);
?>
--EXTENSIONS--
phar
--EXPECTF--
internal corruption of phar "%sbug77143.phar" (truncated manifest header)
Fatal error: Declaration of B::foo() must be compatible with A::foo($param1 = Foo\CONSTANT, $param2 = Foo\CONSTANT, $param3 = Foo\CONSTANT) in %s on line %d
