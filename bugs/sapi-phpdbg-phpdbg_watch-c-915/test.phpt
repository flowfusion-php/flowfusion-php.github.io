--TEST--
SPL: RecursiveIteratorIterator and catch getChildren+Bug #73927 (phpdbg fails with windows error prompt at "watch array")
--SKIPIF--
<?php
if (getenv('SKIP_ASAN')) {
    die("skip intentionally causes segfaults");
}
?>
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
    
class MyRecursiveArrayIterator extends RecursiveArrayIterator
{
    function getChildren(): ?RecursiveArrayIterator
    {
        echo __METHOD__ . "\n";
        return $this->current();
    }
    function valid(): bool
    {
        if (!parent::valid())
        {
            echo __METHOD__ . " = false\n";
            return false;
        }
        else
        {
            return true;
        }
    }
}
class RecursiveArrayIteratorIterator extends RecursiveIteratorIterator
{
    private $max_depth;
    private $over = 0;
    private $skip = false;
    function __construct($it, $max_depth)
    {
        $this->max_depth = $max_depth;
        parent::__construct($it, RecursiveIteratorIterator::LEAVES_ONLY, RecursiveIteratorIterator::CATCH_GET_CHILD);
    }
    function rewind(): void
    {
        echo __METHOD__ . "\n";
        $this->skip = false;
        parent::rewind();
    }
    function valid(): bool
    {
        echo __METHOD__ . "\n";
        if ($this->skip)
        {
            $this->skip = false;
            $this->next();
        }
        return parent::valid();
    }
    function current(): mixed
    {
        echo __METHOD__ . "\n";
        return parent::current();
    }
    function key(): int
    {
        echo __METHOD__ . "\n";
        return parent::key();
    }
    function next(): void
    {
        echo __METHOD__ . "\n";
        parent::next();
    }
    function callHasChildren(): bool
    {
        $this->skip = false;
        $has = parent::callHasChildren();
        $res = $this->getDepth() < $this->max_depth && $has;
        echo __METHOD__ . "(".$this->getDepth().") = ".($res?"yes":"no")."/".($has?"yes":"no")."\n";
        if ($has && !$res)
        {
            $this->over++;
            if ($this->over == 2) {
                $this->skip = true;
            }
        }
        return $res;
    }
    function callGetChildren(): MyRecursiveArrayIterator
    {
        if ($this->over == 2)
        {
            echo __METHOD__ . "(throw)\n";
            throw new Exception("Thrown in callGetChildren()");
        }
        echo __METHOD__ . "(ok:{$this->over})\n";
        return new MyRecursiveArrayIterator($this->current());
    }
    function beginChildren(): void
    {
        echo __METHOD__ . "(".$this->getDepth().")\n";
    }
    function endChildren(): void
    {
        echo __METHOD__ . "(".$this->getDepth().")\n";
    }
}
try
{
    foreach(new RecursiveArrayIteratorIterator(new MyRecursiveArrayIterator(array("a", array("ba", array("bba", "bbb"), array(array("bcaa"), array("bcba"))), array("ca"), "d")), 2) as $k=>$v)
    {
        if (is_array($v)) $v = join('',$v);
        echo "$k=>$v\n";
    }
}
catch(UnexpectedValueException $e)
{
    echo $e->getMessage() . "\n";
}
$fusion = $k;
$v1=$definedVars[array_rand($definedVars = get_defined_vars())];
// Generate some mock data
$example = [1, 23, 23423, 256436, 3463, 4363, 457];
foreach (range(1, 1000) as $val) {
    $example[] = mt_rand(1, 10000);
}
// Stuff to debug
function doCoolStuff($value)
{
    $value++;
    return mt_rand(1, 1000);
}
$lower = [];
foreach ($example as $key => $value) {
    if ($value < 100) {
        $fusion[] = $value;
    } else {
        doCoolStuff($value);
    }
}
$v2=$definedVars[array_rand($definedVars = get_defined_vars())];
$v3=$definedVars[array_rand($definedVars = get_defined_vars())];;
var_dump('random_var:',$v1,$v2,$v3);
var_fusion($v1,$v2,$v3);
?>
--PHPDBG--
b 19
r
c
w $value
w $lower[]
q
--EXPECTF--
RecursiveArrayIteratorIterator::rewind
RecursiveArrayIteratorIterator::callHasChildren(0) = no/no
RecursiveArrayIteratorIterator::valid
RecursiveArrayIteratorIterator::current
RecursiveArrayIteratorIterator::key
0=>a
RecursiveArrayIteratorIterator::next
RecursiveArrayIteratorIterator::callHasChildren(0) = yes/yes
RecursiveArrayIteratorIterator::callGetChildren(ok:0)
RecursiveArrayIteratorIterator::current
RecursiveArrayIteratorIterator::beginChildren(1)
RecursiveArrayIteratorIterator::callHasChildren(1) = no/no
RecursiveArrayIteratorIterator::valid
RecursiveArrayIteratorIterator::current
RecursiveArrayIteratorIterator::key
0=>ba
RecursiveArrayIteratorIterator::next
RecursiveArrayIteratorIterator::callHasChildren(1) = yes/yes
RecursiveArrayIteratorIterator::callGetChildren(ok:0)
RecursiveArrayIteratorIterator::current
RecursiveArrayIteratorIterator::beginChildren(2)
RecursiveArrayIteratorIterator::callHasChildren(2) = no/no
RecursiveArrayIteratorIterator::valid
RecursiveArrayIteratorIterator::current
RecursiveArrayIteratorIterator::key
0=>bba
RecursiveArrayIteratorIterator::next
RecursiveArrayIteratorIterator::callHasChildren(2) = no/no
RecursiveArrayIteratorIterator::valid
RecursiveArrayIteratorIterator::current
RecursiveArrayIteratorIterator::key
1=>bbb
RecursiveArrayIteratorIterator::next
MyRecursiveArrayIterator::valid = false
RecursiveArrayIteratorIterator::endChildren(2)
RecursiveArrayIteratorIterator::callHasChildren(1) = yes/yes
RecursiveArrayIteratorIterator::callGetChildren(ok:0)
RecursiveArrayIteratorIterator::current
RecursiveArrayIteratorIterator::beginChildren(2)
RecursiveArrayIteratorIterator::callHasChildren(2) = no/yes
RecursiveArrayIteratorIterator::valid
RecursiveArrayIteratorIterator::current
RecursiveArrayIteratorIterator::key
0=>bcaa
RecursiveArrayIteratorIterator::next
RecursiveArrayIteratorIterator::callHasChildren(2) = no/yes
RecursiveArrayIteratorIterator::valid
RecursiveArrayIteratorIterator::next
MyRecursiveArrayIterator::valid = false
RecursiveArrayIteratorIterator::endChildren(2)
MyRecursiveArrayIterator::valid = false
RecursiveArrayIteratorIterator::endChildren(1)
RecursiveArrayIteratorIterator::callHasChildren(0) = yes/yes
RecursiveArrayIteratorIterator::callGetChildren(throw)
RecursiveArrayIteratorIterator::callHasChildren(0) = no/no
RecursiveArrayIteratorIterator::current
RecursiveArrayIteratorIterator::key
3=>d
RecursiveArrayIteratorIterator::next
MyRecursiveArrayIterator::valid = false
RecursiveArrayIteratorIterator::valid
MyRecursiveArrayIterator::valid = false
[Successful compilation of %s]
prompt> [Breakpoint #0 added at %s:%d]
prompt> [Breakpoint #0 at %s:%d, hits: 1]
>00019:     if ($value < 100) {
 00020:         $lower[] = $value;
 00021:     } else {
prompt> [Breakpoint #0 at %s:%d, hits: 2]
>00019:     if ($value < 100) {
 00020:         $lower[] = $value;
 00021:     } else {
prompt> [Added watchpoint #0 for $value]
prompt> [Added watchpoint #1 for $lower[0]]
prompt> [$lower[0] has been removed, removing watchpoint]
[$value has been removed, removing watchpoint]
