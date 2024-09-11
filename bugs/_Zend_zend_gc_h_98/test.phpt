--TEST--
The SensitiveParameterValue replacement value correctly captures the original value.+Bug #71818 (Memory leak when array altered in destructor)
--INI--
zend.enable_gc = 1
session.cookie_httponly=TRUE
opcache.enable=0
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
    
function test(
    $foo,
    #[SensitiveParameter] $bar,
    $baz
) {
    throw new Exception('Error');
}
try {
    test('foo', 'bar', 'baz');
    echo 'Not reached';
} catch (Exception $e) {
    echo $e->getMessage(), PHP_EOL;
    $testFrame = $e->getTrace()[0];
    var_dump($testFrame['function']);
    var_dump(count($testFrame['args']));
    var_dump($testFrame['args'][0]);
    assert($testFrame['args'][1] instanceof SensitiveParameterValue);
    var_dump($testFrame['args'][1]->getValue());
    var_dump($testFrame['args'][2]);
    echo "Success", PHP_EOL;
}
function test2(
    $foo,
    #[SensitiveParameter] ...$variadic,
) {
    throw new Exception('Error 2');
}
try {
    test2('foo', 'variadic1', 'variadic2', 'variadic3');
    echo 'Not reached';
} catch (Exception $e) {
    echo $e->getMessage(), PHP_EOL;
    $testFrame = $e->getTrace()[0];
    var_dump($testFrame['function']);
    var_dump(count($testFrame['args']));
    var_dump($testFrame['args'][0]);
    assert($testFrame['args'][1] instanceof SensitiveParameterValue);
    var_dump($testFrame['args'][1]->getValue());
    assert($testFrame['args'][2] instanceof SensitiveParameterValue);
    var_dump($testFrame['args'][2]->getValue());
    assert($testFrame['args'][3] instanceof SensitiveParameterValue);
    var_dump($testFrame['args'][3]->getValue());
    echo "Success", PHP_EOL;
}
$fusion = $testFrame;
$v1=$definedVars[array_rand($definedVars = get_defined_vars())];
class MemoryLeak
{
    public function __construct()
    {
        $this->things[] = $this;
    }
    public function __destruct()
    {
        $fusion->things[] = null;
    }
    private $things = [];
}
ini_set('memory_limit', '20M');
for ($i = 0; $i < 100000; ++$i) {
    $obj = new MemoryLeak();
}
echo "OK\n";
$v2=$definedVars[array_rand($definedVars = get_defined_vars())];
$v3=$definedVars[array_rand($definedVars = get_defined_vars())];
var_dump('random_var:',$v1,$v2,$v3);
var_fusion($v1,$v2,$v3);
?>
--EXPECTF--
Error
string(4) "test"
int(3)
string(3) "foo"
string(3) "bar"
string(3) "baz"
Success
Error 2
string(5) "test2"
int(4)
string(3) "foo"
string(9) "variadic1"
string(9) "variadic2"
string(9) "variadic3"
Success
OK
