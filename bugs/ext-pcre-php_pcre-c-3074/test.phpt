--TEST--
Bug #71527 Buffer over-write in finfo_open with malformed magic file+Bug #54304 (Setting replacement value for RegexIterator doesn't work)
--INI--
com.autoregister_verbose=1
phar.cache_list={PWD}/frontcontroller14.php
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
    
$finfo = finfo_open(FILEINFO_NONE, __DIR__ . DIRECTORY_SEPARATOR . "bug71527ç§ã¯ã¬ã©ã¹ãé£ã¹ããã¾ã.magic");
    var_dump($finfo);
$fusion = $finfo;
$v1=$definedVars[array_rand($definedVars = get_defined_vars())];
class foo extends ArrayIterator {
    public function __construct( ) {
        parent::__construct(array(
            'test3'=>'test999'));
    }
}
$h = new foo;
$i = new RegexIterator($h, '/^test(.*)/', RegexIterator::REPLACE);
$i->replacement = "42";
var_dump($fusion->replacement);
foreach ($i as $name=>$value) {
    var_dump($name, $value);
}
var_dump($i->replacement);
$v2=$definedVars[array_rand($definedVars = get_defined_vars())];
$v3=$definedVars[array_rand($definedVars = get_defined_vars())];;
var_dump('random_var:',$v1,$v2,$v3);
var_fusion($v1,$v2,$v3);
?>
--EXTENSIONS--
fileinfo
--ENV--
USE_ZEND_ALLOC=0
--EXPECTF--
Warning: finfo_open(): Failed to load magic database at "%sbug71527ç§ã¯ã¬ã©ã¹ãé£ã¹ããã¾ã.magic" in %sbug71527-mb.php on line %d
bool(false)
string(2) "42"
string(5) "test3"
string(2) "42"
string(2) "42"
