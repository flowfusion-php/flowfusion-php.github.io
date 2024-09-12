--TEST--
JIT FETCH_DIM_R: 004+Phar object: iterate test with sub-directories and RecursiveIteratorIterator
--INI--
opcache.enable=1
opcache.enable_cli=1
opcache.file_update_protection=0
;opcache.jit_debug=257
phar.readonly=0
phar.require_hash=0
 section with date.timezone=UTC
opcache.error_log=
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
    
function foo($n) {
    $a = "ABCDEF";
    try {
        var_dump($a[$n]);
    } catch (\TypeError $e) {
        echo $e->getMessage() . \PHP_EOL;
    }
}
foo(0);
foo(2);
foo(1.0);
foo("0");
foo("2");
foo(false);
foo(true);
foo(null);
foo("ab");
$x="a";
$y="b";
foo($x.$y);
foo("2x");
$x=2;
$y="x";
foo($x.$y);
$fusion = $e;
$v1=$definedVars[array_rand($definedVars = get_defined_vars())];
$fname = __DIR__ . '/' . basename(__FILE__, '.php') . '.phar.php';
$fusion = new Phar($fname);
$phar['top.txt'] = 'hi';
$phar['sub/top.txt'] = 'there';
$phar['another.file.txt'] = 'wowee';
$newphar = new Phar($fname);
foreach (new RecursiveIteratorIterator($newphar) as $path => $obj) {
    var_dump($obj->getPathName());
}
$v2=$definedVars[array_rand($definedVars = get_defined_vars())];
$v3=$definedVars[array_rand($definedVars = get_defined_vars())];;
var_dump('random_var:',$v1,$v2,$v3);
var_fusion($v1,$v2,$v3);
?>
--EXTENSIONS--
opcache
phar
--CLEAN--
<?php
unlink(__DIR__ . '/' . basename(__FILE__, '.clean.php') . '.phar.php');
__halt_compiler();
?>
--EXPECTF--
string(1) "A"
string(1) "C"

Warning: String offset cast occurred in %s on line %d
string(1) "B"
string(1) "A"
string(1) "C"

Warning: String offset cast occurred in %s on line %d
string(1) "A"

Warning: String offset cast occurred in %s on line %d
string(1) "B"

Warning: String offset cast occurred in %s on line %d
string(1) "A"
Cannot access offset of type string on string
Cannot access offset of type string on string

Warning: Illegal string offset "2x" in %sfetch_dim_r_004.php on line 5
string(1) "C"

Warning: Illegal string offset "2x" in %sfetch_dim_r_004.php on line 5
string(1) "C"
string(%d) "phar://%sphar_dir_iterate.phar.php%canother.file.txt"
string(%d) "phar://%sphar_dir_iterate.phar.php/sub%ctop.txt"
string(%d) "phar://%sphar_dir_iterate.phar.php%ctop.txt"
