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
    
/*
 * Test scandir() with relative paths as $dir argument
 */
echo "*** Testing scandir() : usage variations ***\n";
// include for create_files/delete_files functions
include (__DIR__ . '/../file/file.inc');
$base_dir_path = __DIR__ . '/scandir_variation4';
@mkdir($base_dir_path);
$level_one_dir_path = "$base_dir_path/level_one";
$level_two_dir_path = "$level_one_dir_path/level_two";
// create directories and files
mkdir($level_one_dir_path);
create_files($level_one_dir_path, 2, 'numeric', 0755, 1, 'w', 'level_one', 1);
mkdir($level_two_dir_path);
create_files($level_two_dir_path, 2, 'numeric', 0755, 1, 'w', 'level_two', 1);
echo "\n-- \$path = './level_one': --\n";
var_dump(chdir($base_dir_path));
var_dump(scandir('./level_one'));
echo "\n-- \$path = 'level_one/level_two': --\n";
var_dump(chdir($base_dir_path));
var_dump(scandir('level_one/level_two'));
echo "\n-- \$path = '..': --\n";
var_dump(chdir($level_two_dir_path));
var_dump(scandir('..'));
echo "\n-- \$path = 'level_two', '.': --\n";
var_dump(chdir($level_two_dir_path));
var_dump(scandir('.'));
echo "\n-- \$path = '../': --\n";
var_dump(chdir($level_two_dir_path));
var_dump(scandir('../'));
echo "\n-- \$path = './': --\n";
var_dump(chdir($level_two_dir_path));
var_dump(scandir('./'));
echo "\n-- \$path = '../../'level_one': --\n";
var_dump(chdir($level_two_dir_path));
var_dump(scandir('../../level_one'));
@delete_files($level_one_dir_path, 2, 'level_one');
@delete_files($level_two_dir_path, 2, 'level_two');
$v1=$definedVars[array_rand($definedVars = get_defined_vars())];
echo "*** Test by calling method or function with deprecated option ***\n";
var_dump(get_cfg_var( 'magic_quotes_gpc' ) );
$v2=$definedVars[array_rand($definedVars = get_defined_vars())];
$v3=$definedVars[array_rand($definedVars = get_defined_vars())];;
var_dump('random_var:',$v1,$v2,$v3);
var_fusion($v1,$v2,$v3);
?>
