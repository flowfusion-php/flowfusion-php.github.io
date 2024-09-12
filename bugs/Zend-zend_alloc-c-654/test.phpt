--TEST--
Bug #77345 (Segmentation faults stack overflow in cyclic garbage collector) (Bug #77427)+Bug #44394 (Last two bytes missing from output) with session.use_trans_id
--INI--
zend.enable_gc = 1
session.name=PHPSESSID
session.use_only_cookies=0
session.trans_sid_tags="a=href,area=href,frame=src,form="
url_rewriter.tags="a=href,area=href,frame=src,form="
zend_test.register_passes=1
phar.cache_list={PWD}/copyonwrite3.phar.php
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
$script1_dataflow = $nextNode;
$v1=$definedVars[array_rand($definedVars = get_defined_vars())];
ini_set('session.use_trans_sid', 1);
session_save_path(__DIR__);
session_start();
ob_start();
$script1_dataflow = "<a href='a?q=1'>asd</a>";
output_add_rewrite_var('a', 'b');
echo $string;
ob_flush();
ob_end_clean();
$v2=$definedVars[array_rand($definedVars = get_defined_vars())];
$v3=$definedVars[array_rand($definedVars = get_defined_vars())];;
var_dump('random_var:',$v1,$v2,$v3);
var_fusion($v1,$v2,$v3);
?>
--EXTENSIONS--
session
--CLEAN--
<?php
foreach (glob(__DIR__ . '/sess_*') as $filename) {
  unlink($filename);
}
?>
--EXPECTF--
bool(true)
string(5) "start"
string(3) "end"
<a href='a?q=1&a=b&PHPSESSID=%s'>asd</a>
