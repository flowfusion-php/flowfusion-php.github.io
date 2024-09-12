--TEST--
SPL: Test on RecursiveIteratorIterator key function checking switch statements+JIT FETCH_DIM_RW: 004
--INI--
opcache.enable=1
opcache.enable_cli=1
opcache.file_update_protection=0
opcache.file_cache_only=1
exif.decode_unicode_motorola=UCS-2BE
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
    
$ar = array("one"=>1, "two"=>2, "three"=>array("four"=>4, "five"=>5, "six"=>array("seven"=>7)), "eight"=>8, -100 => 10, NULL => "null");
  $it = new RecursiveArrayIterator($ar);
  $it = new RecursiveIteratorIterator($it);
  foreach($it as $k=>$v)
  {
    echo "$k=>$v\n";
    var_dump($k);
  }
$script1_dataflow = $ar;
$v1=$definedVars[array_rand($definedVars = get_defined_vars())];
set_error_handler(function(y$y) {
});
$k=[];
$y[$script1_dataflow]++;
$v2=$definedVars[array_rand($definedVars = get_defined_vars())];
$v3=$definedVars[array_rand($definedVars = get_defined_vars())];;
var_dump('random_var:',$v1,$v2,$v3);
var_fusion($v1,$v2,$v3);
?>
--CREDITS--
Rohan Abraham (rohanabrahams@gmail.com)
TestFest London May 2009
--EXPECTF--
one=>1
string(3) "one"
two=>2
string(3) "two"
four=>4
string(4) "four"
five=>5
string(4) "five"
seven=>7
string(5) "seven"
eight=>8
string(5) "eight"
-100=>10
int(-100)
=>null
string(0) ""
Fatal error: Uncaught TypeError: {closure:%s:%d}(): Argument #1 ($y) must be of type y, int given, called in %s on line %d and defined in %s:%d
Stack trace:
#0 %s(%d): {closure:%s:%d}(2, 'Undefined varia...', '%s', 5)
#1 {main}

Next TypeError: Cannot access offset of type array on array in %sfetch_dim_rw_004.php:5
Stack trace:
#0 {main}
  thrown in %sfetch_dim_rw_004.php on line 5
