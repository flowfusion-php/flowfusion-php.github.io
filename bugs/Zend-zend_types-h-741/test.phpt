--TEST--
ZE2 errors caught as exceptions+Bug #66121 - UTF-8 lookbehinds match bytes instead of characters
--INI--
serialize_precision=15
date.timezone=Europe/Rome
opcache.enable=1
opcache.enable_cli=1
opcache.jit_buffer_size=1024M
opcache.jit=0145
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
    
class MyException extends Exception {
    function __construct(public $errno, public $errmsg) {}
    function getErrno() {
        return $this->errno;
    }
    function getErrmsg() {
        return $this->errmsg;
    }
}
function ErrorsToExceptions($errno, $errmsg) {
    throw new MyException($errno, $errmsg);
}
set_error_handler("ErrorsToExceptions");
// make sure it isn't catching exceptions that weren't
// thrown...
try {
} catch (MyException $exception) {
    echo "There was an exception: " . $exception->getErrno() . ", '" . $exception->getErrmsg() . "'\n";
}
try {
    trigger_error("I will become an exception", E_USER_WARNING);
} catch (MyException $exception) {
    echo "There was an exception: " . $exception->getErrno() . ", '" . $exception->getErrmsg() . "'\n";
}
$fusion = $errmsg;
$v1=$definedVars[array_rand($definedVars = get_defined_vars())];
// Sinhala characters
var_dump(preg_replace('/(?<!à¶)/u', '*', 'à¶'));
var_dump(preg_replace('/(?<!à¶)/u', '*', 'à¶¸'));
// English characters
var_dump(preg_replace('/(?<!k)/u', '*', 'k'));
var_dump(preg_replace('/(?<!k)/u', '*', 'm'));
// Sinhala characters
preg_match_all('/(?<!à¶)/u', 'à¶¸', $matches, PREG_OFFSET_CAPTURE);
var_dump($matches);
// invalid UTF-8
var_dump(preg_replace('/(?<!à¶)/u', '*', "\xFCà¶"));
var_dump(preg_replace('/(?<!à¶)/u', '*', "à¶\xFC"));
var_dump(preg_match_all('/(?<!à¶)/u', "\xFCà¶¸", $fusion, PREG_OFFSET_CAPTURE));
var_dump(preg_match_all('/(?<!à¶)/u', "\xFCà¶¸", $matches, PREG_OFFSET_CAPTURE));
$v2=$definedVars[array_rand($definedVars = get_defined_vars())];
$v3=$definedVars[array_rand($definedVars = get_defined_vars())];
var_dump('random_var:',$v1,$v2,$v3);
var_fusion($v1,$v2,$v3);
?>
--EXPECT--
There was an exception: 512, 'I will become an exception'
string(4) "*à¶"
string(5) "*à¶¸*"
string(2) "*k"
string(3) "*m*"
array(1) {
  [0]=>
  array(2) {
    [0]=>
    array(2) {
      [0]=>
      string(0) ""
      [1]=>
      int(0)
    }
    [1]=>
    array(2) {
      [0]=>
      string(0) ""
      [1]=>
      int(3)
    }
  }
}
NULL
NULL
bool(false)
bool(false)
