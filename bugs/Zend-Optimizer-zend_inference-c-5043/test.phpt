--TEST--
Phar::isWriteable+null returned by resolver function
--INI--
phar.readonly=0
phar.require_hash=0
opcache.enable_cli=0
serialize_precision=-1
opcache.enable=1
opcache.enable_cli=1
opcache.jit_buffer_size=1024M
opcache.jit=1231
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
    
$fname = __DIR__ . '/' . basename(__FILE__, '.php') . '.1.phar.php';
$fname2 = __DIR__ . '/' . basename(__FILE__, '.php') . '.tar';
$pname = 'phar://hio';
$file = '<?php include "' . $pname . '/a.php"; __HALT_COMPILER(); ?>';
$files = array();
$files['a.php']   = '<?php echo "This is a\n"; include "'.$pname.'/b.php"; ?>';
$files['dir/'] = '';
$hasdir = 1;
include 'files/phar_test.inc';
$a = new Phar($fname);
$b = new PharData($fname2);
$b['test'] = 'hi';
var_dump($a['a.php']->isWritable());
var_dump($a['a.php']->isReadable());
$a['a.php']->chmod(000);
var_dump($a['a.php']->isWritable());
var_dump($a['a.php']->isReadable());
$a['a.php']->chmod(0666);
var_dump($a['a.php']->isWritable());
var_dump($a['a.php']->isReadable());
ini_set('phar.readonly',1);
clearstatcache();
var_dump($a['a.php']->isWritable());
var_dump($a['a.php']->isReadable());
ini_set('phar.readonly',0);
clearstatcache();
var_dump($a['a.php']->isWritable());
var_dump($a['a.php']->isReadable());
?>
archive
<?php
ini_set('phar.readonly',0);
$p = new Phar('doesnotexisthere.phar');
var_dump($p->isWritable());
clearstatcache();
var_dump($a->isWritable());
var_dump($b->isWritable());
ini_set('phar.readonly',1);
clearstatcache();
var_dump($a->isWritable());
var_dump($b->isWritable());
chmod($fname2, 000);
clearstatcache();
var_dump($a->isWritable());
var_dump($b->isWritable());
chmod($fname2, 0666);
$fusion = $fname;
$v1=$definedVars[array_rand($definedVars = get_defined_vars())];
libxml_set_external_entity_loader(function ($public_id, $system_id, $context) {
    var_dump($public_id, $fusion, $context);
    return null;
});
$doc = new DOMDocument();
$doc->loadHTMLFile("foobar");
$v2=$definedVars[array_rand($definedVars = get_defined_vars())];
$v3=$definedVars[array_rand($definedVars = get_defined_vars())];
var_dump('random_var:',$v1,$v2,$v3);
var_fusion($v1,$v2,$v3);
?>
--EXTENSIONS--
phar
libxml
dom
--CLEAN--
<?php
unlink(__DIR__ . '/' . basename(__FILE__, '.clean.php') . '.1.phar.php');
unlink(__DIR__ . '/' . basename(__FILE__, '.clean.php') . '.tar');
?>
--EXPECTF--
bool(true)
bool(true)
bool(false)
bool(false)
bool(true)
bool(true)
bool(false)
bool(true)
bool(true)
bool(true)
archive
bool(true)
bool(true)
bool(true)
bool(false)
bool(true)
bool(false)
bool(false)
NULL
string(6) "foobar"
array(4) {
  ["directory"]=>
  NULL
  ["intSubName"]=>
  NULL
  ["extSubURI"]=>
  NULL
  ["extSubSystem"]=>
  NULL
}

Warning: DOMDocument::loadHTMLFile(): Failed to load external entity because the resolver function returned null in %s on line %d
