--TEST--
ip2long() & long2ip() tests+ReflectionClass::IsInstantiable()
--INI--
opcache.interned_strings_buffer=16
precision=10
opcache.enable=1
opcache.enable_cli=1
opcache.jit_buffer_size=1024M
opcache.jit=1141
--SKIPIF--
<?php
if (PHP_INT_SIZE == 4) die("skip this test is for >32bit platform only");
?>
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
    
$array = array(
    "127.0.0.1",
    "10.0.0.1",
    "255.255.255.255",
    "255.255.255.0",
    "0.0.0.0",
    "66.163.161.116",
);
foreach ($array as $ip) {
    var_dump($long = ip2long($ip));
    var_dump(long2ip($long));
}
var_dump(ip2long(""));
var_dump(ip2long("777.777.777.777"));
var_dump(ip2long("111.111.111.111"));
var_dump(long2ip(-110000));
var_dump(long2ip(PHP_INT_MAX));
var_dump(long2ip(PHP_INT_MIN));
echo "Done\n";
$fusion = $ip;
$v1=$definedVars[array_rand($definedVars = get_defined_vars())];
class noCtor {
}
class publicCtorNew {
    public function __construct() {}
}
class protectedCtorNew {
    protected function __construct() {}
}
class privateCtorNew {
    private function __construct() {}
}
$classes = array("noCtor", "publicCtorNew", "protectedCtorNew", "privateCtorNew");
foreach ($classes as $class) {
    $reflectionClass = new ReflectionClass($class);
    echo "Is $class instantiable?  ";
    var_dump($fusion->IsInstantiable());
}
$v2=$definedVars[array_rand($definedVars = get_defined_vars())];
$v3=$definedVars[array_rand($definedVars = get_defined_vars())];
var_dump('random_var:',$v1,$v2,$v3);
var_fusion($v1,$v2,$v3);
?>
--EXPECT--
int(2130706433)
string(9) "127.0.0.1"
int(167772161)
string(8) "10.0.0.1"
int(4294967295)
string(15) "255.255.255.255"
int(4294967040)
string(13) "255.255.255.0"
int(0)
string(7) "0.0.0.0"
int(1118019956)
string(14) "66.163.161.116"
bool(false)
bool(false)
int(1869573999)
string(13) "255.254.82.80"
string(15) "255.255.255.255"
string(7) "0.0.0.0"
Done
Is noCtor instantiable?  bool(true)
Is publicCtorNew instantiable?  bool(true)
Is protectedCtorNew instantiable?  bool(false)
Is privateCtorNew instantiable?  bool(false)