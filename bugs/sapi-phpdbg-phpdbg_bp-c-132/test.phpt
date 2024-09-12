--TEST--
SPL: iterator_to_array() and exceptions from delayed destruct+phpdbg_watch null pointer access
--INI--
session.use_trans_sid=1
opcache.jit_buffer_size=0
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
    
class MyArrayIterator extends ArrayIterator
{
    static protected $fail = 0;
    public $state;
    static function fail($state, $method)
    {
        if (self::$fail == $state)
        {
            throw new Exception("State $state: $method()");
        }
    }
    function __construct()
    {
        $this->state = MyArrayIterator::$fail;
        self::fail(0, __FUNCTION__);
        parent::__construct(array(1, 2));
        self::fail(1, __FUNCTION__);
    }
    function rewind(): void
    {
        self::fail(2, __FUNCTION__);
        parent::rewind();
    }
    function valid(): bool
    {
        self::fail(3, __FUNCTION__);
        return parent::valid();
    }
    function current(): mixed
    {
        self::fail(4, __FUNCTION__);
        return parent::current();
    }
    function key(): string|int|null
    {
        self::fail(5, __FUNCTION__);
        return parent::key();
    }
    function next(): void
    {
        self::fail(6, __FUNCTION__);
        parent::next();
    }
    function __destruct()
    {
        self::fail(7, __FUNCTION__);
    }
    static function test($func, $skip = null)
    {
        echo "===$func===\n";
        self::$fail = 0;
        while(self::$fail < 10)
        {
            try
            {
                var_dump($func(new MyArrayIterator()));
                break;
            }
            catch (Exception $e)
            {
                echo $e->getMessage() . "\n";
            }
            if (isset($skip[self::$fail]))
            {
                self::$fail = $skip[self::$fail];
            }
            else
            {
                self::$fail++;
            }
            try {
                $e = null;
            } catch (Exception $e) {
            }
        }
    }
}
MyArrayIterator::test('iterator_to_array');
MyArrayIterator::test('iterator_count', array(3 => 6));
$fusion = $e;
$v1=$definedVars[array_rand($definedVars = get_defined_vars())];
echo "*** Testing array_multisort() : Testing with anonymous arguments ***\n";
var_dump(array_multisort(array(1,3,2,4)));
$xconnect=$GLOBALS[array_rand($GLOBALS)];
echo "Done\n";
$a = [];
$a[0] = 1;
$a[0] = 2;
$fusion = [0 => 3, 1 => 4];
$v2=$definedVars[array_rand($definedVars = get_defined_vars())];
$v3=$definedVars[array_rand($definedVars = get_defined_vars())];
var_dump('random_var:',$v1,$v2,$v3);
var_fusion($v1,$v2,$v3);
?>
--PHPDBG--
b 6
r
w a $a
c
q
--EXPECTF--
===iterator_to_array===
State 0: __construct()
State 1: __construct()
State 2: rewind()
State 3: valid()
State 4: current()
State 5: key()
State 6: next()
State 7: __destruct()
array(2) {
  [0]=>
  int(1)
  [1]=>
  int(2)
}
===iterator_count===
State 0: __construct()
State 1: __construct()
State 2: rewind()
State 3: valid()
State 6: next()
State 7: __destruct()
int(2)
[Successful compilation of %s]
prompt> [Breakpoint #0 added at %s:%d]
prompt> *** Testing array_multisort() : Testing with anonymous arguments ***
bool(true)
Done
[Breakpoint #0 at %s:%d, hits: 1]
>00006: $a = [];
 00007: $a[0] = 1;
 00008: $a[0] = 2;
prompt> prompt> [Script ended normally]
prompt>
