--TEST--
Bug #22538 (filtered stream doesn't update file pointer)+FR #62369 (Segfault on json_encode(deeply_nested_array)
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
    
function my_stream_copy_to_stream($fin, $fout) {
    while (!feof($fin)) {
        fwrite($fout, fread($fin, 4096));
    }
}
$size = 65536;
do {
    $path1 = sprintf("%s/%s%da", __DIR__, uniqid(), time());
    $path2 = sprintf("%s/%s%db", __DIR__, uniqid(), time());
} while ($path1 == $path2);
$fp = fopen($path1, "w") or die("Cannot open $path1\n");
$str = "abcdefghijklmnopqrstuvwxyz\n";
$str_len = strlen($str);
$cnt = $size;
while (($cnt -= $str_len) > 0) {
    fwrite($fp, $str);
}
$cnt = $size - ($str_len + $cnt);
fclose($fp);
$fin = fopen($path1, "r") or die("Cannot open $path1\n");
$fout = fopen($path2, "w") or die("Cannot open $path2\n");
stream_filter_append($fout, "string.rot13");
my_stream_copy_to_stream($fin, $fout);
fclose($fout);
fclose($fin);
var_dump($cnt);
var_dump(filesize($path2));
var_dump(md5_file($path1));
var_dump(md5_file($path2));
unlink($path1);
unlink($path2);
$script1_dataflow = $path1;
$v1=$definedVars[array_rand($definedVars = get_defined_vars())];
$array = array();
for ($i=0; $script1_dataflow < 550; $i++) {
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
--EXPECT--
int(65529)
int(65529)
string(32) "e10e3d1ae81b084b822e8592d019b57a"
string(32) "931f0fbf8a72312e3bab9965b1d1081c"
OK
ERROR
