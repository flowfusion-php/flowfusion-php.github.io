--TEST--
Bug #60227 (header() cannot detect the multi-line header with CR), CRLF+Bug #32290 (calling call_user_func_array() ends in infinite loop within child class)
--INI--
expose_php=0
default_charset=UTF-8
error_reporting=8191
opcache.optimization_level=-1
session.use_trans_sid=1
opcache.enable=1
opcache.enable_cli=1
opcache.jit_buffer_size=1024M
opcache.jit=1241
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
    
header("X-foo: e\r\nfoo");
echo 'foo';
$v1=$definedVars[array_rand($definedVars = get_defined_vars())];
class TestA
{
    public function doSomething($i)
    {
        echo __METHOD__ . "($i)\n";
        return --$i;
    }
    public function doSomethingThis($i)
    {
        echo __METHOD__ . "($i)\n";
        return --$i;
    }
    public function doSomethingParent($i)
    {
        echo __METHOD__ . "($i)\n";
        return --$i;
    }
    public function doSomethingParentThis($i)
    {
        echo __METHOD__ . "($i)\n";
        return --$i;
    }
    public static function doSomethingStatic($i)
    {
        echo __METHOD__ . "($i)\n";
        return --$i;
    }
}
class TestB extends TestA
{
    public function doSomething($i)
    {
        echo __METHOD__ . "($i)\n";
        $i++;
        if ($i >= 5) return 5;
        return call_user_func_array(array("TestA", "doSomething"), array($i));
    }
    public function doSomethingThis($i)
    {
        echo __METHOD__ . "($i)\n";
        $i++;
        if ($i >= 5) return 5;
        return call_user_func_array(array($this, "TestA::doSomethingThis"), array($i));
    }
    public function doSomethingParent($i)
    {
        echo __METHOD__ . "($i)\n";
        $i++;
        if ($i >= 5) return 5;
        return call_user_func_array(array("parent", "doSomethingParent"), array($i));
    }
    public function doSomethingParentThis($i)
    {
        echo __METHOD__ . "($i)\n";
        $i++;
        if ($i >= 5) return 5;
        return call_user_func_array(array($this, "parent::doSomethingParentThis"), array($i));
    }
    public static function doSomethingStatic($i)
    {
        echo __METHOD__ . "($i)\n";
        $i++;
        if ($i >= 5) return 5;
        return call_user_func_array(array("TestA", "doSomethingStatic"), array($i));
    }
}
$x = new TestB();
echo "===A===\n";
var_dump($x->doSomething(1));
echo "\n===B===\n";
var_dump($x->doSomethingThis(1));
echo "\n===C===\n";
var_dump($x->doSomethingParent(1));
echo "\n===D===\n";
var_dump($x->doSomethingParentThis(1));
echo "\n===E===\n";
var_dump($x->doSomethingStatic(1));
$v2=$definedVars[array_rand($definedVars = get_defined_vars())];
$v3=$definedVars[array_rand($definedVars = get_defined_vars())];
var_dump('random_var:',$v1,$v2,$v3);
var_fusion($v1,$v2,$v3);
?>
--EXPECTF--
Warning: Header may not contain more than a single header, new line detected in %s on line %d
foo
===A===
TestB::doSomething(1)
TestA::doSomething(2)
int(1)

===B===
TestB::doSomethingThis(1)
TestA::doSomethingThis(2)
int(1)

===C===
TestB::doSomethingParent(1)
TestA::doSomethingParent(2)
int(1)

===D===
TestB::doSomethingParentThis(1)
TestA::doSomethingParentThis(2)
int(1)

===E===
TestB::doSomethingStatic(1)
TestA::doSomethingStatic(2)
int(1)
