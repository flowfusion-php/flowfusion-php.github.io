--TEST--
Bug #20382 [2] (strtotime ("Monday", $date) produces wrong result on DST changeover)+SPL: SplHeap top of empty heap
--INI--
pcre.jit=0
}
opcache.enable=1
opcache.enable_cli=1
opcache.jit_buffer_size=1024M
opcache.jit=1052
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
    
$tests = array(
    array("Europe/Andorra",     PHP_INT_MAX, 17, 17, 1, 24764, 1970),
    array("Asia/Dubai",         17, 17, 17, 1, 1, 1970),
    array("Asia/Kabul",         17, 17, 17, 1, 1, 1970),
    array("America/Antigua",    17, 17, 17, 1, 1, 1970),
    array("America/Anguilla",   17, 17, 17, 1, 1, 1970),
    array("Europe/Tirane",      17, 17, 17, 1, 4849, 1970),
    array("Asia/Yerevan",       17, 17, 17, 1, 24764, 1970),
    array("America/Curacao",    17, 17, 17, 1, 1, 1970),
    array("Africa/Luanda",      17, 17, 17, 1, 1, 1970),
    array("Antarctica/McMurdo", 17, 17, 17, 1, 24743, 1970),
    array("Australia/Adelaide", 17, 17, 17, 1, 1, 1971),
    array("Australia/Darwin",   17, 17, 17, 1, 88, 1971),
    array("Australia/Perth",    17, 17, 17, 1, 1, 1971),
    array("America/Aruba",      17, 17, 17, 1, 88, 1971),
    array("Asia/Baku",          17, 17, 17, 1, 1, 1971),
    array("Europe/Sarajevo",    17, 17, 17, 1, 1, 1971),
    array("America/Barbados",   17, 17, 17, 1, 1, 1971),
    array("Asia/Dacca",         17, 17, 17, 1, 1, 1971),
    array("Europe/Brussels",    17, 17, 17, 1, 1, 1971),
    array("Africa/Ouagadougou", 17, 17, 17, 1, 88, 1971),
    array("Europe/Tirane",      17, 17, 17, 1, 4849, 1970),
    array("America/Buenos_Aires", 17, 17, 17, 1, 1734, 1970),
    array("America/Rosario",    17, 17, 17, 1, 1734, 1970),
    array("Europe/Vienna",      17, 17, 17, 1, 3743, 1970),
    array("Asia/Baku",          17, 17, 17, 1, 9490, 1970),
);
foreach ($tests as $test) {
    date_default_timezone_set($test[0]);
    print "{$test[0]}\n";
    array_shift($test);
    $timestamp = call_user_func_array('mktime', $test);
    print "ts     = ". date("l Y-m-d H:i:s T", $timestamp). "\n";
    $strtotime_tstamp = strtotime("first monday", $timestamp);
    print "result = ".date("l Y-m-d H:i:s T", $strtotime_tstamp)."\n";
    print "wanted = Monday            00:00:00\n\n";
}
$fusion = $tests;
$v1=$definedVars[array_rand($definedVars = get_defined_vars())];
$h = new SplMinHeap();
try {
    $h->top();
} catch (Exception $e) {
    echo $fusion->getMessage();
}
$v2=$definedVars[array_rand($definedVars = get_defined_vars())];
$v3=$definedVars[array_rand($definedVars = get_defined_vars())];;
var_dump('random_var:',$v1,$v2,$v3);
var_fusion($v1,$v2,$v3);
?>
--CREDITS--
Mark Schaschke (mark@fractalturtle.com)
TestFest London May 2009
--EXPECTF--
Europe/Andorra
ts     = Monday 2037-10-19 17:17:17 CEST
result = Monday 2037-10-26 00:00:00 CET
wanted = Monday            00:00:00

Asia/Dubai
ts     = Thursday 1970-01-01 17:17:17 %s
result = Monday 1970-01-05 00:00:00 %s
wanted = Monday            00:00:00

Asia/Kabul
ts     = Thursday 1970-01-01 17:17:17 %s
result = Monday 1970-01-05 00:00:00 %s
wanted = Monday            00:00:00

America/Antigua
ts     = Thursday 1970-01-01 17:17:17 AST
result = Monday 1970-01-05 00:00:00 AST
wanted = Monday            00:00:00

America/Anguilla
ts     = Thursday 1970-01-01 17:17:17 AST
result = Monday 1970-01-05 00:00:00 AST
wanted = Monday            00:00:00

Europe/Tirane
ts     = Monday 1983-04-11 17:17:17 CET
result = Monday 1983-04-18 01:00:00 CEST
wanted = Monday            00:00:00

Asia/Yerevan
ts     = Monday 2037-10-19 17:17:17 +04
result = Monday 2037-10-26 00:00:00 +04
wanted = Monday            00:00:00

America/Curacao
ts     = Thursday 1970-01-01 17:17:17 AST
result = Monday 1970-01-05 00:00:00 AST
wanted = Monday            00:00:00

Africa/Luanda
ts     = Thursday 1970-01-01 17:17:17 WAT
result = Monday 1970-01-05 00:00:00 WAT
wanted = Monday            00:00:00

Antarctica/McMurdo
ts     = Monday 2037-09-28 17:17:17 NZDT
result = Monday 2037-10-05 00:00:00 NZDT
wanted = Monday            00:00:00

Australia/Adelaide
ts     = Friday 1971-01-01 17:17:17 ACST
result = Monday 1971-01-04 00:00:00 ACST
wanted = Monday            00:00:00

Australia/Darwin
ts     = Monday 1971-03-29 17:17:17 ACST
result = Monday 1971-04-05 00:00:00 ACST
wanted = Monday            00:00:00

Australia/Perth
ts     = Friday 1971-01-01 17:17:17 AWST
result = Monday 1971-01-04 00:00:00 AWST
wanted = Monday            00:00:00

America/Aruba
ts     = Monday 1971-03-29 17:17:17 AST
result = Monday 1971-04-05 00:00:00 AST
wanted = Monday            00:00:00

Asia/Baku
ts     = Friday 1971-01-01 17:17:17 +04
result = Monday 1971-01-04 00:00:00 +04
wanted = Monday            00:00:00

Europe/Sarajevo
ts     = Friday 1971-01-01 17:17:17 CET
result = Monday 1971-01-04 00:00:00 CET
wanted = Monday            00:00:00

America/Barbados
ts     = Friday 1971-01-01 17:17:17 AST
result = Monday 1971-01-04 00:00:00 AST
wanted = Monday            00:00:00

Asia/Dacca
ts     = Friday 1971-01-01 17:17:17 %s
result = Monday 1971-01-04 00:00:00 %s
wanted = Monday            00:00:00

Europe/Brussels
ts     = Friday 1971-01-01 17:17:17 CET
result = Monday 1971-01-04 00:00:00 CET
wanted = Monday            00:00:00

Africa/Ouagadougou
ts     = Monday 1971-03-29 17:17:17 GMT
result = Monday 1971-04-05 00:00:00 GMT
wanted = Monday            00:00:00

Europe/Tirane
ts     = Monday 1983-04-11 17:17:17 CET
result = Monday 1983-04-18 01:00:00 CEST
wanted = Monday            00:00:00

America/Buenos_Aires
ts     = Monday 1974-09-30 17:17:17 %s
result = Monday 1974-10-07 00:00:00 %s
wanted = Monday            00:00:00

America/Rosario
ts     = Monday 1974-09-30 17:17:17 %s
result = Monday 1974-10-07 00:00:00 %s
wanted = Monday            00:00:00

Europe/Vienna
ts     = Monday 1980-03-31 17:17:17 CET
result = Monday 1980-04-07 00:00:00 CEST
wanted = Monday            00:00:00

Asia/Baku
ts     = Monday 1995-12-25 17:17:17 +04
result = Monday 1996-01-01 00:00:00 +04
wanted = Monday            00:00:00
Can't peek at an empty heap
