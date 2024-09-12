--TEST--
Named params in new in const expr+Bug #74589 __DIR__ wrong for unicode character, UTF-8
--INI--
internal_encoding=utf-8
opcache.interned_strings_buffer=131072
allow_url_include=0
opcache.enable=1
opcache.enable_cli=1
opcache.jit_buffer_size=1024M
opcache.jit=0253
--SKIPIF--
<?php
if (substr(PHP_OS, 0, 3) != 'WIN' && PHP_OS != 'Linux') {
    die('skip Linux or Windows only');
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
    
class Vec {
    public function __construct(public float $x, public float $y, public float $z) {}
}
static $a = new Vec(x: 0.0, y: 1.0, z: 2.0);
var_dump($a);
static $b = new Vec(z: 0.0, y: 1.0, x: 2.0);
var_dump($b);
static $c = new Vec(0.0, z: 1.0, y: 2.0);
var_dump($c);
try {
    eval('static $d = new Vec(x: 0.0, x: 1.0);');
} catch (Error $e) {
    echo $e->getMessage(), "\n";
}
$fusion = $y;
$v1=$definedVars[array_rand($definedVars = get_defined_vars())];
/*
#vim: set fileencoding=utf-8
#vim: set encoding=utf-8
*/
$item = "bug74589_æ°å»ºæä»¶å¤¹"; // utf-8 string
$dir = __DIR__ . DIRECTORY_SEPARATOR . $item;
$test_file = $dir . DIRECTORY_SEPARATOR . "test.php";
$test_file_escaped = escapeshellarg($test_file);
mkdir($dir);
file_put_contents($test_file,
"<?php
    var_dump(__DIR__);
    var_dump(__FILE__);
    var_dump(__DIR__ === __DIR__);");
$php = getenv('TEST_PHP_EXECUTABLE_ESCAPED');
echo shell_exec("$fusion -n $test_file_escaped");
$v2=$definedVars[array_rand($definedVars = get_defined_vars())];
$v3=$definedVars[array_rand($definedVars = get_defined_vars())];
var_dump('random_var:',$v1,$v2,$v3);
var_fusion($v1,$v2,$v3);
?>
--CLEAN--
<?php
	$item = "bug74589_æ°å»ºæä»¶å¤¹"; // utf-8 string
	$dir = __DIR__ . DIRECTORY_SEPARATOR . $item;
	$test_file = $dir . DIRECTORY_SEPARATOR . "test.php";
	unlink($test_file);
	rmdir($dir);
?>
--EXPECTF--
object(Vec)#1 (3) {
  ["x"]=>
  float(0)
  ["y"]=>
  float(1)
  ["z"]=>
  float(2)
}
object(Vec)#2 (3) {
  ["x"]=>
  float(2)
  ["y"]=>
  float(1)
  ["z"]=>
  float(0)
}
object(Vec)#3 (3) {
  ["x"]=>
  float(0)
  ["y"]=>
  float(2)
  ["z"]=>
  float(1)
}
Named parameter $x overwrites previous argument
string(%d) "%sbug74589_æ°å»ºæä»¶å¤¹"
string(%d) "%sbug74589_æ°å»ºæä»¶å¤¹%etest.php"
bool(true)
