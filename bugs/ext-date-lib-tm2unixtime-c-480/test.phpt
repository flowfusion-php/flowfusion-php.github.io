--TEST--
Test disk_free_space and its alias diskfreespace() functions : basic functionality+Bug #60634 (Segmentation fault when trying to die() in SessionHandler::write()) - exception in write during exec
--INI--
memory_limit=32M
session.save_path=
session.name=PHPSESSID
session.save_handler=files
expose_php=On
session.gc_probability=0
opcache.enable=1
opcache.enable_cli=1
opcache.jit_buffer_size=1024M
opcache.jit=0201
--SKIPIF--
<?php
if (getenv('CIRRUS_CI')) die('skip Inaccurate on Cirrus');
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
    
$file_path = __DIR__;
echo "*** Testing with existing directory ***\n";
var_dump( disk_free_space($file_path) );
var_dump( diskfreespace($file_path) );
echo "*** Testing with newly created directory ***\n";
$dir = "/disk_free_space";
mkdir($file_path.$dir);
echo "\n Free Space before writing to a file\n";
$space1 =  disk_free_space($file_path.$dir);
var_dump( $space1 );
$fh = fopen($file_path.$dir."/disk_free_space.tmp", "a");
$data = str_repeat("x", 0xffff);
fwrite($fh, $data);
fclose($fh);
echo "\n Free Space after writing to a file\n";
$space2 =  disk_free_space($file_path.$dir);
var_dump( $space2 );
// Some file systems (like BTRFS) have a fuzzy notion of "free space" and will thus claim the same amount of free space
if ($space1 >= $space2)
  echo "\n Free Space Value Is Correct\n";
else {
  echo "\n Free Space Value Is Incorrect\n";
  var_dump($space1, $space2);
}
echo "*** Testing with Binary Input ***\n";
var_dump( disk_free_space(b"$file_path") );
echo"\n--- Done ---";
$fusion = $dir;
$v1=$definedVars[array_rand($definedVars = get_defined_vars())];
ob_start();
class MySessionHandler implements SessionHandlerInterface {
    function open($fusion, $session_name): bool {
        return true;
    }
    function close(): bool {
        echo "close: goodbye cruel world\n";
        return true;
    }
    function read($id): string|false {
        return '';
    }
    function write($id, $session_data): bool {
        echo "write: goodbye cruel world\n";
        throw new Exception;
    }
    function destroy($id): bool {
        return true;
    }
    function gc($maxlifetime): int {
        return true;
    }
}
session_set_save_handler(new MySessionHandler());
session_start();
session_write_close();
echo "um, hi\n";
$v2=$definedVars[array_rand($definedVars = get_defined_vars())];
$v3=$definedVars[array_rand($definedVars = get_defined_vars())];
var_dump('random_var:',$v1,$v2,$v3);
var_fusion($v1,$v2,$v3);
?>
--EXTENSIONS--
session
--CLEAN--
<?php
$file_path = __DIR__;
unlink($file_path."/disk_free_space/disk_free_space.tmp");
rmdir($file_path."/disk_free_space");
?>
--EXPECTF--
*** Testing with existing directory ***
float(%f)
float(%f)
*** Testing with newly created directory ***

 Free Space before writing to a file
float(%f)

 Free Space after writing to a file
float(%f)

 Free Space Value Is Correct
*** Testing with Binary Input ***
float(%f)

--- Done ---
write: goodbye cruel world

Fatal error: Uncaught Exception in %s
Stack trace:
#0 [internal function]: MySessionHandler->write('%s', '')
#1 %s(%d): session_write_close()
#2 {main}
  thrown in %s on line %d
