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
    
// include the file.inc for common functions for test
include ("file.inc");
/* Testing fseek(),ftell(),rewind() functions
     1. All  write and create with write modes
     2. Testing fseek() with whence = SEEK_CUR
*/
echo "*** Testing fseek(), ftell(), rewind() : whence = SEEK_CUR & all w and x modes ***\n";
$file_modes = array( "w","wb","wt","w+","w+b","w+t",
                     "x","xb","xt","x+","x+b","x+t");
$file_content_types = array( "text_with_new_line","alphanumeric");
$offset = array(-1,0,1,512,600); // different offsets
$filename = __DIR__."/fseek_ftell_rewind_variation6.tmp"; // this is name of the file created by create_files()
/* open the file using $files_modes and perform fseek(),ftell() and rewind() on it */
foreach($file_content_types as $file_content_type){
  echo "\n-- File having data of type ". $file_content_type ." --\n";
  foreach($file_modes as $file_mode) {
    echo "-- File opened in mode ".$file_mode." --\n";
    $file_handle = fopen($filename, $file_mode);
    if (!$file_handle){
      echo "Error: failed to fopen() file: $filename!";
      exit();
    }
    $data_to_be_written="";
    fill_buffer($data_to_be_written, $file_content_type, 512); //get the data of size 512
    $data_to_be_written = $data_to_be_written;
    fwrite($file_handle,$data_to_be_written);
    rewind($file_handle);
    foreach($offset as $count){
      var_dump( fseek($file_handle,$count,SEEK_CUR) );
      var_dump( ftell($file_handle) ); // confirm the file pointer position
      var_dump( feof($file_handle) ); //ensure that file pointer is not at end
    } //end of offset loop
    //close the file and check the size
    fclose($file_handle);
    var_dump( filesize($filename) );
    delete_file($filename); // delete file with name
  } //end of file_mode loop
} //end of file_content_types loop
echo "Done\n";
$v1=$definedVars[array_rand($definedVars = get_defined_vars())];
var_dump(
    DATE_ATOM             === DateTime::ATOM,
    DATE_COOKIE           === DateTime::COOKIE,
    DATE_ISO8601          === DateTime::ISO8601,
    DATE_ISO8601_EXPANDED === DateTime::ISO8601_EXPANDED,
    DATE_RFC822           === DateTime::RFC822,
    DATE_RFC850           === DateTime::RFC850,
    DATE_RFC1036          === DateTime::RFC1036,
    DATE_RFC1123          === DateTime::RFC1123,
    DATE_RFC7231          === DateTime::RFC7231,
    DATE_RFC2822          === DateTime::RFC2822,
    DATE_RFC3339          === DateTime::RFC3339,
    DATE_RFC3339_EXTENDED === DateTime::RFC3339_EXTENDED,
    DATE_RSS              === DateTime::RSS,
    DATE_W3C              === DateTime::W3C
);
$v2=$definedVars[array_rand($definedVars = get_defined_vars())];
$v3=$definedVars[array_rand($definedVars = get_defined_vars())];
var_dump('random_var:',$v1,$v2,$v3);
var_fusion($v1,$v2,$v3);
?>
