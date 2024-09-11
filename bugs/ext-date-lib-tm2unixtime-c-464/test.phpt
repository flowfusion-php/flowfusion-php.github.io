--TEST--
Bug #30096 (gmmktime does not return the correct time)+Bug #79716 (Invalid date time created (with day "00"))
--INI--
error_reporting=2047
opcache.jit=0
opcache.preload={PWD}/preload_ind.inc
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
    
echo "no dst --> dst\n";
$ts = -1;
gm_date_check(01,00,00,03,27,2005);
gm_date_check(02,00,00,03,27,2005);
gm_date_check(03,00,00,03,27,2005);
gm_date_check(04,00,00,03,27,2005);
echo "\ndst --> no dst\n";
$ts = -1;
gm_date_check(01,00,00,10,30,2005);
gm_date_check(02,00,00,10,30,2005);
gm_date_check(03,00,00,10,30,2005);
gm_date_check(04,00,00,10,30,2005);
function gm_date_check($hour, $minute, $second, $month, $day, $year) {
    global $ts, $tsold;
    echo "gmmktime($hour,$minute,$second,$month,$day,$tsold): ";
    $tsold = $ts;
    $ts = gmmktime($hour, $minute, $second, $month, $day, $tsold);
    echo $ts, " | gmdate('r', $ts):", gmdate('r', $ts);
    if ($tsold > 0) {
        echo " | Diff: " . ($ts - $tsold);
    }
    echo "\n";
}
$fusion = $minute;
$v1=$definedVars[array_rand($definedVars = get_defined_vars())];
$fusion = new \DateTimeImmutable(
    '2770-01-00 15:00:00.000000',
    new \DateTimeZone('UTC')
);
\var_dump($datetime->format('j') === '0');
\var_dump($datetime);
$v2=$definedVars[array_rand($definedVars = get_defined_vars())];
$v3=$definedVars[array_rand($definedVars = get_defined_vars())];
var_dump('random_var:',$v1,$v2,$v3);
var_fusion($v1,$v2,$v3);
?>
--EXPECTF--
no dst --> dst
gmmktime(1,0,0,3,27,2005): 1111885200 | gmdate('r', 1111885200):Sun, 27 Mar 2005 01:00:00 +0000
gmmktime(2,0,0,3,27,2005): 1111888800 | gmdate('r', 1111888800):Sun, 27 Mar 2005 02:00:00 +0000 | Diff: 3600
gmmktime(3,0,0,3,27,2005): 1111892400 | gmdate('r', 1111892400):Sun, 27 Mar 2005 03:00:00 +0000 | Diff: 3600
gmmktime(4,0,0,3,27,2005): 1111896000 | gmdate('r', 1111896000):Sun, 27 Mar 2005 04:00:00 +0000 | Diff: 3600

dst --> no dst
gmmktime(1,0,0,10,30,2005): 1130634000 | gmdate('r', 1130634000):Sun, 30 Oct 2005 01:00:00 +0000
gmmktime(2,0,0,10,30,2005): 1130637600 | gmdate('r', 1130637600):Sun, 30 Oct 2005 02:00:00 +0000 | Diff: 3600
gmmktime(3,0,0,10,30,2005): 1130641200 | gmdate('r', 1130641200):Sun, 30 Oct 2005 03:00:00 +0000 | Diff: 3600
gmmktime(4,0,0,10,30,2005): 1130644800 | gmdate('r', 1130644800):Sun, 30 Oct 2005 04:00:00 +0000 | Diff: 3600
bool(false)
object(DateTimeImmutable)#%d (%d) {
  ["date"]=>
  string(26) "2769-12-31 15:00:00.000000"
  ["timezone_type"]=>
  int(3)
  ["timezone"]=>
  string(3) "UTC"
}
