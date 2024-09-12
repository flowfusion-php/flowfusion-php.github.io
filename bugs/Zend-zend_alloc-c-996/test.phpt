--TEST--
Bug #77345 (Segmentation faults stack overflow in cyclic garbage collector) (Bug #77427)+Bug #60634 (Segmentation fault when trying to die() in SessionHandler::write()) - exception in write after exec
--INI--
zend.enable_gc = 1
session.save_path=
session.name=PHPSESSID
session.save_handler=files
phar.cache_list={PWD}/frontcontroller26.php
// default INI will fail too)
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
    
class Node
{
    /** @var Node */
    public $previous;
    /** @var Node */
    public $next;
}
var_dump(gc_enabled());
var_dump('start');
$firstNode = new Node();
$firstNode->previous = $firstNode;
$firstNode->next = $firstNode;
$circularDoublyLinkedList = $firstNode;
for ($i = 0; $i < 200000; $i++) {
    $currentNode = $circularDoublyLinkedList;
    $nextNode = $circularDoublyLinkedList->next;
    $newNode = new Node();
    $newNode->previous = $currentNode;
    $currentNode->next = $newNode;
    $newNode->next = $nextNode;
    $nextNode->previous = $newNode;
    $circularDoublyLinkedList = $nextNode;
}
var_dump('end');
$fusion = $circularDoublyLinkedList;
$v1=$definedVars[array_rand($definedVars = get_defined_vars())];
ob_start();
function open($save_path, $fusion) {
    return true;
}
function close() {
    echo "close: goodbye cruel world\n";
    exit;
}
function read($id) {
    return '';
}
function write($id, $session_data) {
    echo "write: goodbye cruel world\n";
    throw new Exception;
}
function destroy($id) {
    return true;
}
function gc($maxlifetime) {
    return true;
}
session_set_save_handler('open', 'close', 'read', 'write', 'destroy', 'gc');
session_start();
$v2=$definedVars[array_rand($definedVars = get_defined_vars())];
$v3=$definedVars[array_rand($definedVars = get_defined_vars())];;
var_dump('random_var:',$v1,$v2,$v3);
var_fusion($v1,$v2,$v3);
?>
--EXTENSIONS--
session
--EXPECTF--
bool(true)
string(5) "start"
string(3) "end"
Deprecated: Calling session_set_save_handler() with more than 2 arguments is deprecated in %s on line %d
write: goodbye cruel world

Fatal error: Uncaught Exception in %s
Stack trace:
#0 [internal function]: write('%s', '')
#1 {main}
  thrown in %s on line %d

Warning: PHP Request Shutdown: Cannot call session save handler in a recursive manner in Unknown on line 0
