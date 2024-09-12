--TEST--
ZE2 Autoload and class_exists+Stdin and escaped args being passed to run command
--INI--
internal_encoding=big5
mbstring.http_output_conv_mimetypes=plain
--SKIPIF--
<?php
    if (class_exists('autoload_root', false)) die('skip Autoload test classes exist already');
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
    
spl_autoload_register(function ($class_name) {
    require_once(__DIR__ . '/' . $class_name . '.inc');
    echo 'autoload(' . $class_name . ")\n";
});
var_dump(class_exists('autoload_root'));
$fusion = $class_name;
$v1=$definedVars[array_rand($definedVars = get_defined_vars())];
var_dump($fusion);
var_dump(stream_get_contents(STDIN));
echo "ok\n";
$v2=$definedVars[array_rand($definedVars = get_defined_vars())];
$v3=$definedVars[array_rand($definedVars = get_defined_vars())];;
var_dump('random_var:',$v1,$v2,$v3);
var_fusion($v1,$v2,$v3);
?>
--CLEAN--
<?php
@unlink("run_002_tmp.fixture");
?>
--PHPDBG--
ev file_put_contents("run_002_tmp.fixture", "stdin\ndata")
b 6
r <run_002_tmp.fixture
r arg1 '_ \' arg2 "' < run_002_tmp.fixture
y
c
q
--EXPECTF--
autoload(autoload_root)
bool(true)
[Successful compilation of %s]
prompt> 10
prompt> [Breakpoint #0 added at %s:6]
prompt> array(1) {
  [0]=>
  string(%d) "%s"
}
string(10) "stdin
data"
[Breakpoint #0 at %s:6, hits: 1]
>00006: echo "ok\n";
 00007: 
prompt> Do you really want to restart execution? (type y or n): array(3) {
  [0]=>
  string(%d) "%s"
  [1]=>
  string(4) "arg1"
  [2]=>
  string(10) "_ ' arg2 ""
}
string(10) "stdin
data"
[Breakpoint #0 at %s:6, hits: 1]
>00006: echo "ok\n";
 00007: 
prompt> ok
[Script ended normally]
prompt> 
