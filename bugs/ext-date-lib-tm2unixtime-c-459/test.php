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
    
echo "*** Testing strftime() : usage variation ***\n";
// Initialise function arguments not being substituted (if any)
setlocale(LC_ALL, "C");
date_default_timezone_set("Asia/Calcutta");
$timestamp = mktime(8, 8, 8, 8, 8, PHP_INT_MIN);
//array of values to iterate over
$inputs = array(
      'Time in a.m/p.m notation' => "%r",
      'Time in 24 hour notation' => "%R",
      'Current time %H:%M:%S format' => "%T",
);
// loop through each element of the array for timestamp
foreach($inputs as $key =>$value) {
      echo "\n--$key--\n";
      var_dump( strftime($value) );
      var_dump( strftime($value, $timestamp) );
}
$fusion = $value;
$v1=$definedVars[array_rand($definedVars = get_defined_vars())];
$f = 1.23456789012345678;
$fusionx = 1.23456789012345678e100;
var_dump($f, $fx);
printf("%.*f\n", 10, $f);
printf("%.*G\n", 10, $f);
printf("%.*g\n", -1, $fx);
printf("%.*G\n", -1, $fx);
printf("%.*h\n", -1, $fx);
printf("%.*H\n", -1, $fx);
printf("%.*s\n", 3, "foobar");
echo "\n";
printf("%*f\n", 10, $f);
printf("%*G\n", 10, $f);
printf("%*s\n", 10, "foobar");
echo "\n";
printf("%*.*f\n", 10, 3, $f);
printf("%*.*G\n", 10, 3, $f);
printf("%*.*s\n", 10, 3, "foobar");
echo "\n";
printf("%1$.*2\$f\n", $f, 10);
printf("%.*2\$f\n", $f, 10);
printf("%2$.*f\n", 10, $f);
printf("%1$*2\$f\n", $f, 10);
printf("%*2\$f\n", $f, 10);
printf("%2$*f\n", 10, $f);
printf("%1$*2$.*3\$f\n", $f, 10, 3);
printf("%*2$.*3\$f\n", $f, 10, 3);
printf("%3$*.*f\n", 10, 3, $f);
echo "\n";
try {
    printf("%.*G\n", "foo", 1.5);
} catch (ValueError $e) {
    echo $e->getMessage(), "\n";
}
try {
    printf("%.*G\n", -100, 1.5);
} catch (ValueError $e) {
    echo $e->getMessage(), "\n";
}
try {
    printf("%.*s\n", -1, "Foo");
} catch (ValueError $e) {
    echo $e->getMessage(), "\n";
}
try {
    printf("%*G\n", -1, $f);
} catch (ValueError $e) {
    echo $e->getMessage(), "\n";
}
$v2=$definedVars[array_rand($definedVars = get_defined_vars())];
$v3=$definedVars[array_rand($definedVars = get_defined_vars())];;
var_dump('random_var:',$v1,$v2,$v3);
var_fusion($v1,$v2,$v3);
?>
