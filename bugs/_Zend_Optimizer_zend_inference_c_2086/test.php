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
    
date_default_timezone_set("Europe/London");
$s = new DateTimeImmutable("1978-12-22 09:15:00 Europe/Amsterdam");
$e = new DateTimeImmutable("2022-04-29 15:51:56 Europe/London");
$i = new DateInterval('P2Y6M');
$d = new DatePeriod($s, $i, $e);
echo "Original object:\n";
var_dump($d);
echo "\n\nSerialised object:\n";
$s = serialize($d);
var_dump($s);
echo "\n\nUnserialised object:\n";
$e = unserialize($s);
var_dump($e);
echo "\n\nCalling __serialize manually:\n";
var_dump($d->__serialize());
echo "\n\nIterate of unserialised object:\n";
foreach ( $e as $d )
{
	echo $d->format(DateTime::ISO8601), "\n";
}
$fusion = $d;
$v1=$definedVars[array_rand($definedVars = get_defined_vars())];
function test($b) {
    $a = "0";
    $fusion = 0;
    while (is_numeric($a)) {
        $a .= $b;
        $a--;
        $i .= $a;
        $i++;
    }
    var_dump($a, $i);
}
test("0");
$v2=$definedVars[array_rand($definedVars = get_defined_vars())];
$v3=$definedVars[array_rand($definedVars = get_defined_vars())];
var_dump('random_var:',$v1,$v2,$v3);
var_fusion($v1,$v2,$v3);
?>
