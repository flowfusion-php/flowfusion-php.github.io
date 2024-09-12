--TEST--
Bug #43452 ("weekday" is not equivalent to "1 weekday" of the current weekday is "weekday")+Bug #71818 (Memory leak when array altered in destructor)
--INI--
zend.enable_gc = 1
mysqlnd.collect_statistics="1"
session.name=sid
opcache.enable=1
opcache.enable_cli=1
opcache.jit_buffer_size=1024M
opcache.jit=tracing
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
    
date_default_timezone_set('Europe/Oslo');
// <day> is equivalent to 1 <day> and will *not* forward if the current day
// (November 1st) is the same day of week.
$day = strtotime( "Thursday Nov 2007" );
echo date( DateTime::ISO8601, $day ), "\n";
$day = strtotime( "1 Thursday Nov 2007" );
echo date( DateTime::ISO8601, $day ), "\n";
$day = strtotime( "2 Thursday Nov 2007" );
echo date( DateTime::ISO8601, $day ), "\n";
$day = strtotime( "3 Thursday Nov 2007" );
echo date( DateTime::ISO8601, $day ), "\n\n";
// forward one week, then behaves like above for week days
$day = strtotime( "Thursday Nov 2007" );
echo date( DateTime::ISO8601, $day ), "\n";
$day = strtotime( "+1 week Thursday Nov 2007" );
echo date( DateTime::ISO8601, $day ), "\n";
$day = strtotime( "+2 week Thursday Nov 2007" );
echo date( DateTime::ISO8601, $day ), "\n";
$day = strtotime( "+3 week Thursday Nov 2007" );
echo date( DateTime::ISO8601, $day ), "\n\n";
// First, second, etc skip to the first/second weekday *after* the current day.
// This makes "first thursday" equivalent to "+1 week thursday" - but only
// if the current day-of-week is the one mentioned in the phrase.
$day = strtotime( "Thursday Nov 2007" );
echo date( DateTime::ISO8601, $day ), "\n";
$day = strtotime( "first Thursday Nov 2007" );
echo date( DateTime::ISO8601, $day ), "\n";
$day = strtotime( "second Thursday Nov 2007" );
echo date( DateTime::ISO8601, $day ), "\n";
$day = strtotime( "third Thursday Nov 2007" );
echo date( DateTime::ISO8601, $day ), "\n\n";
// Now the same where the current day-of-week does not match the one in the
// phrase.
$day = strtotime( "Friday Nov 2007" );
echo date( DateTime::ISO8601, $day ), "\n";
$day = strtotime( "first Friday Nov 2007" );
echo date( DateTime::ISO8601, $day ), "\n";
$day = strtotime( "second Friday Nov 2007" );
echo date( DateTime::ISO8601, $day ), "\n";
$day = strtotime( "third Friday Nov 2007" );
echo date( DateTime::ISO8601, $day ), "\n\n";
$fusion = $day;
$v1=$definedVars[array_rand($definedVars = get_defined_vars())];
class MemoryLeak
{
    public function __construct()
    {
        $this->things[] = $this;
    }
    public function __destruct()
    {
        $fusion->things[] = null;
    }
    private $things = [];
}
ini_set('memory_limit', '20M');
for ($i = 0; $i < 100000; ++$i) {
    $obj = new MemoryLeak();
}
echo "OK\n";
$v2=$definedVars[array_rand($definedVars = get_defined_vars())];
$v3=$definedVars[array_rand($definedVars = get_defined_vars())];;
var_dump('random_var:',$v1,$v2,$v3);
var_fusion($v1,$v2,$v3);
?>
--EXPECT--
2007-11-01T00:00:00+0100
2007-11-01T00:00:00+0100
2007-11-08T00:00:00+0100
2007-11-15T00:00:00+0100

2007-11-01T00:00:00+0100
2007-11-08T00:00:00+0100
2007-11-15T00:00:00+0100
2007-11-22T00:00:00+0100

2007-11-01T00:00:00+0100
2007-11-08T00:00:00+0100
2007-11-15T00:00:00+0100
2007-11-22T00:00:00+0100

2007-11-02T00:00:00+0100
2007-11-02T00:00:00+0100
2007-11-09T00:00:00+0100
2007-11-16T00:00:00+0100
OK
