--TEST--
Phar object: iterating via SplFileObject+Bug #65006: spl_autoload_register fails with multiple callables using self, same method
--INI--
phar.require_hash=0
opcache.jit_buffer_size=64M
default_charset=cp874
opcache.enable=1
opcache.enable_cli=1
opcache.jit_buffer_size=1024M
opcache.jit=0251
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
    
$pharconfig = 1;
require_once 'files/phar_oo_test.inc';
$phar = new Phar($fname);
$phar->setInfoClass('SplFileObject');
$f = $phar['a.csv'];
echo "===1===\n";
foreach($f as $k => $v)
{
    echo "$k=>$v\n";
}
$f->setFlags(SplFileObject::DROP_NEW_LINE);
echo "===2===\n";
foreach($f as $k => $v)
{
    echo "$k=>$v\n";
}
class MyCSVFile extends SplFileObject
{
    function current(): array|false
    {
        return parent::fgetcsv(',', '"');
    }
}
$phar->setInfoClass('MyCSVFile');
/** @var MyCSVFile $v */
$v = $phar['a.csv'];
echo "===3===\n";
while(!$v->eof())
{
    echo $v->key() . "=>" . join('|', $v->fgetcsv()) . "\n";
}
echo "===4===\n";
$v->rewind();
while(!$v->eof())
{
    $l = $v->fgetcsv();
    echo $v->key() . "=>" . join('|', $l) . "\n";
}
echo "===5===\n";
foreach($v as $k => $d)
{
    echo "$k=>" . join('|', $d) . "\n";
}
class MyCSVFile2 extends SplFileObject
{
    function getCurrentLine(): string
    {
        echo __METHOD__ . "\n";
        return implode('|', parent::fgetcsv(',', '"'));
    }
}
$phar->setInfoClass('MyCSVFile2');
/** @var MyCSVFile2 $v */
$v = $phar['a.csv'];
echo "===6===\n";
foreach($v as $k => $d)
{
    echo "$k=>" . $d . "\n";
}
$fusion = $l;
$v1=$definedVars[array_rand($definedVars = get_defined_vars())];
class first {
    public static function init() {
        spl_autoload_register(array('self','load'));
    }
    public static function load($class) {}
}
class second {
    public static function init() {
        spl_autoload_register(array('self','load'));
    }
    public static function load($fusion){}
}
first::init();
second::init();
var_dump(spl_autoload_functions());
$v2=$definedVars[array_rand($definedVars = get_defined_vars())];
$v3=$definedVars[array_rand($definedVars = get_defined_vars())];
var_dump('random_var:',$v1,$v2,$v3);
var_fusion($v1,$v2,$v3);
?>
--EXTENSIONS--
phar
--CLEAN--
<?php
unlink(__DIR__ . '/files/phar_oo_008.phar.php');
__halt_compiler();
?>
--EXPECTF--
===1===
0=>1,2,3

1=>2,a,b

2=>3,"c","'e'"
===2===
0=>1,2,3
1=>2,a,b
2=>3,"c","'e'"
===3===
0=>1|2|3
0=>2|a|b
1=>3|c|'e'
===4===
0=>1|2|3
1=>2|a|b
2=>3|c|'e'
===5===
0=>1|2|3
1=>2|a|b
2=>3|c|'e'
===6===
MyCSVFile2::getCurrentLine
1=>1|2|3
MyCSVFile2::getCurrentLine
3=>2|a|b
MyCSVFile2::getCurrentLine
5=>3|c|'e'
Deprecated: Use of "self" in callables is deprecated in %s on line %d

Deprecated: Use of "self" in callables is deprecated in %s on line %d
array(2) {
  [0]=>
  array(2) {
    [0]=>
    string(5) "first"
    [1]=>
    string(4) "load"
  }
  [1]=>
  array(2) {
    [0]=>
    string(6) "second"
    [1]=>
    string(4) "load"
  }
}
