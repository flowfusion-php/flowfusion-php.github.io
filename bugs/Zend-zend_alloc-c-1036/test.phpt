--TEST--
Test session_set_save_handler() : manual shutdown function+Testing get_declared_traits()
--INI--
session.save_handler=files
session.name=PHPSESSID
default_charset=cp1253
opcache.preload={PWD}/preload_user.inc
opcache.enable=1
opcache.enable_cli=1
opcache.jit_buffer_size=1024M
opcache.jit=0211
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
echo "*** Testing session_set_save_handler() : manual shutdown function ***\n";
class MySession extends SessionHandler {
    public $num;
    public function __construct($num) {
        $this->num = $num;
        echo "(#$this->num) constructor called\n";
    }
    public function __destruct() {
        echo "(#$this->num) destructor called\n";
    }
    public function finish() {
        $id = session_id();
        echo "(#$this->num) finish called $id\n";
        session_write_close();
    }
    public function write($id, $data): bool {
        echo "(#$this->num) writing $id = $data\n";
        return parent::write($id, $data);
    }
    public function close(): bool {
        $id = session_id();
        echo "(#$this->num) closing $id\n";
        return parent::close();
    }
}
$handler = new MySession(1);
session_set_save_handler($handler, false);
register_shutdown_function(array($handler, 'finish'));
session_start();
$_SESSION['foo'] = 'bar';
echo "done\n";
ob_end_flush();
$v1=$definedVars[array_rand($definedVars = get_defined_vars())];
class a { }
interface b { }
trait c { }
abstract class d { }
final class e { }
var_dump(get_declared_traits());
$v2=$definedVars[array_rand($definedVars = get_defined_vars())];
$v3=$definedVars[array_rand($definedVars = get_defined_vars())];
var_dump('random_var:',$v1,$v2,$v3);
var_fusion($v1,$v2,$v3);
?>
--EXTENSIONS--
session
--EXPECTF--
*** Testing session_set_save_handler() : manual shutdown function ***
(#1) constructor called
done
(#1) finish called %s
(#1) writing %s = foo|s:3:"bar";
(#1) closing %s
(#1) destructor called
array(%d) {%A
  [%d]=>
  string(1) "c"
}
