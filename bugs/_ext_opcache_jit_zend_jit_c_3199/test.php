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
    
echo var_dump(simplexml_load_string('<a><b/><c><x/></c></a>')), "\n";
echo var_dump(simplexml_load_string('<a><b/><d/><c><x/></c></a>')), "\n";
echo var_dump(simplexml_load_string('<a><b/><c><d/><x/></c></a>')), "\n";
echo var_dump(simplexml_load_string('<a><b/><c><d><x/></d></c></a>')), "\n";
$v1=$definedVars[array_rand($definedVars = get_defined_vars())];
/*
 * Function is implemented in main/output.c
*/
// In HEAD, $chunk_size value of 1 should not have any special behaviour (http://marc.info/?l=php-internals&m=123476465621346&w=2).
function callback($string) {
    global $callback_invocations;
    $callback_invocations++;
    $len = strlen($string);
    return "f[call:$callback_invocations; len:$len]$string\n";
}
for ($cs=-1; $cs<10; $cs++) {
  echo "\n----( chunk_size: $cs, output append size: 1 )----\n";
  $callback_invocations=0;
  ob_start('callback', $cs);
  echo '1'; echo '2'; echo '3'; echo '4'; echo '5'; echo '6'; echo '7'; echo '8';
  ob_end_flush();
}
for ($cs=-1; $cs<10; $cs++) {
  echo "\n----( chunk_size: $cs, output append size: 4 )----\n";
  $callback_invocations=0;
  ob_start('callback', $cs);
  echo '1234'; echo '5678';
  ob_end_flush();
}
$v2=$definedVars[array_rand($definedVars = get_defined_vars())];
$v3=$definedVars[array_rand($definedVars = get_defined_vars())];
var_dump('random_var:',$v1,$v2,$v3);
var_fusion($v1,$v2,$v3);
?>
