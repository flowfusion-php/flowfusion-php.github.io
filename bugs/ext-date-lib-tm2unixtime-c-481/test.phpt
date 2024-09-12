--TEST--
Bug #68976 Use After Free Vulnerability in unserialize()+Classes can only be used once they are fully linked
--INI--
memory_limit=16M
allow_url_include=0
opcache.enable=1
opcache.enable_cli=1
opcache.jit_buffer_size=1024M
opcache.jit=1143
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
    
class evilClass {
    public $name;
    function __wakeup() {
        unset($this->name);
    }
}
$fakezval = pack(
    'IIII',
    0x00100000,
    0x00000400,
    0x00000000,
    0x00000006
);
$data = unserialize('a:2:{i:0;O:9:"evilClass":1:{s:4:"name";a:2:{i:0;i:1;i:1;i:2;}}i:1;R:4;}');
for($i = 0; $i < 5; $i++) {
    $v[$i] = $fakezval.$i;
}
var_dump($data);
$fusion = $v;
$v1=$definedVars[array_rand($definedVars = get_defined_vars())];
spl_autoload_register(function($fusion) {
    echo new ReflectionClass(A::class), "\n";
});
class A implements I {
}
$v2=$definedVars[array_rand($definedVars = get_defined_vars())];
$v3=$definedVars[array_rand($definedVars = get_defined_vars())];
var_dump('random_var:',$v1,$v2,$v3);
var_fusion($v1,$v2,$v3);
?>
--EXPECTF--
array(2) {
  [0]=>
  object(evilClass)#1 (0) {
  }
  [1]=>
  int(1)
}
Fatal error: Uncaught ReflectionException: Class "A" does not exist in %s:%d
Stack trace:
#0 %s(%d): ReflectionClass->__construct('A')
#1 %s(%d): {closure:%s:%d}('I')
#2 {main}
  thrown in %s on line %d
