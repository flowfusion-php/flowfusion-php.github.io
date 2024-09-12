--TEST--
GH-7875 (mails are sent even if failure to log throws exception)+Phar: copy-on-write test 4 [cache_list]
--INI--
sendmail_path={MAIL:{PWD}/gh7875.mail.out}
mail.log={PWD}/gh7875.mail.log
default_charset=UTF-8
phar.cache_list={PWD}/copyonwrite4.phar.php
phar.readonly=0
opcache.enable_cli=0
default_charset=cp874
opcache.enable=1
opcache.enable_cli=1
opcache.jit_buffer_size=1024M
opcache.jit=0133
--SKIPIF--
<?php
$filename = __DIR__ . "/gh7875.mail.log";
touch($filename);
chmod($filename, 0444);
clearstatcache();
$is_writable = is_writable($filename);
chmod($filename, 0644);
unlink($filename);
if ($is_writable) die("skip cannot make file read-only");
if (PHP_OS_FAMILY !== "Windows") {
    if (!extension_loaded('posix')) die('skip POSIX extension not loaded');
    if (posix_geteuid() == 0) die('skip Cannot run test as root.');
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
    
function exception_error_handler($severity, $message, $file, $line) {
    if (!(error_reporting() & $severity)) {
        return;
    }
    throw new ErrorException($message, 0, $severity, $file, $line);
}
set_error_handler("exception_error_handler");
touch(__DIR__ . "/gh7875.mail.log");
chmod(__DIR__ . "/gh7875.mail.log", 0444);
try {
	mail('recipient@example.com', 'Subject', 'Body', []);
	echo 'Not Reached';
} catch (\Exception $e) {
	echo $e->getMessage(), PHP_EOL;
    var_dump(file_exists(__DIR__ . "/gh7875.mail.out"));
}
$v1=$definedVars[array_rand($definedVars = get_defined_vars())];
$v2=$definedVars[array_rand($definedVars = get_defined_vars())];
$v3=$definedVars[array_rand($definedVars = get_defined_vars())];
var_dump('random_var:',$v1,$v2,$v3);
var_fusion($v1,$v2,$v3);
?>
--EXTENSIONS--
phar
--CLEAN--
<?php
@chmod(__DIR__ . "/gh7875.mail.log", 0644);
@unlink(__DIR__ . "/gh7875.mail.log");
@unlink(__DIR__ . "/gh7875.mail.out");
?>
--EXPECTF--
mail(%s): Failed to open stream: Permission denied
bool(false)
bool(false)
bool(true)
string(2) "hi"
ok
