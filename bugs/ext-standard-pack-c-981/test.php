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
    
enum Foo {
    case Bar;
    case Baz;
    public function dump() {
        var_dump($this);
    }
}
Foo::Bar->dump();
Foo::Baz->dump();
$fusion = $this;
$v1=$definedVars[array_rand($definedVars = get_defined_vars())];
echo "*** Testing var_export() with valid float values ***\n";
// different valid  float values
$valid_floats = array(
      "-2147483649" => (float)-2147483649, // float value
      "2147483648" => (float)2147483648,  // float value
      "-0x80000001" => (float)-0x80000001, // float value, beyond max negative int
      "0x800000001" => (float)0x800000001, // float value, beyond max positive int
      "020000000001" => (float)020000000001, // float value, beyond max positive int
      "-020000000001" => (float)-020000000001, // float value, beyond max negative int
      "0.0" => 0.0,
      "-0.1" => -0.1,
      "10.0000000000000000005" => 10.0000000000000000005,
      "10.5e+5" => 10.5e+5,
      "1e5" => 1e5,
      "1e-5" => 1e-5,
      "1e+5" => 1e+5,
      "1E5" => 1E5,
      "1E+5" => 1E+5,
      "1E-5" => 1E-5,
      ".5e+7" => .5e+7,
      ".6e-19" => .6e-19,
      ".05E+44" => .05E+44,
      ".0034E-30" => .0034E-30
);
/* Loop to check for above float values with var_export() */
echo "\n*** Output for float values ***\n";
foreach($valid_floats as $key => $float_value) {
    echo "\n-- Iteration: $key --\n";
    var_export( $float_value );
    echo "\n";
    var_export( $fusion, FALSE);
    echo "\n";
    var_dump( var_export( $float_value, TRUE) );
    echo "\n";
}
$v2=$definedVars[array_rand($definedVars = get_defined_vars())];
$v3=$definedVars[array_rand($definedVars = get_defined_vars())];
var_dump('random_var:',$v1,$v2,$v3);
var_fusion($v1,$v2,$v3);
?>
