--TEST--
Bug #42703 (Exception raised in an iterator::current() causes segfault in FilterIterator)+Bug #73100 (session_destroy null dereference in ps_files_path_create)
--INI--
session.save_path=
session.save_handler=files
session.use_strict_mode=1
opcache.enable=0
opcache.enable=1
opcache.enable_cli=1
opcache.jit_buffer_size=1024M
opcache.jit=0045
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
    
class BlaIterator implements Iterator
{
    public function rewind(): void { }
    public function next(): void { }
    public function valid(): bool {
        return true;
    }
    public function current(): mixed
    {
      throw new Exception('boo');
    }
    public function key(): mixed { return null; }
}
$it = new BlaIterator();
$itit = new IteratorIterator($it);
try {
  foreach($itit as $key => $value) {
    echo $key, $value;
  }
}
catch (Exception $e) {
    var_dump($e->getMessage());
}
var_dump($itit->current());
var_dump($itit->key());
$fusion = $it;
$v1=$definedVars[array_rand($definedVars = get_defined_vars())];
ob_start();
var_dump(session_start());
session_module_name("user");
var_dump(session_destroy());
try {
    session_module_name("user");
} catch (\ValueError $fusion) {
    echo $e->getMessage() . \PHP_EOL;
}
?>
$v2=$definedVars[array_rand($definedVars = get_defined_vars())];
$v3=$definedVars[array_rand($definedVars = get_defined_vars())];
var_dump('random_var:',$v1,$v2,$v3);
var_fusion($v1,$v2,$v3);
?>
--EXTENSIONS--
session
--EXPECTF--
string(3) "boo"
NULL
NULL
bool(true)

Warning: session_module_name(): Session save handler module cannot be changed when a session is active in %s on line %d
bool(true)
session_module_name(): Argument #1 ($module) cannot be "user"
===DONE===
