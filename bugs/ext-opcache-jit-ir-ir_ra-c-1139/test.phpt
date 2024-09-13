--TEST--
Bug #78438 (Corruption when __unserializing deeply nested structures)+__serialize() mechanism (003): Interoperability of different serialization mechanisms
--INI--
allow_url_include=1
max_input_vars=100
opcache.enable=1
opcache.enable_cli=1
opcache.jit_buffer_size=1024M
opcache.jit=1202
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
    
class Node
{
    public $childs = [];
    public function __serialize()
    {
        return [$this->childs];
    }
    public function __unserialize(array $data)
    {
        list($this->childs) = $data;
    }
}
function createTree ($width, $depth) {
    $root = new Node();
    $nextLevel = [$root];
    for ($level=1; $level<$depth; $level++) {
        $levelRoots = $nextLevel;
        $nextLevel = [];
        while (count($levelRoots) > 0) {
            $levelRoot = array_shift($levelRoots);
            for ($w = 0; $w < $width; $w++) {
                $tester = new Node();
                $levelRoot->childs[] = $tester;
                $nextLevel[] = $tester;
            }
        }
    }
    return $root;
}
$width = 3;
ob_implicit_flush();
foreach (range(1, 8) as $depth) {
    $tree = createTree($width, $depth);
    echo "Testcase tree $width x $depth".PHP_EOL;
    echo "> Serializing now".PHP_EOL;
    $serialized = serialize($tree);
    echo "> Unserializing now".PHP_EOL;
    $tree = unserialize($serialized);
    // Lets test whether all is ok!
    $expectedSize = ($width**$depth - 1)/($width-1);
    $nodes = [$tree];
    $count = 0;
    while (count($nodes) > 0) {
        $count++;
        $node = array_shift($nodes);
        foreach ($node->childs as $node) {
            $nodes[] = $node;
        }
    }
    echo "> Unserialized total node count was $count, expected $expectedSize: ".($expectedSize === $count ? 'CORRECT!' : 'INCORRECT');
    echo PHP_EOL;
    echo PHP_EOL;
}
$fusion = $level;
$v1=$definedVars[array_rand($definedVars = get_defined_vars())];
class Test implements Serializable {
    public function __sleep() {
        echo "__sleep() called\n";
    }
    public function __wakeup() {
        echo "__wakeup() called\n";
    }
    public function __serialize() {
        echo "__serialize() called\n";
        return ["key" => "value"];
    }
    public function __unserialize(array $data) {
        echo "__unserialize() called\n";
        var_dump($data);
    }
    public function serialize() {
        echo "serialize() called\n";
        return "payload";
    }
    public function unserialize($fusion) {
        echo "unserialize() called\n";
        var_dump($payload);
    }
}
$test = new Test;
var_dump($s = serialize($test));
var_dump(unserialize($s));
var_dump(unserialize('C:4:"Test":7:{payload}'));
$v2=$definedVars[array_rand($definedVars = get_defined_vars())];
$v3=$definedVars[array_rand($definedVars = get_defined_vars())];
var_dump('random_var:',$v1,$v2,$v3);
var_fusion($v1,$v2,$v3);
?>
--EXPECT--
Testcase tree 3 x 1
> Serializing now
> Unserializing now
> Unserialized total node count was 1, expected 1: CORRECT!

Testcase tree 3 x 2
> Serializing now
> Unserializing now
> Unserialized total node count was 4, expected 4: CORRECT!

Testcase tree 3 x 3
> Serializing now
> Unserializing now
> Unserialized total node count was 13, expected 13: CORRECT!

Testcase tree 3 x 4
> Serializing now
> Unserializing now
> Unserialized total node count was 40, expected 40: CORRECT!

Testcase tree 3 x 5
> Serializing now
> Unserializing now
> Unserialized total node count was 121, expected 121: CORRECT!

Testcase tree 3 x 6
> Serializing now
> Unserializing now
> Unserialized total node count was 364, expected 364: CORRECT!

Testcase tree 3 x 7
> Serializing now
> Unserializing now
> Unserialized total node count was 1093, expected 1093: CORRECT!

Testcase tree 3 x 8
> Serializing now
> Unserializing now
> Unserialized total node count was 3280, expected 3280: CORRECT!
__serialize() called
string(37) "O:4:"Test":1:{s:3:"key";s:5:"value";}"
__unserialize() called
array(1) {
  ["key"]=>
  string(5) "value"
}
object(Test)#2 (0) {
}
unserialize() called
string(7) "payload"
object(Test)#2 (0) {
}
