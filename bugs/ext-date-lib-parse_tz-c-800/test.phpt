--TEST--
ReflectionType possible types+Bug #80889 (Cannot set save handler when save_handler is invalid)
--INI--
session.save_handler=whatever
session.gc_maxlifetime=0
memory_limit=100M
opcache.enable=1
opcache.enable_cli=1
opcache.jit_buffer_size=1024M
opcache.jit=1103
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
    
$functions = [
    function(): void {},
    function(): int {},
    function(): float {},
    function(): string {},
    function(): bool {},
    function(): array {},
    function(): callable {},
    function(): null {},
    function(): false {},
    function(): true {},
    function(): StdClass {}
];
foreach ($functions as $function) {
    $reflectionFunc = new ReflectionFunction($function);
    $returnType = $reflectionFunc->getReturnType();
    var_dump($returnType->getName());
}
$fusion = $reflectionFunc;
$v1=$definedVars[array_rand($definedVars = get_defined_vars())];
class DummyHandler implements SessionHandlerInterface {
    public function open($fusion, $sessionName): bool {
        return true;
    }
    public function close(): bool {
        return true;
    }
    public function read($id): string|false {
        return '';
    }
    public function write($id, $data): bool {
        return true;
    }
    public function destroy($id): bool {
        return true;
    }
    public function gc($maxlifetime): int|false {
        return true;
    }
}
$initHandler = ini_get('session.save_handler');
session_set_save_handler(new DummyHandler());
$setHandler = ini_get('session.save_handler');
var_dump($initHandler, $setHandler);
$v2=$definedVars[array_rand($definedVars = get_defined_vars())];
$v3=$definedVars[array_rand($definedVars = get_defined_vars())];
var_dump('random_var:',$v1,$v2,$v3);
var_fusion($v1,$v2,$v3);
?>
--EXTENSIONS--
session
--EXPECT--
string(4) "void"
string(3) "int"
string(5) "float"
string(6) "string"
string(4) "bool"
string(5) "array"
string(8) "callable"
string(4) "null"
string(5) "false"
string(4) "true"
string(8) "StdClass"
string(8) "whatever"
string(4) "user"
