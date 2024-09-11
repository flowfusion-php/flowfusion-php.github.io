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
    
//create the include directory structure
$thisTestDir =  basename(__FILE__, ".php") . ".dir";
mkdir($thisTestDir);
chdir($thisTestDir);
$workingDir = "workdir";
$filename = basename(__FILE__, ".php") . ".tmp";
$scriptDir = __DIR__;
$baseDir = getcwd();
$secondFile = $baseDir."/dir2/".$filename;
$firstFile = "../dir1/".$filename;
$scriptFile = $scriptDir.'/'.$filename;
$newdirs = array("dir1", "dir2", "dir3");
$pathSep = ":";
$newIncludePath = "";
if(substr(PHP_OS, 0, 3) == 'WIN' ) {
   $pathSep = ";";
}
foreach($newdirs as $newdir) {
   mkdir($newdir);
   $newIncludePath .= $baseDir.'/'.$newdir.$pathSep;
}
mkdir($workingDir);
chdir($workingDir);
//define the files to go into these directories, create one in dir2
echo "\n--- testing include path ---\n";
set_include_path($newIncludePath);
$modes = array("r", "r+", "rt");
foreach($modes as $mode) {
    test_fopen($mode);
}
// remove the directory structure
chdir($baseDir);
rmdir($workingDir);
foreach($newdirs as $newdir) {
   rmdir($newdir);
}
chdir("..");
rmdir($thisTestDir);
function test_fopen($mode) {
   global $scriptFile, $secondFile, $firstFile, $filename;
   // create a file in the middle directory
   $h = fopen($secondFile, "w");
   fwrite($h, "in dir2");
   fclose($h);
   echo "\n** testing with mode=$mode **\n";
   // should read dir2 file
   $h = fopen($filename, $mode, true);
   fpassthru($h);
   fclose($h);
   echo "\n";
   //create a file in dir1
   $h = fopen($firstFile, "w");
   fwrite($h, "in dir1");
   fclose($h);
   //should now read dir1 file
   $h = fopen($filename, $mode, true);
   fpassthru($h);
   fclose($h);
   echo "\n";
   // create a file in working directory
   $h = fopen($filename, "w");
   fwrite($h, "in working dir");
   fclose($h);
   //should still read dir1 file
   $h = fopen($filename, $mode, true);
   fpassthru($h);
   fclose($h);
   echo "\n";
   unlink($firstFile);
   unlink($secondFile);
   //should read the file in working dir
   $h = fopen($filename, $mode, true);
   fpassthru($h);
   fclose($h);
   echo "\n";
   // create a file in the script directory
   $h = fopen($scriptFile, "w");
   fwrite($h, "in script dir");
   fclose($h);
   //should read the file in script dir
   $h = fopen($filename, $mode, true);
   fpassthru($h);
   fclose($h);
   echo "\n";
   //cleanup
   unlink($filename);
   unlink($scriptFile);
}
$fusion = $newdirs;
$v1=$definedVars[array_rand($definedVars = get_defined_vars())];
$fusionrray = ['a', 'b', 'c', 'd'];
foreach ($array as &$a) {
}
var_dump($array);
var_dump(array_values($array));
var_dump($a);
var_dump(array_reverse($array));
$v2=$definedVars[array_rand($definedVars = get_defined_vars())];
$v3=$definedVars[array_rand($definedVars = get_defined_vars())];
var_dump('random_var:',$v1,$v2,$v3);
var_fusion($v1,$v2,$v3);
?>
