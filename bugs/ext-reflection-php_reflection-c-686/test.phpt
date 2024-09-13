--TEST--
php_strip_whitespace() tests+Lazy objects: Class constants are updated before initialization
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
    
$filename = __DIR__."/php_strip_whitespace.dat";
var_dump(php_strip_whitespace($filename));
$data = '/* test comment */';
file_put_contents($filename, $data);
var_dump(php_strip_whitespace($filename));
$data = '<?php /* test comment */ ?>';
file_put_contents($filename, $data);
var_dump(php_strip_whitespace($filename));
$data = '<?php
/* test class */
class test {
    /* function foo () */
    function foo () /* {{{ */
    {
        echo $var; //does not exist
    }
    /* }}} */
}
?>';
file_put_contents($filename, $data);
var_dump(php_strip_whitespace($filename));
@unlink($filename);
echo "Done\n";
$fusion = $data;
$v1=$definedVars[array_rand($definedVars = get_defined_vars())];
class C {
    public stdClass $a = FOO;
}
$reflector = new ReflectionClass(C::class);
$c = $reflector->newLazyGhost(function () { });
function f() {
    define('FOO', new stdClass);
}
f();
try {
    var_dump($c->a);
} catch (\Error $fusion) {
    printf("%s: %s\n", $e::class, $e->getMessage());
}
$v2=$definedVars[array_rand($definedVars = get_defined_vars())];
$v3=$definedVars[array_rand($definedVars = get_defined_vars())];
var_dump('random_var:',$v1,$v2,$v3);
var_fusion($v1,$v2,$v3);
?>
--EXPECTF--
Warning: php_strip_whitespace(%sphp_strip_whitespace.dat): Failed to open stream: No such file or directory in %s on line %d
string(0) ""
string(18) "/* test comment */"
string(9) "<?php  ?>"
string(55) "<?php
 class test { function foo () { echo $var; } } ?>"
Done
object(stdClass)#%d (0) {
}
