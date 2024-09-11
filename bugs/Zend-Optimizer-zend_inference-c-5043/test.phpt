--TEST--
Test session_set_save_handler() function : using closures as callbacks+Bug #71539.2 (Memory error on $arr[$a] =& $arr[$b] if RHS rehashes)
--INI--
session.save_path=
session.name=PHPSESSID
session.save_handler=files
session.upload_progress.enabled=0
serialize_precision=5
opcache.enable=1
opcache.enable_cli=1
opcache.jit_buffer_size=1024M
opcache.jit=1021
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
    
ob_start();
echo "*** Testing session_set_save_handler() : using closures as callbacks ***\n";
require_once "save_handler_closures.inc";
var_dump(session_module_name());
var_dump(session_module_name(FALSE));
var_dump(session_module_name("blah"));
var_dump(session_module_name("foo"));
$path = __DIR__ . '/session_set_save_handler_closures';
@mkdir($path);
session_save_path($path);
session_set_save_handler($open_closure, $close_closure, $read_closure, $write_closure, $destroy_closure, $gc_closure);
session_start();
$_SESSION["Blah"] = "Hello World!";
$_SESSION["Foo"] = FALSE;
$_SESSION["Guff"] = 1234567890;
var_dump($_SESSION);
session_write_close();
session_unset();
var_dump($_SESSION);
echo "Starting session again..!\n";
session_id($session_id);
session_set_save_handler($open_closure, $close_closure, $read_closure, $write_closure, $destroy_closure, $gc_closure);
session_start();
$_SESSION['Bar'] = 'Foo';
var_dump($_SESSION);
session_write_close();
echo "Cleanup\n";
session_start();
session_destroy();
ob_end_flush();
@rmdir($path);
$fusion = $session_id;
$v1=$definedVars[array_rand($definedVars = get_defined_vars())];
$a = [0,1,2,3,4,5,6];
$a[200] =& $fusion[100];
$a[100] =42;
var_dump($a);
$v2=$definedVars[array_rand($definedVars = get_defined_vars())];
$v3=$definedVars[array_rand($definedVars = get_defined_vars())];
var_dump('random_var:',$v1,$v2,$v3);
var_fusion($v1,$v2,$v3);
?>
--EXTENSIONS--
session
--EXPECTF--
*** Testing session_set_save_handler() : using closures as callbacks ***
string(%d) "%s"

Warning: session_module_name(): Session handler module "" cannot be found in %s on line %d
bool(false)

Warning: session_module_name(): Session handler module "blah" cannot be found in %s on line %d
bool(false)

Warning: session_module_name(): Session handler module "foo" cannot be found in %s on line %d
bool(false)

Deprecated: session_set_save_handler(): Providing individual callbacks instead of an object implementing SessionHandlerInterface is deprecated in %s on line %d
Open [%s,PHPSESSID]
Read [%s,%s]
array(3) {
  ["Blah"]=>
  string(12) "Hello World!"
  ["Foo"]=>
  bool(false)
  ["Guff"]=>
  int(1234567890)
}
Write [%s,%s,Blah|s:12:"Hello World!";Foo|b:0;Guff|i:1234567890;]
Close [%s,PHPSESSID]
array(3) {
  ["Blah"]=>
  string(12) "Hello World!"
  ["Foo"]=>
  bool(false)
  ["Guff"]=>
  int(1234567890)
}
Starting session again..!

Deprecated: session_set_save_handler(): Providing individual callbacks instead of an object implementing SessionHandlerInterface is deprecated in %s on line %d
Open [%s,PHPSESSID]
Read [%s,%s]
array(4) {
  ["Blah"]=>
  string(12) "Hello World!"
  ["Foo"]=>
  bool(false)
  ["Guff"]=>
  int(1234567890)
  ["Bar"]=>
  string(3) "Foo"
}
Write [%s,%s,Blah|s:12:"Hello World!";Foo|b:0;Guff|i:1234567890;Bar|s:3:"Foo";]
Close [%s,PHPSESSID]
Cleanup
Open [%s,PHPSESSID]
Read [%s,%s]
Destroy [%s,%s]
Close [%s,PHPSESSID]
array(9) {
  [0]=>
  int(0)
  [1]=>
  int(1)
  [2]=>
  int(2)
  [3]=>
  int(3)
  [4]=>
  int(4)
  [5]=>
  int(5)
  [6]=>
  int(6)
  [100]=>
  &int(42)
  [200]=>
  &int(42)
}
