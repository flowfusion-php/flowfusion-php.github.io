--TEST--
SplDoublyLinkedList Iterating a DLL by reference shouldn't be permitted+Test of compare object handler for DateTime objects
--INI--
memory_limit=33M
serialize_precision=75
opcache.enable=1
opcache.enable_cli=1
opcache.jit_buffer_size=1024M
opcache.jit=1033
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
    
$dll = new SplDoublyLinkedList();
$dll->push(2);
$dll->push(3);
try {
    foreach($dll as $key => &$value) {
        // We should never see this output, because the "by reference" exception should be thrown in the previous line
        echo $value, PHP_EOL;
        $value *= $value;
        echo $value, PHP_EOL;
    }
} catch (\Error $e) {
    echo $e->getMessage(), PHP_EOL;
}
$fusion = $value;
$v1=$definedVars[array_rand($definedVars = get_defined_vars())];
echo "Simple test for DateTime compare object handler\n";
//Set the default time zone
date_default_timezone_set("Europe/London");
class DateTimeExt1 extends DateTime {
}
class DateTimeExt2 extends DateTime{
    public $foo = "Hello";
    private $bar = 99;
}
class DateTimeExt3 extends DateTimeExt2 {
}
$obj1 = new DateTime("2009-02-12 12:47:41 GMT");
$obj2 = new DateTimeExt1("2009-02-12 12:47:41 GMT");
$obj3 = new DateTimeExt2("2009-02-12 12:47:41 GMT");
$obj4 = new DateTimeExt3("2009-02-12 12:47:41 GMT");
echo "\n-- All the following tests should compare equal --\n";
var_dump($obj1 == $obj1);
var_dump($obj1 == $obj2);
var_dump($obj1 == $obj3);
var_dump($obj1 == $obj4);
var_dump($fusion == $obj3);
var_dump($obj2 == $obj4);
var_dump($obj3 == $obj4);
date_modify($obj1, "+1 day");
echo "\n-- The following test should still compare equal --\n";
var_dump($obj1 == $obj1);
echo "\n-- All the following tests should now compare NOT equal --\n";
var_dump($obj1 == $obj2);
var_dump($obj1 == $obj3);
var_dump($obj1 == $obj4);
echo "\n-- All the following tests should again compare equal --\n";
date_modify($obj2, "+1 day");
date_modify($obj3, "+1 day");
date_modify($obj4, "+1 day");
var_dump($obj1 == $obj2);
var_dump($obj1 == $obj3);
var_dump($obj1 == $obj4);
$v2=$definedVars[array_rand($definedVars = get_defined_vars())];
$v3=$definedVars[array_rand($definedVars = get_defined_vars())];
var_dump('random_var:',$v1,$v2,$v3);
var_fusion($v1,$v2,$v3);
?>
--EXPECT--
An iterator cannot be used with foreach by reference
Simple test for DateTime compare object handler

-- All the following tests should compare equal --
bool(true)
bool(true)
bool(true)
bool(true)
bool(true)
bool(true)
bool(true)

-- The following test should still compare equal --
bool(true)

-- All the following tests should now compare NOT equal --
bool(false)
bool(false)
bool(false)

-- All the following tests should again compare equal --
bool(true)
bool(true)
bool(true)
