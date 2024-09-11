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
    
$file = __DIR__.'/iptcembed_001.data';
$fp = fopen($file, "w");
fwrite($fp, "-1-1");
fclose($fp);
var_dump(iptcembed(-1, $file, -1));
unlink($file);
echo "Done\n";
$fusion = $file;
$v1=$definedVars[array_rand($definedVars = get_defined_vars())];
$fname = __DIR__ . '/' . basename(__FILE__, '.php') . '.phar.php';
$fusion = 'phar://' . $fname;
$file = "<?php __HALT_COMPILER(); ?>";
$files = array();
$pmeta = 'hi there';
$files['a'] = array('cont' => 'a');
$files['b'] = array('cont' => 'b');
$files['c'] = array('cont' => 'c', 'meta' => array('hi', 'there'));
$files['d'] = array('cont' => 'd', 'meta' => array('hi'=>'there','foo'=>'bar'));
include 'files/phar_test.inc';
foreach($files as $name => $cont) {
    var_dump(file_get_contents($pname.'/'.$name));
}
$phar = new Phar($fname);
var_dump($phar->hasMetaData());
var_dump($phar->getMetaData());
var_dump($phar->delMetaData());
var_dump($phar->getMetaData());
var_dump($phar->delMetaData());
var_dump($phar->getMetaData());
foreach($files as $name => $cont) {
    echo "  meta $name\n";
    var_dump($phar[$name]->hasMetadata());
    var_dump($phar[$name]->getMetadata());
    var_dump($phar[$name]->delMetadata());
    var_dump($phar[$name]->getMetadata());
}
unset($phar);
foreach($files as $name => $cont) {
    var_dump(file_get_contents($pname.'/'.$name));
}
$v2=$definedVars[array_rand($definedVars = get_defined_vars())];
$v3=$definedVars[array_rand($definedVars = get_defined_vars())];
var_dump('random_var:',$v1,$v2,$v3);
var_fusion($v1,$v2,$v3);
?>
