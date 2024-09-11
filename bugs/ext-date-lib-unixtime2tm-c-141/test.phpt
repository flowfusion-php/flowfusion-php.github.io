--TEST--
comparing different variables (smaller or equal than)+strtotime() function (64 bit)
--SKIPIF--
<?php if (PHP_INT_SIZE != 8) die("skip this test is for 64bit platform only"); ?>
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
--EXPECTF--
array(3) {  [0]=>  int(1)  [1]=>  int(2)  [2]=>  int(3)} <= array(3) {  [0]=>  int(1)  [1]=>  int(2)  [2]=>  int(3)}
array(3) {  [0]=>  int(1)  [1]=>  int(2)  [2]=>  int(3)} > string(0) ""
array(3) {  [0]=>  int(1)  [1]=>  int(2)  [2]=>  int(3)} > int(1)
array(3) {  [0]=>  int(1)  [1]=>  int(2)  [2]=>  int(3)} > float(2.5)
array(3) {  [0]=>  int(1)  [1]=>  int(2)  [2]=>  int(3)} > int(0)
array(3) {  [0]=>  int(1)  [1]=>  int(2)  [2]=>  int(3)} > string(6) "string"
array(3) {  [0]=>  int(1)  [1]=>  int(2)  [2]=>  int(3)} > string(3) "123"
array(3) {  [0]=>  int(1)  [1]=>  int(2)  [2]=>  int(3)} > string(3) "2.5"
array(3) {  [0]=>  int(1)  [1]=>  int(2)  [2]=>  int(3)} > NULL
array(3) {  [0]=>  int(1)  [1]=>  int(2)  [2]=>  int(3)} <= bool(true)
array(3) {  [0]=>  int(1)  [1]=>  int(2)  [2]=>  int(3)} > bool(false)
array(3) {  [0]=>  int(1)  [1]=>  int(2)  [2]=>  int(3)} <= object(stdClass)#%d (0) {}
array(3) {  [0]=>  int(1)  [1]=>  int(2)  [2]=>  int(3)} <= object(stdClass)#%d (0) {}
array(3) {  [0]=>  int(1)  [1]=>  int(2)  [2]=>  int(3)} <= object(test)#%d (0) {}
array(3) {  [0]=>  int(1)  [1]=>  int(2)  [2]=>  int(3)} > array(0) {}
array(3) {  [0]=>  int(1)  [1]=>  int(2)  [2]=>  int(3)} > int(-9223372036854775808)
array(3) {  [0]=>  int(1)  [1]=>  int(2)  [2]=>  int(3)} > string(20) "-9223372036854775808"
string(0) "" <= array(3) {  [0]=>  int(1)  [1]=>  int(2)  [2]=>  int(3)}
string(0) "" <= string(0) ""
string(0) "" <= int(1)
string(0) "" <= float(2.5)
string(0) "" <= int(0)
string(0) "" <= string(6) "string"
string(0) "" <= string(3) "123"
string(0) "" <= string(3) "2.5"
string(0) "" <= NULL
string(0) "" <= bool(true)
string(0) "" <= bool(false)
string(0) "" <= object(stdClass)#%d (0) {}
string(0) "" <= object(stdClass)#%d (0) {}
string(0) "" <= object(test)#%d (0) {}
string(0) "" <= array(0) {}
string(0) "" <= int(-9223372036854775808)
string(0) "" <= string(20) "-9223372036854775808"
int(1) <= array(3) {  [0]=>  int(1)  [1]=>  int(2)  [2]=>  int(3)}
int(1) > string(0) ""
int(1) <= int(1)
int(1) <= float(2.5)
int(1) > int(0)
int(1) <= string(6) "string"
int(1) <= string(3) "123"
int(1) <= string(3) "2.5"
int(1) > NULL
int(1) <= bool(true)
int(1) > bool(false)
int(1)
Notice: Object of class stdClass could not be converted to int in %s on line %d
 <= object(stdClass)#%d (0) {}
int(1)
Notice: Object of class stdClass could not be converted to int in %s on line %d
 <= object(stdClass)#%d (0) {}
int(1)
Notice: Object of class test could not be converted to int in %s on line %d
 <= object(test)#%d (0) {}
int(1) <= array(0) {}
int(1) > int(-9223372036854775808)
int(1) > string(20) "-9223372036854775808"
float(2.5) <= array(3) {  [0]=>  int(1)  [1]=>  int(2)  [2]=>  int(3)}
float(2.5) > string(0) ""
float(2.5) > int(1)
float(2.5) <= float(2.5)
float(2.5) > int(0)
float(2.5) <= string(6) "string"
float(2.5) <= string(3) "123"
float(2.5) <= string(3) "2.5"
float(2.5) > NULL
float(2.5) <= bool(true)
float(2.5) > bool(false)
float(2.5)
Notice: Object of class stdClass could not be converted to float in %s on line %d
 > object(stdClass)#%d (0) {}
float(2.5)
Notice: Object of class stdClass could not be converted to float in %s on line %d
 > object(stdClass)#%d (0) {}
float(2.5)
Notice: Object of class test could not be converted to float in %s on line %d
 > object(test)#%d (0) {}
float(2.5) <= array(0) {}
float(2.5) > int(-9223372036854775808)
float(2.5) > string(20) "-9223372036854775808"
int(0) <= array(3) {  [0]=>  int(1)  [1]=>  int(2)  [2]=>  int(3)}
int(0) > string(0) ""
int(0) <= int(1)
int(0) <= float(2.5)
int(0) <= int(0)
int(0) <= string(6) "string"
int(0) <= string(3) "123"
int(0) <= string(3) "2.5"
int(0) <= NULL
int(0) <= bool(true)
int(0) <= bool(false)
int(0)
Notice: Object of class stdClass could not be converted to int in %s on line %d
 <= object(stdClass)#%d (0) {}
int(0)
Notice: Object of class stdClass could not be converted to int in %s on line %d
 <= object(stdClass)#%d (0) {}
int(0)
Notice: Object of class test could not be converted to int in %s on line %d
 <= object(test)#%d (0) {}
int(0) <= array(0) {}
int(0) > int(-9223372036854775808)
int(0) > string(20) "-9223372036854775808"
string(6) "string" <= array(3) {  [0]=>  int(1)  [1]=>  int(2)  [2]=>  int(3)}
string(6) "string" > string(0) ""
string(6) "string" > int(1)
string(6) "string" > float(2.5)
string(6) "string" > int(0)
string(6) "string" <= string(6) "string"
string(6) "string" > string(3) "123"
string(6) "string" > string(3) "2.5"
string(6) "string" > NULL
string(6) "string" <= bool(true)
string(6) "string" > bool(false)
string(6) "string" <= object(stdClass)#%d (0) {}
string(6) "string" <= object(stdClass)#%d (0) {}
string(6) "string" <= object(test)#%d (0) {}
string(6) "string" <= array(0) {}
string(6) "string" > int(-9223372036854775808)
string(6) "string" > string(20) "-9223372036854775808"
string(3) "123" <= array(3) {  [0]=>  int(1)  [1]=>  int(2)  [2]=>  int(3)}
string(3) "123" > string(0) ""
string(3) "123" > int(1)
string(3) "123" > float(2.5)
string(3) "123" > int(0)
string(3) "123" <= string(6) "string"
string(3) "123" <= string(3) "123"
string(3) "123" > string(3) "2.5"
string(3) "123" > NULL
string(3) "123" <= bool(true)
string(3) "123" > bool(false)
string(3) "123" <= object(stdClass)#%d (0) {}
string(3) "123" <= object(stdClass)#%d (0) {}
string(3) "123" <= object(test)#%d (0) {}
string(3) "123" <= array(0) {}
string(3) "123" > int(-9223372036854775808)
string(3) "123" > string(20) "-9223372036854775808"
string(3) "2.5" <= array(3) {  [0]=>  int(1)  [1]=>  int(2)  [2]=>  int(3)}
string(3) "2.5" > string(0) ""
string(3) "2.5" > int(1)
string(3) "2.5" <= float(2.5)
string(3) "2.5" > int(0)
string(3) "2.5" <= string(6) "string"
string(3) "2.5" <= string(3) "123"
string(3) "2.5" <= string(3) "2.5"
string(3) "2.5" > NULL
string(3) "2.5" <= bool(true)
string(3) "2.5" > bool(false)
string(3) "2.5" <= object(stdClass)#%d (0) {}
string(3) "2.5" <= object(stdClass)#%d (0) {}
string(3) "2.5" <= object(test)#%d (0) {}
string(3) "2.5" <= array(0) {}
string(3) "2.5" > int(-9223372036854775808)
string(3) "2.5" > string(20) "-9223372036854775808"
NULL <= array(3) {  [0]=>  int(1)  [1]=>  int(2)  [2]=>  int(3)}
NULL <= string(0) ""
NULL <= int(1)
NULL <= float(2.5)
NULL <= int(0)
NULL <= string(6) "string"
NULL <= string(3) "123"
NULL <= string(3) "2.5"
NULL <= NULL
NULL <= bool(true)
NULL <= bool(false)
NULL <= object(stdClass)#%d (0) {}
NULL <= object(stdClass)#%d (0) {}
NULL <= object(test)#%d (0) {}
NULL <= array(0) {}
NULL <= int(-9223372036854775808)
NULL <= string(20) "-9223372036854775808"
bool(true) <= array(3) {  [0]=>  int(1)  [1]=>  int(2)  [2]=>  int(3)}
bool(true) > string(0) ""
bool(true) <= int(1)
bool(true) <= float(2.5)
bool(true) > int(0)
bool(true) <= string(6) "string"
bool(true) <= string(3) "123"
bool(true) <= string(3) "2.5"
bool(true) > NULL
bool(true) <= bool(true)
bool(true) > bool(false)
bool(true) <= object(stdClass)#%d (0) {}
bool(true) <= object(stdClass)#%d (0) {}
bool(true) <= object(test)#%d (0) {}
bool(true) > array(0) {}
bool(true) <= int(-9223372036854775808)
bool(true) <= string(20) "-9223372036854775808"
bool(false) <= array(3) {  [0]=>  int(1)  [1]=>  int(2)  [2]=>  int(3)}
bool(false) <= string(0) ""
bool(false) <= int(1)
bool(false) <= float(2.5)
bool(false) <= int(0)
bool(false) <= string(6) "string"
bool(false) <= string(3) "123"
bool(false) <= string(3) "2.5"
bool(false) <= NULL
bool(false) <= bool(true)
bool(false) <= bool(false)
bool(false) <= object(stdClass)#%d (0) {}
bool(false) <= object(stdClass)#%d (0) {}
bool(false) <= object(test)#%d (0) {}
bool(false) <= array(0) {}
bool(false) <= int(-9223372036854775808)
bool(false) <= string(20) "-9223372036854775808"
object(stdClass)#%d (0) {} > array(3) {  [0]=>  int(1)  [1]=>  int(2)  [2]=>  int(3)}
object(stdClass)#%d (0) {} > string(0) ""
object(stdClass)#%d (0) {}
Notice: Object of class stdClass could not be converted to int in %s on line %d
 <= int(1)
object(stdClass)#%d (0) {}
Notice: Object of class stdClass could not be converted to float in %s on line %d
 <= float(2.5)
object(stdClass)#%d (0) {}
Notice: Object of class stdClass could not be converted to int in %s on line %d
 > int(0)
object(stdClass)#%d (0) {} > string(6) "string"
object(stdClass)#%d (0) {} > string(3) "123"
object(stdClass)#%d (0) {} > string(3) "2.5"
object(stdClass)#%d (0) {} > NULL
object(stdClass)#%d (0) {} <= bool(true)
object(stdClass)#%d (0) {} > bool(false)
object(stdClass)#%d (0) {} <= object(stdClass)#%d (0) {}
object(stdClass)#%d (0) {} <= object(stdClass)#%d (0) {}
object(stdClass)#%d (0) {} > object(test)#%d (0) {}
object(stdClass)#%d (0) {} > array(0) {}
object(stdClass)#%d (0) {}
Notice: Object of class stdClass could not be converted to int in %s on line %d
 > int(-9223372036854775808)
object(stdClass)#%d (0) {} > string(20) "-9223372036854775808"
object(stdClass)#%d (0) {} > array(3) {  [0]=>  int(1)  [1]=>  int(2)  [2]=>  int(3)}
object(stdClass)#%d (0) {} > string(0) ""
object(stdClass)#%d (0) {}
Notice: Object of class stdClass could not be converted to int in %s on line %d
 <= int(1)
object(stdClass)#%d (0) {}
Notice: Object of class stdClass could not be converted to float in %s on line %d
 <= float(2.5)
object(stdClass)#%d (0) {}
Notice: Object of class stdClass could not be converted to int in %s on line %d
 > int(0)
object(stdClass)#%d (0) {} > string(6) "string"
object(stdClass)#%d (0) {} > string(3) "123"
object(stdClass)#%d (0) {} > string(3) "2.5"
object(stdClass)#%d (0) {} > NULL
object(stdClass)#%d (0) {} <= bool(true)
object(stdClass)#%d (0) {} > bool(false)
object(stdClass)#%d (0) {} <= object(stdClass)#%d (0) {}
object(stdClass)#%d (0) {} <= object(stdClass)#%d (0) {}
object(stdClass)#%d (0) {} > object(test)#%d (0) {}
object(stdClass)#%d (0) {} > array(0) {}
object(stdClass)#%d (0) {}
Notice: Object of class stdClass could not be converted to int in %s on line %d
 > int(-9223372036854775808)
object(stdClass)#%d (0) {} > string(20) "-9223372036854775808"
object(test)#%d (0) {} > array(3) {  [0]=>  int(1)  [1]=>  int(2)  [2]=>  int(3)}
object(test)#%d (0) {} > string(0) ""
object(test)#%d (0) {}
Notice: Object of class test could not be converted to int in %s on line %d
 <= int(1)
object(test)#%d (0) {}
Notice: Object of class test could not be converted to float in %s on line %d
 <= float(2.5)
object(test)#%d (0) {}
Notice: Object of class test could not be converted to int in %s on line %d
 > int(0)
object(test)#%d (0) {} > string(6) "string"
object(test)#%d (0) {} > string(3) "123"
object(test)#%d (0) {} > string(3) "2.5"
object(test)#%d (0) {} > NULL
object(test)#%d (0) {} <= bool(true)
object(test)#%d (0) {} > bool(false)
object(test)#%d (0) {} > object(stdClass)#%d (0) {}
object(test)#%d (0) {} > object(stdClass)#%d (0) {}
object(test)#%d (0) {} <= object(test)#%d (0) {}
object(test)#%d (0) {} > array(0) {}
object(test)#%d (0) {}
Notice: Object of class test could not be converted to int in %s on line %d
 > int(-9223372036854775808)
object(test)#%d (0) {} > string(20) "-9223372036854775808"
array(0) {} <= array(3) {  [0]=>  int(1)  [1]=>  int(2)  [2]=>  int(3)}
array(0) {} > string(0) ""
array(0) {} > int(1)
array(0) {} > float(2.5)
array(0) {} > int(0)
array(0) {} > string(6) "string"
array(0) {} > string(3) "123"
array(0) {} > string(3) "2.5"
array(0) {} <= NULL
array(0) {} <= bool(true)
array(0) {} <= bool(false)
array(0) {} <= object(stdClass)#%d (0) {}
array(0) {} <= object(stdClass)#%d (0) {}
array(0) {} <= object(test)#%d (0) {}
array(0) {} <= array(0) {}
array(0) {} > int(-9223372036854775808)
array(0) {} > string(20) "-9223372036854775808"
int(-9223372036854775808) <= array(3) {  [0]=>  int(1)  [1]=>  int(2)  [2]=>  int(3)}
int(-9223372036854775808) > string(0) ""
int(-9223372036854775808) <= int(1)
int(-9223372036854775808) <= float(2.5)
int(-9223372036854775808) <= int(0)
int(-9223372036854775808) <= string(6) "string"
int(-9223372036854775808) <= string(3) "123"
int(-9223372036854775808) <= string(3) "2.5"
int(-9223372036854775808) > NULL
int(-9223372036854775808) <= bool(true)
int(-9223372036854775808) > bool(false)
int(-9223372036854775808)
Notice: Object of class stdClass could not be converted to int in %s on line %d
 <= object(stdClass)#%d (0) {}
int(-9223372036854775808)
Notice: Object of class stdClass could not be converted to int in %s on line %d
 <= object(stdClass)#%d (0) {}
int(-9223372036854775808)
Notice: Object of class test could not be converted to int in %s on line %d
 <= object(test)#%d (0) {}
int(-9223372036854775808) <= array(0) {}
int(-9223372036854775808) <= int(-9223372036854775808)
int(-9223372036854775808) <= string(20) "-9223372036854775808"
string(20) "-9223372036854775808" <= array(3) {  [0]=>  int(1)  [1]=>  int(2)  [2]=>  int(3)}
string(20) "-9223372036854775808" > string(0) ""
string(20) "-9223372036854775808" <= int(1)
string(20) "-9223372036854775808" <= float(2.5)
string(20) "-9223372036854775808" <= int(0)
string(20) "-9223372036854775808" <= string(6) "string"
string(20) "-9223372036854775808" <= string(3) "123"
string(20) "-9223372036854775808" <= string(3) "2.5"
string(20) "-9223372036854775808" > NULL
string(20) "-9223372036854775808" <= bool(true)
string(20) "-9223372036854775808" > bool(false)
string(20) "-9223372036854775808" <= object(stdClass)#%d (0) {}
string(20) "-9223372036854775808" <= object(stdClass)#%d (0) {}
string(20) "-9223372036854775808" <= object(test)#%d (0) {}
string(20) "-9223372036854775808" <= array(0) {}
string(20) "-9223372036854775808" <= int(-9223372036854775808)
string(20) "-9223372036854775808" <= string(20) "-9223372036854775808"
Done
bool(false)
bool(false)
string(31) "Thu, 15 Jun 2006 00:00:00 +0100"
string(31) "Fri, 16 Jun 2006 22:49:12 +0100"
bool(false)
string(31) "Fri, 16 Jun 2006 23:49:12 +0100"
bool(false)
string(31) "Fri, 16 Jun 2006 02:22:00 +0100"
string(31) "Sun, 16 Jun 0222 02:22:00 -0036"
string(31) "Fri, 16 Jun 2006 02:22:33 +0100"
bool(false)
string(31) "Tue, 02 Mar 2004 00:00:00 +0000"
string(31) "Tue, 02 Mar 2004 00:00:00 +0000"
string(31) "Sun, 12 Feb 2006 23:12:23 +0000"
bool(false)
string(31) "Fri, 16 Jun 2006 00:00:00 +0100"
string(31) "Sun, 15 Jan 2006 00:00:00 +0000"
string(31) "Sun, 15 Jan 2006 00:00:00 +0000"
string(31) "Tue, 10 Oct 2000 13:55:36 +0100"
bool(false)
string(31) "Fri, 16 Jun 2006 20:06:00 +0100"
string(31) "Mon, 16 Jun 1986 22:51:59 +0100"
string(31) "Mon, 16 Jan 2006 00:00:00 +0000"
string(31) "Mon, 16 Jan 2006 00:00:00 +0000"
