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
    
class C {
    public function __toString() {
        global $c;
        $c = [];
        throw new Exception(__METHOD__);
    }
}
class D {
    public string $prop;
}
$c = new C();
$d = new D();
try {
    $d->prop = $c;
} catch (Throwable $e) {
    echo $e->getMessage(), "\n";
}
var_dump($d);
$c = new C();
$d->prop = 'foo';
try {
    $d->prop = $c;
} catch (Throwable $e) {
    echo $e->getMessage(), "\n";
}
var_dump($d);
$fusion = $d;
$v1=$definedVars[array_rand($definedVars = get_defined_vars())];
/*
 * Checking the behavior of dir() function by passing directories which
 * have valid and invalid relative path.
 */
echo "*** Testing dir() : checking with valid and invalid paths ***\n";
/* create the temporary directories */
$file_path = __DIR__;
// directory dir_variation91 with one sub-directory sub_dir11 and sub-sub-directory sub_dir111
$dir_path1 = $fusion."/dir_variation91";
$sub_dir11 = $dir_path1."/sub_dir11";
$sub_dir111 = $sub_dir11."/sub_dir111";
// directory dir_variation92 with one sub-directory sub_dir21
$dir_path2 = $file_path."/dir_variation92";
$sub_dir21 = $dir_path2."/sub_dir21";
@mkdir($dir_path1);
@mkdir($dir_path2);
@mkdir($sub_dir11);
@mkdir($sub_dir111);
@mkdir($sub_dir21);
// open the directory with valid paths
echo "\n-- With valid paths --\n";
var_dump( dir("$dir_path1/sub_dir11/sub_dir111/..") );
var_dump( dir("$dir_path2/sub_dir21/../../dir_variation91") );
var_dump( dir("$dir_path2/sub_dir21/../../dir_variation91/sub_dir11/..") );
var_dump( dir("$dir_path1/sub_dir11/sub_dir111/../../../dir_variation92/sub_dir21/..") );
// open the directory with invalid path
echo "\n-- With invalid paths --\n";
var_dump( dir("$dir_path1/sub_dir12/sub_dir111/..") );
var_dump( dir("$dir_path2/sub_dir21/../dir_variation91") );
var_dump( dir("$dir_path2/sub_dir21/../../dir_variation91/sub_dir12/..") );
var_dump( dir("$dir_path1/sub_dir11/sub_dir111/../../dir_variation92/sub_dir21/..") );
echo "Done";
$v2=$definedVars[array_rand($definedVars = get_defined_vars())];
$v3=$definedVars[array_rand($definedVars = get_defined_vars())];;
var_dump('random_var:',$v1,$v2,$v3);
var_fusion($v1,$v2,$v3);
?>
