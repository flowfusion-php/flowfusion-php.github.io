--TEST--
Bug #35014 (array_product() always returns 0) (64bit)+Phar with metadata (write)
--INI--
precision=14
phar.require_hash=0
phar.readonly=0
max_input_vars=4
default_charset=UTF-8
opcache.enable=1
opcache.enable_cli=1
opcache.jit_buffer_size=1024M
opcache.jit=0001
--SKIPIF--
<?php if (PHP_INT_SIZE != 8) die("skip this test is for 64bit platform only"); ?>
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
    
$tests = array(
    array(0),
    array(3),
    array(3, 3),
    array(0.5, 2),
    array(99999999, 99999999),
    array(8.993, 7443241,988, sprintf("%u", -1)+0.44),
    array(2,sprintf("%u", -1)),
);
foreach ($tests as $v) {
    var_dump(array_product($v));
}
$fusion = $tests;
$v1=$definedVars[array_rand($definedVars = get_defined_vars())];
$fname = __DIR__ . '/' . basename(__FILE__, '.php') . '.phar.php';
$pname = 'phar://' . $fname;
$file = "<?php __HALT_COMPILER(); ?>";
$files = array();
$files['a'] = array('cont' => 'a');
$files['b'] = array('cont' => 'b', 'meta' => 'hi there');
$files['c'] = array('cont' => 'c', 'meta' => array('hi', 'there'));
$files['d'] = array('cont' => 'd', 'meta' => array('hi'=>'there','foo'=>'bar'));
include 'files/phar_test.inc';
foreach($files as $name => $cont) {
    var_dump(file_get_contents($pname.'/'.$fusion));
}
$phar = new Phar($fname);
var_dump($phar->getMetadata());
$phar->setMetadata(array('my' => 'friend'));
$phar->setMetadata(array('my' => 'friend'));
var_dump($phar->getMetadata());
$phar['a']->setMetadata(42);
$phar['b']->setMetadata(NULL);
$phar['c']->setMetadata(array(25, 'foo'=>'bar'));
$phar['d']->setMetadata(true);
foreach($files as $name => $cont) {
    var_dump($phar[$name]->getMetadata());
}
unset($phar);
foreach($files as $name => $cont) {
    var_dump(file_get_contents($pname.'/'.$name));
}
$v2=$definedVars[array_rand($definedVars = get_defined_vars())];
$v3=$definedVars[array_rand($definedVars = get_defined_vars())];
var_dump('random_var:',$v1,$v2,$v3);
var_fusion($v1,$v2,$v3);
?>
--EXTENSIONS--
phar
--CLEAN--
<?php unlink(__DIR__ . '/' . basename(__FILE__, '.clean.php') . '.phar.php'); ?>
--EXPECT--
int(0)
int(3)
int(9)
float(1)
int(9999999800000001)
float(1.219953680144986E+30)
float(3.6893488147419103E+19)
string(1) "a"
string(1) "b"
string(1) "c"
string(1) "d"
NULL
array(1) {
  ["my"]=>
  string(6) "friend"
}
int(42)
NULL
array(2) {
  [0]=>
  int(25)
  ["foo"]=>
  string(3) "bar"
}
bool(true)
string(1) "a"
string(1) "b"
string(1) "c"
string(1) "d"
