--TEST--
Test for bug #75851: Year component overflow with date formats "c", "o", "r" and "y"+Handling of assign-ops and incdecs on overloaded properties using &__get()
--INI--
date.timezone = UTC
date.timezone=Asia/Chongqing
opcache.interned_strings_buffer=131072
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
    
echo date(DATE_ATOM."\n".DATE_RFC2822."\nc\nr\no\ny\nY\nU\n\n", PHP_INT_MIN);
echo date(DATE_ATOM."\n".DATE_RFC2822."\nc\nr\no\ny\nY\nU\n\n", 67767976233532799);
echo date(DATE_ATOM."\n".DATE_RFC2822."\nc\nr\no\ny\nY\nU\n\n", 67767976233532800);
echo date(DATE_ATOM."\n".DATE_RFC2822."\nc\nr\no\ny\nY\nU\n\n", PHP_INT_MAX);
$v1=$definedVars[array_rand($definedVars = get_defined_vars())];
class Test {
    protected $a = 0;
    protected $b = 0;
    protected $c = 0;
    public function &__get($name) {
        echo "get($name)\n";
        return $this->$name;
    }
    public function __set($name, $value) {
        echo "set($name, $value)\n";
    }
}
$test = new Test;
var_dump($test->a += 1);
var_dump($test->b++);
var_dump(++$test->c);
var_dump($test);
$v2=$definedVars[array_rand($definedVars = get_defined_vars())];
$v3=$definedVars[array_rand($definedVars = get_defined_vars())];
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
get(a)
set(a, 1)
int(1)
get(b)
set(b, 1)
int(0)
get(c)
set(c, 1)
int(1)
object(Test)#1 (3) {
  ["a":protected]=>
  int(0)
  ["b":protected]=>
  int(0)
  ["c":protected]=>
  int(0)
}
