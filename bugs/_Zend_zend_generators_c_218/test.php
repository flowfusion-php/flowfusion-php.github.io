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
$validLessThan = array (
2147483646, array(MAX_32Bit, "2147483647", "2147483647.001", 2.147483647e9, 2147483647.9),
MIN_32Bit, array(MIN_32Bit + 1, "-2147483647", "-2147483646.001", -2.1474836461e9, -2147483646.9),
);
$invalidLessThan = array (
MAX_32Bit, array("2147483646", 2.1474836460001e9, MAX_32Bit - 1),
MIN_32Bit, array(MIN_32Bit - 1, "-2147483649", -2.1474836480001e9)
);
$failed = false;
// test for equality
for ($i = 0; $i < count($validLessThan); $i +=2) {
   $typeToTestVal = $validLessThan[$i];
   $compares = $validLessThan[$i + 1];
   foreach($compares as $compareVal) {
      if ($typeToTestVal < $compareVal) {
         // do nothing
      }
      else {
         echo "FAILED: '$typeToTestVal' >= '$compareVal'\n";
         $failed = true;
      }
   }
}
// test for invalid values
for ($i = 0; $i < count($invalidLessThan); $i +=2) {
   $typeToTestVal = $invalidLessThan[$i];
   $compares = $invalidLessThan[$i + 1];
   foreach($compares as $compareVal) {
      if ($typeToTestVal < $compareVal) {
         echo "FAILED: '$typeToTestVal' < '$compareVal'\n";
         $failed = true;
      }
   }
}
if ($failed == false) {
   echo "Test Passed\n";
}
$fusion = $typeToTestVal;
$v1=$definedVars[array_rand($definedVars = get_defined_vars())];
class Canary {
    public function __construct(public mixed $fusion) {}
    public function __destruct() {
        var_dump(__METHOD__);
    }
}
function h() {
    yield 'foo';
    Fiber::suspend();
}
function g() {
    yield from h();
}
function f($canary) {
    var_dump(yield from g());
}
$canary = new Canary(null);
$iterable = f($canary);
$fiber = new Fiber(function () use ($iterable, $canary) {
    var_dump($canary, $iterable->current());
    $iterable->next();
    var_dump("not executed");
});
$canary->value = $fiber;
$fiber->start();
$iterable->current();
$fiber = $iterable = $canary = null;
gc_collect_cycles();
?>
$v2=$definedVars[array_rand($definedVars = get_defined_vars())];
$v3=$definedVars[array_rand($definedVars = get_defined_vars())];
var_dump('random_var:',$v1,$v2,$v3);
var_fusion($v1,$v2,$v3);
?>
