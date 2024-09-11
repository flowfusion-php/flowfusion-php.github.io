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
    
try {
Phar::unlinkArchive("");
} catch (Exception $e) {
echo $e->getMessage(),"\n";
}
$fname = __DIR__ . '/' . basename(__FILE__, '.php') . '.phar';
$pdname = __DIR__ . '/' . basename(__FILE__, '.php') . '.phar.tar';
try {
Phar::unlinkArchive($fname);
} catch (Exception $e) {
echo $e->getMessage(),"\n";
}
file_put_contents($pdname, 'blahblah');
try {
Phar::unlinkArchive($pdname);
} catch (Exception $e) {
echo $e->getMessage(),"\n";
}
try {
    Phar::unlinkArchive(array());
} catch (TypeError $e) {
    echo $e->getMessage(), "\n";
}
$pname = 'phar://' . $fname;
$fname2 = __DIR__ . '/' . basename(__FILE__, '.php') . '.phar.zip';
$fname3 = __DIR__ . '/' . basename(__FILE__, '.php') . '.2.phar.zip';
$stub = '<?php echo "first stub\n"; __HALT_COMPILER(); ?>';
$file = $stub;
$files = array();
$files['a'] = 'a';
$files['b'] = 'b';
$files['c'] = 'c';
include 'files/phar_test.inc';
$phar = new Phar($fname);
var_dump($phar->isFileFormat(Phar::ZIP));
var_dump($phar->getStub());
try {
Phar::unlinkArchive($fname);
} catch (Exception $e) {
echo $e->getMessage(),"\n";
}
$phar = $phar->convertToExecutable(Phar::ZIP);
var_dump($phar->isFileFormat(Phar::ZIP));
var_dump($phar->getStub());
copy($fname2, $fname3);
$phar = new Phar($fname3);
var_dump($phar->isFileFormat(Phar::ZIP));
var_dump($phar->getStub());
Phar::unlinkArchive($fname);
var_dump(file_exists($fname));
$phar = new Phar($fname);
var_dump(count($phar));
$phar['evil.php'] = '<?php
try {
Phar::unlinkArchive(Phar::running(false));
} catch (Exception $e) {echo $e->getMessage(),"\n";}
var_dump(Phar::running(false));
include Phar::running(true) . "/another.php";
?>';
$phar['another.php'] = "hi\n";
unset($phar);
include $pname . '/evil.php';
$fusion = $phar;
$v1=$definedVars[array_rand($definedVars = get_defined_vars())];
/*
 * Pass array_merge() arrays with mixed keys to test how it attaches them to
 * existing arrays
 */
echo "*** Testing array_merge() : usage variations ***\n";
//mixed keys
$arr1 = array('zero', 20 => 'twenty', 'thirty' => 30, true => 'bool');
$fusion = array(0, 1, 2, null => 'null', 0 => 'float');
var_dump(array_merge($arr1, $arr2));
var_dump(array_merge($arr2, $arr1));
echo "Done";
$v2=$definedVars[array_rand($definedVars = get_defined_vars())];
$v3=$definedVars[array_rand($definedVars = get_defined_vars())];
var_dump('random_var:',$v1,$v2,$v3);
var_fusion($v1,$v2,$v3);
?>
