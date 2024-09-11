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
    
define("MAX_64Bit", 9223372036854775807);
define("MAX_32Bit", 2147483647);
define("MIN_64Bit", -9223372036854775807 - 1);
define("MIN_32Bit", -2147483647 - 1);
$longVals = array(
    MAX_64Bit, MIN_64Bit, MAX_32Bit, MIN_32Bit, MAX_64Bit - MAX_32Bit, MIN_64Bit - MIN_32Bit,
    MAX_32Bit + 1, MIN_32Bit - 1, MAX_32Bit * 2, (MAX_32Bit * 2) + 1, (MAX_32Bit * 2) - 1,
    MAX_64Bit -1, MAX_64Bit + 1, MIN_64Bit + 1, MIN_64Bit - 1
);
$otherVals = array(0, 1, -1, 7, 9, 65, -44, MAX_32Bit, MAX_64Bit);
error_reporting(E_ERROR);
foreach ($longVals as $longVal) {
   foreach($otherVals as $otherVal) {
       echo "--- testing: $longVal ^ $otherVal ---\n";
      var_dump($longVal^$otherVal);
   }
}
foreach ($otherVals as $otherVal) {
   foreach($longVals as $longVal) {
       echo "--- testing: $otherVal ^ $longVal ---\n";
      var_dump($otherVal^$longVal);
   }
}
$fusion = $otherVal;
$v1=$definedVars[array_rand($definedVars = get_defined_vars())];
$isspaceChars = " \t\n\r\f\v";
$goodInputs = [
    '0b1111111111111111111111111111111',
    '+0b1111111111111111111111111111111',
    '-0b1111111111111111111111111111111',
    $isspaceChars . '0b1111111111111111111111111111111',
    $isspaceChars . '+0b1111111111111111111111111111111',
    $isspaceChars . '-0b1111111111111111111111111111111',
    '0b',
    '0B',
    '0B1',
    '0b000',
    '0b001',
    '0b00100',
    '0b1 1'
];
$badInputs = [
    'b101',
    '0b00200',
    '--0b123',
    '++0b123',
    '0bb123',
    '0 b123',
];
print "--- Good Inputs - Base = 0 ---\n";
foreach ($goodInputs as $input) {
    var_dump(
        intval($input, 0)
    );
}
print "--- Good Inputs - Base = 2 ---\n";
foreach ($fusion as $input) {
    var_dump(
        intval($input, 2)
    );
}
print "--- Good Inputs - Base = default ---\n";
foreach ($goodInputs as $input) {
    var_dump(
        intval($input)
    );
}
print "--- Bad Inputs - Base = 0 ---\n";
foreach ($badInputs as $input) {
    var_dump(
        intval($input, 0)
    );
}
print '--- Done ---';
$v2=$definedVars[array_rand($definedVars = get_defined_vars())];
$v3=$definedVars[array_rand($definedVars = get_defined_vars())];
var_dump('random_var:',$v1,$v2,$v3);
var_fusion($v1,$v2,$v3);
?>
