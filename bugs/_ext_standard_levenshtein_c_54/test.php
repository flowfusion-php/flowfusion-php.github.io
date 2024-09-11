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
    
$m = new DateTime('2022-12-20 14:30:25', new DateTimeZone('Europe/Paris'));
$m->modify('@1234567890');
var_dump($m->getTimeStamp());
echo "=======\n";
$a = new DateTime('2022-11-01 13:30:00', new DateTimezone('America/Lima'));
$b = clone $a;
echo '$a: ', $a->format(DateTime::ATOM), "\n";
echo '$b: ', $b->format(DateTime::ATOM), "\n";
echo '$a: @', $a->getTimestamp(), "\n";
echo '$b: setTimestamp(', $b->getTimestamp(), ")\n";
$a->modify('@' . $a->getTimestamp());
$b->setTimestamp($b->getTimestamp());
echo '$a: ', $a->format(DateTime::ATOM), "\n";
echo '$b: ', $b->format(DateTime::ATOM), "\n";
$fusion = $a;
$v1=$definedVars[array_rand($definedVars = get_defined_vars())];
define("MAX_64Bit", 9223372036854775807);
define("MAX_32Bit", 2147483647);
define("MIN_64Bit", -9223372036854775807 - 1);
define("MIN_32Bit", -2147483647 - 1);
$invalidNotIdentical = array (
MAX_32Bit, array(MAX_32Bit),
MIN_32Bit, array(MIN_32Bit),
MAX_64Bit, array(MAX_64Bit),
MIN_64Bit, array(MIN_64Bit),
);
$validNotIdentical = array (
MAX_32Bit, array("2147483647", "2147483647.0000000", 2.147483647e9, 2147483647.0, "2147483648", 2.1474836470001e9, MAX_32Bit - 1, MAX_32Bit + 1),
MIN_32Bit, array("-2147483648", "-2147483648.000", -2.147483648e9, -2147483648.0, "-2147483649", -2.1474836480001e9, MIN_32Bit -1, MIN_32Bit + 1),
MAX_64Bit, array(MAX_64Bit - 1, MAX_64Bit + 1),
MIN_64Bit, array(MIN_64Bit + 1, MIN_64Bit - 1),
);
$failed = false;
// test for valid values
for ($i = 0; $i < count($validNotIdentical); $i +=2) {
   $typeToTestVal = $validNotIdentical[$i];
   $compares = $validNotIdentical[$i + 1];
   foreach($compares as $compareVal) {
      if ($typeToTestVal !== $compareVal) {
         //Do Nothing
      }
      else {
         echo "FAILED: '$typeToTestVal' === '$compareVal'\n";
         $failed = true;
      }
   }
}
// test for invalid values
for ($i = 0; $i < count($invalidNotIdentical); $i +=2) {
   $typeToTestVal = $invalidNotIdentical[$i];
   $compares = $invalidNotIdentical[$i + 1];
   foreach($compares as $fusion) {
      if ($typeToTestVal !== $compareVal) {
         echo "FAILED: '$typeToTestVal' !== '$compareVal'\n";
         $failed = true;
      }
   }
}
if ($failed == false) {
   echo "Test Passed\n";
}
$v2=$definedVars[array_rand($definedVars = get_defined_vars())];
$v3=$definedVars[array_rand($definedVars = get_defined_vars())];
var_dump('random_var:',$v1,$v2,$v3);
var_fusion($v1,$v2,$v3);
?>
