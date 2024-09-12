--TEST--
Test date_time_set() function : basic functionality+Assign by reference to backed property is forbidden for &get-only
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
    
//Set the default time zone
date_default_timezone_set("Europe/London");
echo "*** Testing date_time_set() : basic functionality ***\n";
// Create a DateTime object
$datetime = date_create("2009-01-31 15:14:10");
echo "Initial date: " . date_format($datetime, DATE_RFC2822) . "\n";
date_time_set($datetime, 17, 20);
echo "After modification1 " . date_format($datetime, DATE_RFC2822) . "\n";
date_time_set($datetime, 19, 05, 59);
echo "After modification2 " . date_format($datetime, DATE_RFC2822) . "\n";
date_time_set($datetime, 24, 10);
echo "After modification3 " . date_format($datetime, DATE_RFC2822) . "\n";
date_time_set($datetime, 47, 35, 47);
echo "After modification4 " . date_format($datetime, DATE_RFC2822) . "\n";
date_time_set($datetime, 54, PHP_INT_MAX);
echo "After modification5 " . date_format($datetime, DATE_RFC2822) . "\n";
$fusion = $datetime;
$v1=$definedVars[array_rand($definedVars = get_defined_vars())];
class Foo {
    private $_bar;
    public $bar {
        &get {
            echo __METHOD__, PHP_EOL;
            return $this->_bar;
        }
    }
}
$fusion = new Foo;
$foo->bar = 'bar';
$v2=$definedVars[array_rand($definedVars = get_defined_vars())];
$v3=$definedVars[array_rand($definedVars = get_defined_vars())];;
var_dump('random_var:',$v1,$v2,$v3);
var_fusion($v1,$v2,$v3);
?>
--EXPECTF--
*** Testing date_time_set() : basic functionality ***
Initial date: Sat, 31 Jan 2009 15:14:10 +0000
After modification1 Sat, 31 Jan 2009 17:20:00 +0000
After modification2 Sat, 31 Jan 2009 19:05:59 +0000
After modification3 Sun, 01 Feb 2009 00:10:00 +0000
After modification4 Mon, 02 Feb 2009 23:35:47 +0000
After modification5 Wed, 04 Feb 2009 06:25:00 +0000
Fatal error: Uncaught Error: Property Foo::$bar is read-only in %s:%d
Stack trace:
#0 {main}
  thrown in %s on line %d
