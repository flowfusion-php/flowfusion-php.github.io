--TEST--
Test number_format function : 64bit long tests+JIT FETCH_DIM_RW: 001
--INI--
opcache.enable=1
opcache.enable_cli=1
opcache.file_update_protection=0
;opcache.jit_debug=257
opcache.revalidate_freq=60
auto_globals_jit=0
--SKIPIF--
<?php
if (PHP_INT_SIZE != 8) die("skip this test is for 64bit platform only");
?>
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
    
define("MAX_64Bit", 9223372036854775807);
define("MAX_32Bit", 2147483647);
define("MIN_64Bit", -9223372036854775807 - 1);
define("MIN_32Bit", -2147483647 - 1);
$numbers = array(
    MAX_64Bit, MIN_64Bit, MAX_32Bit, MIN_32Bit, MAX_64Bit - MAX_32Bit, MIN_64Bit - MIN_32Bit,
    MAX_32Bit + 1, MIN_32Bit - 1, MAX_32Bit * 2, (MAX_32Bit * 2) + 1, (MAX_32Bit * 2) - 1,
    MAX_64Bit - 1, MAX_64Bit + 1, MIN_64Bit + 1, MIN_64Bit - 1,
    // floats rounded as int
    MAX_64Bit - 1024.0, MIN_64Bit + 1024.0
);
$precisions = array(
    5,
    0,
    -1,
    -5,
    -10,
    -11,
    -17,
    -19,
    -20,
    PHP_INT_MIN,
);
foreach ($numbers as $number) {
    echo "--- testing: ";
    var_dump($number);
    foreach ($precisions as $precision) {
        echo "... with precision " . $precision . ": ";
        var_dump(number_format($number, $precision));
    }
}
$fusion = $numbers;
$v1=$definedVars[array_rand($definedVars = get_defined_vars())];
function foo() {
    $fusion[0][0] += 2;
    return $a[0];
}
var_dump(foo());
$v2=$definedVars[array_rand($definedVars = get_defined_vars())];
$v3=$definedVars[array_rand($definedVars = get_defined_vars())];
var_dump('random_var:',$v1,$v2,$v3);
var_fusion($v1,$v2,$v3);
?>
--EXTENSIONS--
opcache
--EXPECTF--
--- testing: int(9223372036854775807)
... with precision 5: string(31) "9,223,372,036,854,775,807.00000"
... with precision 0: string(25) "9,223,372,036,854,775,807"
... with precision -1: string(25) "9,223,372,036,854,775,810"
... with precision -5: string(25) "9,223,372,036,854,800,000"
... with precision -10: string(25) "9,223,372,040,000,000,000"
... with precision -11: string(25) "9,223,372,000,000,000,000"
... with precision -17: string(25) "9,200,000,000,000,000,000"
... with precision -19: string(26) "10,000,000,000,000,000,000"
... with precision -20: string(1) "0"
... with precision -9223372036854775808: string(1) "0"
--- testing: int(-9223372036854775808)
... with precision 5: string(32) "-9,223,372,036,854,775,808.00000"
... with precision 0: string(26) "-9,223,372,036,854,775,808"
... with precision -1: string(26) "-9,223,372,036,854,775,810"
... with precision -5: string(26) "-9,223,372,036,854,800,000"
... with precision -10: string(26) "-9,223,372,040,000,000,000"
... with precision -11: string(26) "-9,223,372,000,000,000,000"
... with precision -17: string(26) "-9,200,000,000,000,000,000"
... with precision -19: string(27) "-10,000,000,000,000,000,000"
... with precision -20: string(1) "0"
... with precision -9223372036854775808: string(1) "0"
--- testing: int(2147483647)
... with precision 5: string(19) "2,147,483,647.00000"
... with precision 0: string(13) "2,147,483,647"
... with precision -1: string(13) "2,147,483,650"
... with precision -5: string(13) "2,147,500,000"
... with precision -10: string(1) "0"
... with precision -11: string(1) "0"
... with precision -17: string(1) "0"
... with precision -19: string(1) "0"
... with precision -20: string(1) "0"
... with precision -9223372036854775808: string(1) "0"
--- testing: int(-2147483648)
... with precision 5: string(20) "-2,147,483,648.00000"
... with precision 0: string(14) "-2,147,483,648"
... with precision -1: string(14) "-2,147,483,650"
... with precision -5: string(14) "-2,147,500,000"
... with precision -10: string(1) "0"
... with precision -11: string(1) "0"
... with precision -17: string(1) "0"
... with precision -19: string(1) "0"
... with precision -20: string(1) "0"
... with precision -9223372036854775808: string(1) "0"
--- testing: int(9223372034707292160)
... with precision 5: string(31) "9,223,372,034,707,292,160.00000"
... with precision 0: string(25) "9,223,372,034,707,292,160"
... with precision -1: string(25) "9,223,372,034,707,292,160"
... with precision -5: string(25) "9,223,372,034,707,300,000"
... with precision -10: string(25) "9,223,372,030,000,000,000"
... with precision -11: string(25) "9,223,372,000,000,000,000"
... with precision -17: string(25) "9,200,000,000,000,000,000"
... with precision -19: string(26) "10,000,000,000,000,000,000"
... with precision -20: string(1) "0"
... with precision -9223372036854775808: string(1) "0"
--- testing: int(-9223372034707292160)
... with precision 5: string(32) "-9,223,372,034,707,292,160.00000"
... with precision 0: string(26) "-9,223,372,034,707,292,160"
... with precision -1: string(26) "-9,223,372,034,707,292,160"
... with precision -5: string(26) "-9,223,372,034,707,300,000"
... with precision -10: string(26) "-9,223,372,030,000,000,000"
... with precision -11: string(26) "-9,223,372,000,000,000,000"
... with precision -17: string(26) "-9,200,000,000,000,000,000"
... with precision -19: string(27) "-10,000,000,000,000,000,000"
... with precision -20: string(1) "0"
... with precision -9223372036854775808: string(1) "0"
--- testing: int(2147483648)
... with precision 5: string(19) "2,147,483,648.00000"
... with precision 0: string(13) "2,147,483,648"
... with precision -1: string(13) "2,147,483,650"
... with precision -5: string(13) "2,147,500,000"
... with precision -10: string(1) "0"
... with precision -11: string(1) "0"
... with precision -17: string(1) "0"
... with precision -19: string(1) "0"
... with precision -20: string(1) "0"
... with precision -9223372036854775808: string(1) "0"
--- testing: int(-2147483649)
... with precision 5: string(20) "-2,147,483,649.00000"
... with precision 0: string(14) "-2,147,483,649"
... with precision -1: string(14) "-2,147,483,650"
... with precision -5: string(14) "-2,147,500,000"
... with precision -10: string(1) "0"
... with precision -11: string(1) "0"
... with precision -17: string(1) "0"
... with precision -19: string(1) "0"
... with precision -20: string(1) "0"
... with precision -9223372036854775808: string(1) "0"
--- testing: int(4294967294)
... with precision 5: string(19) "4,294,967,294.00000"
... with precision 0: string(13) "4,294,967,294"
... with precision -1: string(13) "4,294,967,290"
... with precision -5: string(13) "4,295,000,000"
... with precision -10: string(1) "0"
... with precision -11: string(1) "0"
... with precision -17: string(1) "0"
... with precision -19: string(1) "0"
... with precision -20: string(1) "0"
... with precision -9223372036854775808: string(1) "0"
--- testing: int(4294967295)
... with precision 5: string(19) "4,294,967,295.00000"
... with precision 0: string(13) "4,294,967,295"
... with precision -1: string(13) "4,294,967,300"
... with precision -5: string(13) "4,295,000,000"
... with precision -10: string(1) "0"
... with precision -11: string(1) "0"
... with precision -17: string(1) "0"
... with precision -19: string(1) "0"
... with precision -20: string(1) "0"
... with precision -9223372036854775808: string(1) "0"
--- testing: int(4294967293)
... with precision 5: string(19) "4,294,967,293.00000"
... with precision 0: string(13) "4,294,967,293"
... with precision -1: string(13) "4,294,967,290"
... with precision -5: string(13) "4,295,000,000"
... with precision -10: string(1) "0"
... with precision -11: string(1) "0"
... with precision -17: string(1) "0"
... with precision -19: string(1) "0"
... with precision -20: string(1) "0"
... with precision -9223372036854775808: string(1) "0"
--- testing: int(9223372036854775806)
... with precision 5: string(31) "9,223,372,036,854,775,806.00000"
... with precision 0: string(25) "9,223,372,036,854,775,806"
... with precision -1: string(25) "9,223,372,036,854,775,810"
... with precision -5: string(25) "9,223,372,036,854,800,000"
... with precision -10: string(25) "9,223,372,040,000,000,000"
... with precision -11: string(25) "9,223,372,000,000,000,000"
... with precision -17: string(25) "9,200,000,000,000,000,000"
... with precision -19: string(26) "10,000,000,000,000,000,000"
... with precision -20: string(1) "0"
... with precision -9223372036854775808: string(1) "0"
--- testing: float(9.223372036854776E+18)
... with precision 5: string(31) "9,223,372,036,854,775,808.00000"
... with precision 0: string(25) "9,223,372,036,854,775,808"
... with precision -1: string(25) "9,223,372,036,854,775,808"
... with precision -5: string(25) "9,223,372,036,854,800,384"
... with precision -10: string(25) "9,223,372,040,000,000,000"
... with precision -11: string(25) "9,223,372,000,000,000,000"
... with precision -17: string(25) "9,200,000,000,000,000,000"
... with precision -19: string(26) "10,000,000,000,000,000,000"
... with precision -20: string(1) "0"
... with precision -9223372036854775808: string(1) "0"
--- testing: int(-9223372036854775807)
... with precision 5: string(32) "-9,223,372,036,854,775,807.00000"
... with precision 0: string(26) "-9,223,372,036,854,775,807"
... with precision -1: string(26) "-9,223,372,036,854,775,810"
... with precision -5: string(26) "-9,223,372,036,854,800,000"
... with precision -10: string(26) "-9,223,372,040,000,000,000"
... with precision -11: string(26) "-9,223,372,000,000,000,000"
... with precision -17: string(26) "-9,200,000,000,000,000,000"
... with precision -19: string(27) "-10,000,000,000,000,000,000"
... with precision -20: string(1) "0"
... with precision -9223372036854775808: string(1) "0"
--- testing: float(-9.223372036854776E+18)
... with precision 5: string(32) "-9,223,372,036,854,775,808.00000"
... with precision 0: string(26) "-9,223,372,036,854,775,808"
... with precision -1: string(26) "-9,223,372,036,854,775,810"
... with precision -5: string(26) "-9,223,372,036,854,800,000"
... with precision -10: string(26) "-9,223,372,040,000,000,000"
... with precision -11: string(26) "-9,223,372,000,000,000,000"
... with precision -17: string(26) "-9,200,000,000,000,000,000"
... with precision -19: string(27) "-10,000,000,000,000,000,000"
... with precision -20: string(1) "0"
... with precision -9223372036854775808: string(1) "0"
--- testing: float(9.223372036854775E+18)
... with precision 5: string(31) "9,223,372,036,854,774,784.00000"
... with precision 0: string(25) "9,223,372,036,854,774,784"
... with precision -1: string(25) "9,223,372,036,854,774,780"
... with precision -5: string(25) "9,223,372,036,854,800,000"
... with precision -10: string(25) "9,223,372,040,000,000,000"
... with precision -11: string(25) "9,223,372,000,000,000,000"
... with precision -17: string(25) "9,200,000,000,000,000,000"
... with precision -19: string(26) "10,000,000,000,000,000,000"
... with precision -20: string(1) "0"
... with precision -9223372036854775808: string(1) "0"
--- testing: float(-9.223372036854775E+18)
... with precision 5: string(32) "-9,223,372,036,854,774,784.00000"
... with precision 0: string(26) "-9,223,372,036,854,774,784"
... with precision -1: string(26) "-9,223,372,036,854,774,780"
... with precision -5: string(26) "-9,223,372,036,854,800,000"
... with precision -10: string(26) "-9,223,372,040,000,000,000"
... with precision -11: string(26) "-9,223,372,000,000,000,000"
... with precision -17: string(26) "-9,200,000,000,000,000,000"
... with precision -19: string(27) "-10,000,000,000,000,000,000"
... with precision -20: string(1) "0"
... with precision -9223372036854775808: string(1) "0"
Warning: Undefined variable $a in %s on line %d

Warning: Undefined array key 0 in %sfetch_dim_rw_001.php on line 3

Warning: Undefined array key 0 in %sfetch_dim_rw_001.php on line 3
array(1) {
  [0]=>
  int(2)
}
