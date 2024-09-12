--TEST--
Test for bug #75851: Year component overflow with date formats "c", "o", "r" and "y"+Bug #30096 (gmmktime does not return the correct time)
--INI--
date.timezone = UTC
error_reporting=2047
date.timezone=Asia/Singapore
date.timezone=Europe/Berlin
--SKIPIF--
<?php if (PHP_INT_SIZE != 8) die("skip 64-bit only"); ?>
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
                // Prepare arguments
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
    try{
        fuzz_internal_interface($vars);
        fuzz_internal_interface($vars);
        fuzz_internal_interface($vars);
    } catch (ReflectionException $e) {
        echo("Error: " . $e->getMessage());
    }
    return $result;
}
    
echo date(DATE_ATOM."\n".DATE_RFC2822."\nc\nr\no\ny\nY\nU\n\n", PHP_INT_MIN);
echo date(DATE_ATOM."\n".DATE_RFC2822."\nc\nr\no\ny\nY\nU\n\n", 67767976233532799);
echo date(DATE_ATOM."\n".DATE_RFC2822."\nc\nr\no\ny\nY\nU\n\n", 67767976233532800);
echo date(DATE_ATOM."\n".DATE_RFC2822."\nc\nr\no\ny\nY\nU\n\n", PHP_INT_MAX);
$v1=$definedVars[array_rand($definedVars = get_defined_vars())];
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
    echo "gmmktime($hour,$minute,$second,$month,$day,$year): ";
    $tsold = $ts;
    $ts = gmmktime($hour, $minute, $second, $month, $day, $year);
    echo $ts, " | gmdate('r', $ts):", gmdate('r', $ts);
    if ($tsold > 0) {
        echo " | Diff: " . ($ts - $tsold);
    }
    echo "\n";
}
$v2=$definedVars[array_rand($definedVars = get_defined_vars())];
$v3=$definedVars[array_rand($definedVars = get_defined_vars())];;
var_dump('random_var:',$v1,$v2,$v3);
var_fusion($v1,$v2,$v3);
?>
--EXPECT--
-292277022657-01-27T08:29:52+00:00
Sun, 27 Jan -292277022657 08:29:52 +0000
-292277022657-01-27T08:29:52+00:00
Sun, 27 Jan -292277022657 08:29:52 +0000
-292277022657
-57
-292277022657
-9223372036854775808

2147483647-12-31T23:59:59+00:00
Tue, 31 Dec 2147483647 23:59:59 +0000
2147483647-12-31T23:59:59+00:00
Tue, 31 Dec 2147483647 23:59:59 +0000
2147483648
47
2147483647
67767976233532799

2147483648-01-01T00:00:00+00:00
Wed, 01 Jan 2147483648 00:00:00 +0000
2147483648-01-01T00:00:00+00:00
Wed, 01 Jan 2147483648 00:00:00 +0000
2147483648
48
2147483648
67767976233532800

292277026596-12-04T15:30:07+00:00
Sun, 04 Dec 292277026596 15:30:07 +0000
292277026596-12-04T15:30:07+00:00
Sun, 04 Dec 292277026596 15:30:07 +0000
292277026596
96
292277026596
9223372036854775807
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
