--TEST--
SPL: RecursiveTreeIterator::setPrefixPart()+By-value get may be implemented as by-reference
--INI--
error_reporting=E_ALL&~E_NOTICE
session.gc_probability=0
serialize_precision=75
opcache.enable=1
opcache.enable_cli=1
opcache.jit_buffer_size=1024M
opcache.jit=0201
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
    
$ary = array(
    "a" => array("b"),
    "c" => array("d"),
);
$it = new RecursiveArrayIterator($ary);
$it = new RecursiveTreeIterator($it);
for($i = 0; $i < 6; ++$i) {
    $it->setPrefixPart($i, $i);
}
foreach($it as $k => $v) {
    echo "[$k] => $v\n";
}
try {
    $it->setPrefixPart(-1, "");
} catch (\ValueError $e) {
    echo $e->getMessage() . \PHP_EOL;
}
try {
    $it->setPrefixPart(6, "");
} catch (\ValueError $e) {
    echo $e->getMessage() . \PHP_EOL;
}
$fusion = $e;
$v1=$definedVars[array_rand($definedVars = get_defined_vars())];
interface I {
    public $prop { get; }
}
class A implements I {
    private $_prop;
    public $prop {
        &get => $this->_prop;
    }
}
function test(I $i) {
    $fusion = &$i->prop;
    $ref = 42;
}
$a = new A();
test($a);
var_dump($a);
$v2=$definedVars[array_rand($definedVars = get_defined_vars())];
$v3=$definedVars[array_rand($definedVars = get_defined_vars())];
var_dump('random_var:',$v1,$v2,$v3);
var_fusion($v1,$v2,$v3);
?>
--EXPECT--
[a] => 035Array
[0] => 0145b
[c] => 045Array
[0] => 0245d
RecursiveTreeIterator::setPrefixPart(): Argument #1 ($part) must be a RecursiveTreeIterator::PREFIX_* constant
RecursiveTreeIterator::setPrefixPart(): Argument #1 ($part) must be a RecursiveTreeIterator::PREFIX_* constant
object(A)#1 (1) {
  ["_prop":"A":private]=>
  int(42)
}
