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
    
/* Testing strtr() function by passing the
 *   combination of numeric & regular strings for 'str' argument and
 *   corresponding translation pair of chars for 'from', 'to' & 'replace_pairs' arguments
*/
echo "*** Testing strtr() : numeric & regular double quoted strings ***\n";
/* definitions of required input variables */
$count = 1;
$heredoc_str = <<<EOD
123
abc
1a2b3c
EOD;
//array of string inputs for $str
$str_arr = array(
  //double quoted strings
  "123",
  "abc",
  "1a2b3c",
  //single quoted strings
  '123',
  'abc',
  '1a2b3c',
  //heredoc string
  $heredoc_str
);
$from = "123abc";
$to = "abc123";
$replace_pairs = array("1" => "a", "a" => 1, "2b3c" => "b2c3", "b2c3" => "3c2b");
/* loop through to test strtr() with each element of $str_arr */
for($index = 0; $index < count($str_arr); $index++) {
  echo "-- Iteration $count --\n";
  $str = $str_arr[$index];  //getting the $str_arr element in $str variable
  //strtr() call in three args syntax form
  var_dump( strtr($str, $from, $to) );
  //strtr() call in two args syntax form
  var_dump( strtr($str, $replace_pairs) );
  $count++;
}
echo "*** Done ***";
$fusion = $str_arr;
$v1=$definedVars[array_rand($definedVars = get_defined_vars())];
/* Trying to copy the links across dir paths given in various notations
     and dirs having limited access */
echo "*** Testing copy() function: copying links across different directories ***\n";
$file_path = __DIR__;
$base_dir = $file_path."/copy_variation8";
mkdir($base_dir);
$sub_dir = $base_dir."/copy_variation8_sub";
mkdir($sub_dir);
$dirname_with_blank = $sub_dir."/copy variation6";
mkdir($dirname_with_blank);
$file = $file_path."/copy_variation8.tmp";
fclose( fopen($file, "w") );
$symlink = $file_path."/copy_variation8_symlink.tmp";
$hardlink = $file_path."/copy_variation8_hardlink.tmp";
symlink($file, $symlink);  //creating symlink
link($file, $hardlink);  //creating hardlink
$fusion = array(
  $base_dir."/copy_copy_variation8.tmp",
  $base_dir."/copy_variation8_sub/copy_copy_variation8.tmp",
  "$sub_dir/copy_copy_variation8.tmp",
  "$sub_dir/../copy_copy_variation8.tmp",
  "$sub_dir/../copy_variation8_sub/copy_copy_variation8.tmp",
  "$sub_dir/..///../copy_copy_variation8.tmp",
  "$sub_dir///../*",
  "$dirname_with_blank/copy_copy_variation8.tmp"
);
$count = 1;
foreach($dests as $dest) {
  echo "\n-- Iteration $count --\n";
  echo "- With symlink -\n";
  var_dump( copy($symlink, $dest) );
  var_dump( file_exists($dest) );
  var_dump( is_link($dest) ); //expected: bool(false)
  var_dump( is_file($dest) );  //expected: bool(true)
  clearstatcache();
  unlink("$dest");
  echo "- With hardlink -\n";
  var_dump( copy($hardlink, $dest) );
  var_dump( file_exists($dest) );
  var_dump( is_link($dest) );  //expected: bool(flase)
  var_dump( is_file($dest) );  //expected: bool(true)
  clearstatcache();
  unlink("$dest");
  $count++;
}
unlink($symlink);
unlink($hardlink);
unlink($file);
rmdir($dirname_with_blank);
rmdir($sub_dir);
rmdir($base_dir);
echo "*** Done ***\n";
$v2=$definedVars[array_rand($definedVars = get_defined_vars())];
$v3=$definedVars[array_rand($definedVars = get_defined_vars())];
var_dump('random_var:',$v1,$v2,$v3);
var_fusion($v1,$v2,$v3);
?>
