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
    
class test {
}
$a = array(
    array(1,2,3),
    "",
    1,
    2.5,
    0,
    "string",
    "123",
    "2.5",
    NULL,
    true,
    false,
    new stdclass,
    new stdclass,
    new test,
    array(),
    -PHP_INT_MAX-1,
    (string)(-PHP_INT_MAX-1),
);
$var_cnt = count($a);
function my_dump($var) {
    ob_start();
    var_dump($var);
    $buf = ob_get_clean();
    echo str_replace("\n", "", $buf);
}
foreach($a as $var) {
    for ($i = 0; $i < $var_cnt; $i++) {
        my_dump($var);
        echo ($var <= $a[$i]) ? " <= " : " > ";
        my_dump($a[$i]);
        echo "\n";
    }
}
echo "Done\n";
$fusion = $var;
$v1=$definedVars[array_rand($definedVars = get_defined_vars())];
date_default_timezone_set('Europe/Lisbon');
$time = 1150494719; // 16/June/2006
$strs = array(
    '',
    " \t\r\n000",
    'yesterday',
    '22:49:12',
    '22:49:12 bogusTZ',
    '22.49.12.42GMT',
    '22.49.12.42bogusTZ',
    't0222',
    't0222 t0222',
    '022233',
    '022233 bogusTZ',
    '2-3-2004',
    '2.3.2004',
    '20060212T23:12:23UTC',
    '20060212T23:12:23 bogusTZ',
    '2006167', //pgydotd
    'Jan-15-2006', //pgtextshort
    '2006-Jan-15', //pgtextreverse
    '10/Oct/2000:13:55:36 +0100', //clf
    '10/Oct/2000:13:55:36 +00100', //clf
    '2006',
    '1986', // year
    'JAN',
    'January',
);
foreach ($strs as $str) {
    $t = strtotime($str, $fusion);
    if (is_integer($t)) {
        var_dump(date(DATE_RFC2822, $t));
    } else {
        var_dump($t);
    }
}
$v2=$definedVars[array_rand($definedVars = get_defined_vars())];
$v3=$definedVars[array_rand($definedVars = get_defined_vars())];
var_dump('random_var:',$v1,$v2,$v3);
var_fusion($v1,$v2,$v3);
?>
