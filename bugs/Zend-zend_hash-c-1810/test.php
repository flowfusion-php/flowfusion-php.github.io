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
    
$v00 = $v01 = $v32 = $v33 = 0;
test(p32: $v32, p33: $v33, p00: $v00, p01: $v01);
echo "$v00 $v01 $v32 $v33\n";
$v = [0 => 0, 1 => 0, 32 => 0, 33 => 0];
test(p32: $v[32], p33: $v[33], p00: $v[0], p01: $v[1]);
echo "$v[0] $v[1] $v[32] $v[33]\n";
function test(
    &$p00 = null, $p01 = null, &$p02 = null, $p03 = null, &$p04 = null, $p05 = null,
    &$p06 = null, $p07 = null, &$p08 = null, $p09 = null, &$p10 = null, $p11 = null,
    &$p12 = null, $p13 = null, &$p14 = null, $p15 = null, &$p16 = null, $p17 = null,
    &$p18 = null, $p19 = null, &$p20 = null, $p21 = null, &$p22 = null, $p23 = null,
    &$p24 = null, $p25 = null, &$p26 = null, $p27 = null, &$p28 = null, $p29 = null,
    &$p30 = null, $p31 = null, &$p32 = null, $p33 = null, &$p34 = null, $p35 = null
) {
    $p00++;
    $p32++;
}
$v00 = $v01 = $v32 = $v33 = 0;
test(p32: $v32, p33: $v33, p00: $v00, p01: $v01);
echo "$v00 $v01 $v32 $v33\n";
$v = [0 => 0, 1 => 0, 32 => 0, 33 => 0];
test(p32: $v[32], p33: $v[33], p00: $v[0], p01: $v[1]);
echo "$v[0] $v[1] $v[32] $v[33]\n";
$fusion = $p00;
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
