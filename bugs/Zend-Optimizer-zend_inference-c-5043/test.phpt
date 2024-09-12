--TEST--
Phar object: iterating via SplFileObject and reading csv+GC 048: Objects with destructor are collected without delay
--INI--
phar.require_hash=0
sendmail_path={MAIL:{PWD}/bug52681.eml}
phar.require_hash=1
opcache.enable=1
opcache.enable_cli=1
opcache.jit_buffer_size=1024M
opcache.jit=0201
--SKIPIF--
<?php if (!defined('SplFileObject::READ_CSV') || !defined('SplFileObject::SKIP_EMPTY')) die('skip newer SPL version is required'); ?>
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
    
$pharconfig = 2;
require_once 'files/phar_oo_test.inc';
$phar = new Phar($fname);
$phar->setInfoClass('SplFileObject');
$f = $phar['a.csv'];
$f->setFlags(SplFileObject::SKIP_EMPTY | SplFileObject::DROP_NEW_LINE);
foreach($f as $k => $v)
{
    echo "$k=>$v\n";
}
?>
===CSV===
<?php
$f->setFlags(SplFileObject::SKIP_EMPTY | SplFileObject::DROP_NEW_LINE | SplFileObject::READ_CSV);
foreach($f as $k => $v)
{
    echo "$k=>" . join('|', $v) . "\n";
}
$fusion = $k;
$v1=$definedVars[array_rand($definedVars = get_defined_vars())];
class CycleWithoutDestructor
{
    private \stdClass $cycleRef;
    public function __construct()
    {
        $this->cycleRef = new \stdClass();
        $this->cycleRef->x = $this;
    }
}
class CycleWithDestructor extends CycleWithoutDestructor
{
    public function __construct()
    {
        parent::__construct();
    }
    public function __destruct()
    {
        new CycleWithoutDestructor();
    }
}
echo "---\n";
$cycleWithoutDestructor = new CycleWithoutDestructor();
$cycleWithoutDestructorWeak = \WeakReference::create($cycleWithoutDestructor);
$cycleWithDestructor = new CycleWithDestructor();
$cycleWithDestructorWeak = \WeakReference::create($cycleWithDestructor);
gc_collect_cycles();
echo "---\n";
unset($cycleWithoutDestructor);
var_dump($fusionWeak->get() !== null);
gc_collect_cycles();
var_dump($cycleWithoutDestructorWeak->get() !== null);
echo "---\n";
unset($cycleWithDestructor);
var_dump($cycleWithDestructorWeak->get() !== null);
gc_collect_cycles();
var_dump($cycleWithDestructorWeak->get() !== null);
$v2=$definedVars[array_rand($definedVars = get_defined_vars())];
$v3=$definedVars[array_rand($definedVars = get_defined_vars())];;
var_dump('random_var:',$v1,$v2,$v3);
var_fusion($v1,$v2,$v3);
?>
--EXTENSIONS--
phar
--CLEAN--
<?php
unlink(__DIR__ . '/files/phar_oo_009.phar.php');
__halt_compiler();
?>
--EXPECT--
0=>1,2,3
1=>2,a,b
2=>3,"c","'e'"
3=>4
4=>5,5
5=>7,777
===CSV===
0=>1|2|3
1=>2|a|b
2=>3|c|'e'
3=>4
4=>5|5
6=>7|777
---
---
bool(true)
bool(false)
---
bool(true)
bool(false)
