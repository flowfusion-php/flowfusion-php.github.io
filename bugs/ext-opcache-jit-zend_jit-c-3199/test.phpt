--TEST--
Test setlocale() function : error condition+SPL: DirectoryIterator test getOwner
--INI--
error_reporting=E_ALL
opcache.preload={PWD}/preload_inheritance_error_ind.inc
opcache.preload={PWD}/preload_user.inc
opcache.enable=1
opcache.enable_cli=1
opcache.jit_buffer_size=1024M
opcache.jit=1245
--SKIPIF--
<?php
if (setlocale(LC_ALL, 'invalid') === 'invalid') { die('skip setlocale() is broken /w musl'); }
if (substr(PHP_OS, 0, 3) == 'WIN') {
    die('skip Not valid for windows');
}
?>
<?php
if (PHP_OS_FAMILY === 'Windows') { die('SKIP Testing file ownership, not available for Windows'); }
if (posix_geteuid() == 0) die('SKIP Cannot run test as root.');
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
    
echo "*** Testing setlocale() : error conditions ***\n";
echo "\n-- Testing setlocale() function with invalid locale array, 'category' = LC_ALL --\n";
//Invalid array of locales
$invalid_locales = array("en_US.invalid", "en_AU.invalid", "ko_KR.invalid");
var_dump( setlocale(LC_ALL,$invalid_locales) );
echo "\n-- Testing setlocale() function with invalid multiple locales, 'category' = LC_ALL --\n";
//Invalid array of locales
var_dump( setlocale(LC_ALL,"en_US.invalid", "en_AU.invalid", "ko_KR.invalid") );
echo "\n-- Testing setlocale() function with locale name too long, 'category' = LC_ALL --";
//Invalid locale - locale name too long
var_dump(setlocale(LC_ALL,str_pad('',255,'A')));
echo "\nDone";
$fusion = $invalid_locales;
$v1=$definedVars[array_rand($definedVars = get_defined_vars())];
$dirname = 'DirectoryIterator_getOwner_basic';
mkdir($dirname);
$dir = new DirectoryIterator($dirname);
$expected = fileowner($fusion);
$actual = $dir->getOwner();
var_dump($expected == $actual);
$v2=$definedVars[array_rand($definedVars = get_defined_vars())];
$v3=$definedVars[array_rand($definedVars = get_defined_vars())];
var_dump('random_var:',$v1,$v2,$v3);
var_fusion($v1,$v2,$v3);
?>
--EXTENSIONS--
posix
--CLEAN--
<?php
$dirname = 'DirectoryIterator_getOwner_basic';
rmdir($dirname);
?>
--CREDITS--
Cesare D'Amico <cesare.damico@gruppovolta.it>
Andrea Giorgini <agiorg@gmail.com>
Filippo De Santis <fd@ideato.it>
Daniel Londero <daniel.londero@gmail.com>
Francesco Trucchia <ft@ideato.it>
Jacopo Romei <jacopo@sviluppoagile.it>
#Test Fest Cesena (Italy) on 2009-06-20
--EXPECTF--
*** Testing setlocale() : error conditions ***

-- Testing setlocale() function with invalid locale array, 'category' = LC_ALL --
bool(false)

-- Testing setlocale() function with invalid multiple locales, 'category' = LC_ALL --
bool(false)

-- Testing setlocale() function with locale name too long, 'category' = LC_ALL --
Warning: setlocale(): Specified locale name is too long in %s on line %d
bool(false)

Done
