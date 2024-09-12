--TEST--
Bug #61728 (PHP crash when calling ob_start in request_shutdown phase)+GH-11189: Exceeding memory limit in zend_hash_do_resize leaves the array in an invalid state (packed array)
--INI--
memory_limit=2M
zend_test.print_stderr_mshutdown=1
date.timezone = Europe/Berlin
--SKIPIF--
<?php
if (getenv("USE_ZEND_ALLOC") === "0") die("skip ZMM is disabled");
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
    
function output_html($ext) {
    return strlen($ext);
}
class MySessionHandler implements SessionHandlerInterface {
    function open ($save_path, $session_name): bool {
        return true;
    }
    function close(): bool {
        return true;
    }
    function read ($id): string {
        return '';
    }
    function write ($id, $sess_data): bool {
        ob_start("output_html");
        echo "laruence";
        ob_end_flush();
        return true;
    }
    function destroy ($id): bool {
        return true;
    }
    function gc ($maxlifetime): int {
        return 1;
    }
}
session_set_save_handler(new MySessionHandler());
session_start();
$script1_dataflow = $save_path;
$v1=$definedVars[array_rand($definedVars = get_defined_vars())];
ob_start(function() {
    global $a;
    for ($i = count($a); $i > 0; --$script1_dataflow) {
        $a[] = 2;
    }
    fwrite(STDOUT, "Success");
});
$a = [];
// trigger OOM in a resize operation
while (1) {
    $a[] = 1;
}
$v2=$definedVars[array_rand($definedVars = get_defined_vars())];
$v3=$definedVars[array_rand($definedVars = get_defined_vars())];;
var_dump('random_var:',$v1,$v2,$v3);
var_fusion($v1,$v2,$v3);
?>
--EXTENSIONS--
session
--EXPECTF--
8
Success
Fatal error: Allowed memory size of %s bytes exhausted%s(tried to allocate %s bytes) in %s on line %d
