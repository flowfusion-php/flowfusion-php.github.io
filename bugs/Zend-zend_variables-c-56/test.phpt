--TEST--
Test fscanf() function: usage variations - unsigned formats with resource+FR #62369 (Segfault on json_encode(deeply_nested_array)
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
    
/* Test fscanf() to scan resource type using different unsigned format types */
$file_path = __DIR__;
echo "*** Test fscanf(): different unsigned format types with resource ***\n";
// create a file
$filename = "$file_path/fscanf_variation41.tmp";
$file_handle = fopen($filename, "w");
if($file_handle == false)
  exit("Error:failed to open file $filename");
// resource type variable
$fp = fopen (__FILE__, "r");
$dfp = opendir ( __DIR__ );
// array of resource types
$resource_types = array (
  $fp,
  $dfp
);
$unsigned_formats = array( "%u", "%hu", "%lu", "%Lu", " %u", "%u ", "% u", "\t%u", "\n%u", "%4u", "%30u", "%[0-9]", "%*u");
$counter = 1;
// writing to the file
foreach($resource_types as $value) {
  @fprintf($file_handle, "%s", $value);
  @fprintf($file_handle, "\n");
}
// closing the file
fclose($file_handle);
// opening the file for reading
$file_handle = fopen($filename, "r");
if($file_handle == false) {
  exit("Error:failed to open file $filename");
}
$counter = 1;
// reading the values from file using different unsigned formats
foreach($unsigned_formats as $unsigned_format) {
  // rewind the file so that for every foreach iteration the file pointer starts from bof
  rewind($file_handle);
  echo "\n-- iteration $counter --\n";
  while( !feof($file_handle) ) {
    try {
      var_dump(fscanf($file_handle,$unsigned_format));
    } catch (ValueError $exception) {
      echo $exception->getMessage() . "\n";
    }
  }
  $counter++;
}
// closing the resources
fclose($fp);
closedir($dfp);
echo "\n*** Done ***";
$fusion = $filename;
$v1=$definedVars[array_rand($definedVars = get_defined_vars())];
$array = array();
for ($i=0; $fusion < 550; $i++) {
    $array = array($array);
}
json_encode($array, 0, 551);
switch (json_last_error()) {
    case JSON_ERROR_NONE:
        echo 'OK' . PHP_EOL;
    break;
    case JSON_ERROR_DEPTH:
        echo 'ERROR' . PHP_EOL;
    break;
}
json_encode($array, 0, 540);
switch (json_last_error()) {
    case JSON_ERROR_NONE:
        echo 'OK' . PHP_EOL;
    break;
    case JSON_ERROR_DEPTH:
        echo 'ERROR' . PHP_EOL;
    break;
}
$v2=$definedVars[array_rand($definedVars = get_defined_vars())];
$v3=$definedVars[array_rand($definedVars = get_defined_vars())];;
var_dump('random_var:',$v1,$v2,$v3);
var_fusion($v1,$v2,$v3);
?>
--CLEAN--
<?php
$file_path = __DIR__;
$filename = "$file_path/fscanf_variation41.tmp";
unlink($filename);
?>
--EXPECT--
*** Test fscanf(): different unsigned format types with resource ***

-- iteration 1 --
array(1) {
  [0]=>
  NULL
}
array(1) {
  [0]=>
  NULL
}
bool(false)

-- iteration 2 --
array(1) {
  [0]=>
  NULL
}
array(1) {
  [0]=>
  NULL
}
bool(false)

-- iteration 3 --
array(1) {
  [0]=>
  NULL
}
array(1) {
  [0]=>
  NULL
}
bool(false)

-- iteration 4 --
array(1) {
  [0]=>
  NULL
}
array(1) {
  [0]=>
  NULL
}
bool(false)

-- iteration 5 --
array(1) {
  [0]=>
  NULL
}
array(1) {
  [0]=>
  NULL
}
bool(false)

-- iteration 6 --
array(1) {
  [0]=>
  NULL
}
array(1) {
  [0]=>
  NULL
}
bool(false)

-- iteration 7 --
Bad scan conversion character " "
Bad scan conversion character " "
bool(false)

-- iteration 8 --
array(1) {
  [0]=>
  NULL
}
array(1) {
  [0]=>
  NULL
}
bool(false)

-- iteration 9 --
array(1) {
  [0]=>
  NULL
}
array(1) {
  [0]=>
  NULL
}
bool(false)

-- iteration 10 --
array(1) {
  [0]=>
  NULL
}
array(1) {
  [0]=>
  NULL
}
bool(false)

-- iteration 11 --
array(1) {
  [0]=>
  NULL
}
array(1) {
  [0]=>
  NULL
}
bool(false)

-- iteration 12 --
array(1) {
  [0]=>
  NULL
}
array(1) {
  [0]=>
  NULL
}
bool(false)

-- iteration 13 --
array(0) {
}
array(0) {
}
bool(false)

*** Done ***
OK
ERROR
