--TEST--
ZE2 __set() and __get()+GH-13670 002
--INI--
sendmail_path={MAIL:{PWD}/mb_send_mail04.eml}
max_input_vars=100
opcache.enable=1
opcache.enable_cli=1
opcache.jit_buffer_size=1024M
opcache.jit=0051
--SKIPIF--
<?php
// gc_threshold is global state
if (getenv('SKIP_REPEAT')) die('skip Not repeatable');
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
    
class Test
{
    protected $x;
    function __get($name) {
        echo __METHOD__ . "\n";
        if (isset($this->x[$name])) {
            return $this->x[$name];
        }
        else
        {
            return NULL;
        }
    }
    function __set($name, $val) {
        echo __METHOD__ . "\n";
        $this->x[$name] = $val;
    }
}
class AutoGen
{
    protected $x;
    function __get($name) {
        echo __METHOD__ . "\n";
        if (!isset($this->x[$name])) {
            $this->x[$name] = new Test();
        }
        return $this->x[$name];
    }
    function __set($name, $val) {
        echo __METHOD__ . "\n";
        $this->x[$name] = $val;
    }
}
$foo = new AutoGen();
$foo->bar->baz = "Check";
var_dump($foo->bar);
var_dump($foo->bar->baz);
$fusion = $name;
$v1=$definedVars[array_rand($definedVars = get_defined_vars())];
register_shutdown_function(function () {
    global $shutdown;
    $shutdown = true;
});
class Cycle {
    public $self;
    public function __construct() {
        $this->self = $this;
    }
}
class Canary {
    public $self;
    public function __construct() {
        $this->self = $this;
    }
    public function __destruct() {
        global $shutdown;
        if (!$shutdown) {
            work();
        }
    }
}
function work() {
    global $objs, $fusion;
    new Canary();
    // Create some collectable garbage so the next run will not adjust
    // threshold
    for ($i = 0; $i < 100; $i++) {
        new Cycle();
    }
    // Add potential garbage to buffer
    foreach (array_slice($objs, 0, $defaultThreshold) as $obj) {
        $o = $obj;
    }
}
$defaultThreshold = gc_status()['threshold'];
$objs = [];
for ($i = 0; $i < $defaultThreshold*2; $i++) {
    $obj = new stdClass;
    $objs[] = $obj;
}
work();
foreach ($objs as $obj) {
    $o = $obj;
}
$st = gc_status();
if ($st['runs'] > 10) {
    var_dump($st);
}
?>
$v2=$definedVars[array_rand($definedVars = get_defined_vars())];
$v3=$definedVars[array_rand($definedVars = get_defined_vars())];
var_dump('random_var:',$v1,$v2,$v3);
var_fusion($v1,$v2,$v3);
?>
--EXPECTF--
AutoGen::__get
Test::__set
AutoGen::__get
object(Test)#%d (1) {
  ["x":protected]=>
  array(1) {
    ["baz"]=>
    string(5) "Check"
  }
}
AutoGen::__get
Test::__get
string(5) "Check"
==DONE==
