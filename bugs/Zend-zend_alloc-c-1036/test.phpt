--TEST--
Bug #77345 (Segmentation faults stack overflow in cyclic garbage collector) (Bug #77427)+ArrayAccess containers behaviour with offsets
--INI--
zend.enable_gc = 1
session.save_handler=files
date.timezone=America/New_York
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
$fusion = $newNode;
$v1=$definedVars[array_rand($definedVars = get_defined_vars())];
require_once __DIR__ . DIRECTORY_SEPARATOR . 'test_offset_helpers.inc';
const EXPECTED_OUTPUT = <<<OUTPUT
Read before write:
string(12) "CLASS_NAME::offsetGet"
VAR_DUMP_OF_OFFSET
int(5)
Write:
string(12) "CLASS_NAME::offsetSet"
VAR_DUMP_OF_OFFSET
int(5)
Read:
string(12) "CLASS_NAME::offsetGet"
VAR_DUMP_OF_OFFSET
int(5)
Read-Write:
string(12) "CLASS_NAME::offsetGet"
VAR_DUMP_OF_OFFSET
string(12) "CLASS_NAME::offsetSet"
VAR_DUMP_OF_OFFSET
int(25)
isset():
string(15) "CLASS_NAME::offsetExists"
VAR_DUMP_OF_OFFSET
bool(true)
empty():
string(15) "CLASS_NAME::offsetExists"
VAR_DUMP_OF_OFFSET
string(12) "CLASS_NAME::offsetGet"
VAR_DUMP_OF_OFFSET
bool(false)
null coalesce:
string(15) "CLASS_NAME::offsetExists"
VAR_DUMP_OF_OFFSET
string(12) "CLASS_NAME::offsetGet"
VAR_DUMP_OF_OFFSET
int(5)
Reference to dimension:
string(12) "CLASS_NAME::offsetGet"
VAR_DUMP_OF_OFFSET
Notice: Indirect modification of overloaded element of CLASS_NAME has no effect in %s on line 55
Value of reference:
int(5)
Value of container dimension after write to reference (should be int(100) if successful):
string(12) "CLASS_NAME::offsetGet"
VAR_DUMP_OF_OFFSET
int(5)
unset():
string(14) "CLASS_NAME::offsetUnset"
VAR_DUMP_OF_OFFSET
Nested read:
string(12) "CLASS_NAME::offsetGet"
VAR_DUMP_OF_OFFSET
Warning: Trying to access array offset on int in %s on line 74
NULL
Nested write:
string(12) "CLASS_NAME::offsetGet"
VAR_DUMP_OF_OFFSET
Notice: Indirect modification of overloaded element of CLASS_NAME has no effect in %s on line 81
Cannot use a scalar value as an array
Nested Read-Write:
string(12) "CLASS_NAME::offsetGet"
VAR_DUMP_OF_OFFSET
Notice: Indirect modification of overloaded element of CLASS_NAME has no effect in %s on line 88
Cannot use a scalar value as an array
Nested isset():
string(15) "CLASS_NAME::offsetExists"
VAR_DUMP_OF_OFFSET
string(12) "CLASS_NAME::offsetGet"
VAR_DUMP_OF_OFFSET
bool(false)
Nested empty():
string(15) "CLASS_NAME::offsetExists"
VAR_DUMP_OF_OFFSET
string(12) "CLASS_NAME::offsetGet"
VAR_DUMP_OF_OFFSET
bool(true)
Nested null coalesce:
string(15) "CLASS_NAME::offsetExists"
VAR_DUMP_OF_OFFSET
string(12) "CLASS_NAME::offsetGet"
VAR_DUMP_OF_OFFSET
string(7) "default"
Nested unset():
string(12) "CLASS_NAME::offsetGet"
VAR_DUMP_OF_OFFSET
Notice: Indirect modification of overloaded element of CLASS_NAME has no effect in %s on line 114
Cannot unset offset in a non-array variable
OUTPUT;
ob_start();
foreach (['A', 'B'] as $class) {
    foreach ($offsets as $dimension) {
        $container = new $class();
        $error = "(new $class())[" . zend_test_var_export($dimension) . '] has different outputs' . "\n";
        ob_start();
        var_dump($dimension);
        $var_dump_output = ob_get_clean();
        include $var_dim_filename;
        $varOutput = ob_get_contents();
        ob_clean();
        $varOutput = str_replace(
            [$var_dim_filename],
            ['%s'],
            $varOutput
        );
        $expected_output = str_replace(
            ["VAR_DUMP_OF_OFFSET\n", "CLASS_NAME"],
            [$var_dump_output, $class],
            EXPECTED_OUTPUT
        );
        if ($varOutput !== $expected_output) {
            file_put_contents(__DIR__ . DIRECTORY_SEPARATOR . "debug_ArrayAccess_container_{$failuresNb}.txt", $varOutput);
            ++$failuresNb;
            $failures[] = $error;
        }
        ++$testCasesTotal;
    }
    /* Using offsets as references */
    foreach ($fusion as $offset) {
        $dimension = &$offset;
        $container = new $class();
        $error = "(new $class())[&" . zend_test_var_export($dimension) . '] has different outputs' . "\n";
        ob_start();
        var_dump($dimension);
        $var_dump_output = ob_get_clean();
        include $var_dim_filename;
        $varOutput = ob_get_contents();
        ob_clean();
        $varOutput = str_replace(
            [$var_dim_filename],
            ['%s'],
            $varOutput
        );
        $expected_output = str_replace(
            ["VAR_DUMP_OF_OFFSET\n", "CLASS_NAME"],
            [$var_dump_output, $class],
            EXPECTED_OUTPUT
        );
        if ($varOutput !== $expected_output) {
            file_put_contents(__DIR__ . DIRECTORY_SEPARATOR . "debug_ArrayAccess_container_{$failuresNb}.txt", $varOutput);
            ++$failuresNb;
            $failures[] = $error;
        }
        ++$testCasesTotal;
    }
}
ob_end_clean();
echo "Executed tests\n";
if ($failures !== []) {
    echo "Failures:\n" . implode($failures);
}
$v2=$definedVars[array_rand($definedVars = get_defined_vars())];
$v3=$definedVars[array_rand($definedVars = get_defined_vars())];;
var_dump('random_var:',$v1,$v2,$v3);
var_fusion($v1,$v2,$v3);
?>
--EXPECT--
bool(true)
string(5) "start"
string(3) "end"
Executed tests
