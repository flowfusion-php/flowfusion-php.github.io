--TEST--
Test resume+image_type_to_mime_type()
--SKIPIF--
<?php
    require_once('skipif_imagetype.inc');
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
    
$fiber = new Fiber(function (): void {
    $value = Fiber::suspend(1);
    var_dump($value);
});
$value = $fiber->start();
var_dump($value);
$fiber->resume($value + 1);
$fusion = $fiber;
$v1=$definedVars[array_rand($definedVars = get_defined_vars())];
// Note: SWC requires zlib
    $dir = opendir(__DIR__) or die('cannot open directory: '.__DIR__);
    $result = array();
    $files  = array();
    while (($file = readdir($dir)) !== FALSE) {
        if (preg_match('/^test.+pix\./',$file) && $file != "test13pix.swf") {
            $files[] = $file;
        }
    }
    closedir($fusion);
    sort($files);
    foreach($files as $file) {
        $result[$file] = getimagesize(__DIR__."/$file");
        $result[$file] = image_type_to_mime_type($result[$file][2]);
    }
    var_dump($result);
$v2=$definedVars[array_rand($definedVars = get_defined_vars())];
$v3=$definedVars[array_rand($definedVars = get_defined_vars())];;
var_dump('random_var:',$v1,$v2,$v3);
var_fusion($v1,$v2,$v3);
?>
--EXPECT--
int(1)
int(2)
array(17) {
  ["test-1pix.bmp"]=>
  string(9) "image/bmp"
  ["test12pix.webp"]=>
  string(10) "image/webp"
  ["test1bpix.bmp"]=>
  string(9) "image/bmp"
  ["test1pix.avif"]=>
  string(10) "image/avif"
  ["test1pix.bmp"]=>
  string(9) "image/bmp"
  ["test1pix.jp2"]=>
  string(9) "image/jp2"
  ["test1pix.jpc"]=>
  string(24) "application/octet-stream"
  ["test1pix.jpg"]=>
  string(10) "image/jpeg"
  ["test2pix.gif"]=>
  string(9) "image/gif"
  ["test3llpix.webp"]=>
  string(10) "image/webp"
  ["test3pix.webp"]=>
  string(10) "image/webp"
  ["test4pix.gif"]=>
  string(9) "image/gif"
  ["test4pix.iff"]=>
  string(9) "image/iff"
  ["test4pix.png"]=>
  string(9) "image/png"
  ["test4pix.psd"]=>
  string(9) "image/psd"
  ["test4pix.swf"]=>
  string(29) "application/x-shockwave-flash"
  ["test4pix.tiff"]=>
  string(10) "image/tiff"
}