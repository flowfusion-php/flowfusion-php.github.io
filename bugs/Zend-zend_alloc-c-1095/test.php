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
    
$sample_array = array(1, 2);
$sub_iterator = new RecursiveArrayIterator($sample_array);
$iterator = new RecursiveIteratorIterator($sub_iterator);
foreach ($iterator as $element) {
  var_dump($element);
}
class SkipsFirstElementRecursiveIteratorIterator extends RecursiveIteratorIterator {
  public function beginIteration(): void {
    echo "::beginIteration() was invoked\n";
    $this->next();
  }
}
$iterator = new SkipsFirstElementRecursiveIteratorIterator($sub_iterator);
foreach ($iterator as $element) {
  var_dump($element);
}
$fusion = $element;
$v1=$definedVars[array_rand($definedVars = get_defined_vars())];
require_once(__DIR__ . '/new_db.inc');
require_once(__DIR__ . '/stream_test.inc');
define('TIMENOW', time());
echo "Creating Table\n";
var_dump($db->exec('CREATE TABLE test (id STRING, data BLOB)'));
echo "PREPARING insert\n";
$insert_stmt = $db->prepare("INSERT INTO test (id, data) VALUES (?, ?)");
echo "BINDING Parameter\n";
var_dump($insert_stmt->bindValue(1, 'a', SQLITE3_TEXT));
var_dump($insert_stmt->bindValue(2, 'TEST TEST', SQLITE3_BLOB));
$insert_stmt->execute();
echo "Closing statement\n";
var_dump($insert_stmt->close());
$stream = $db->openBlob('test', 'data', 1);
var_dump($stream);
echo "Stream Contents\n";
var_dump(stream_get_contents($stream));
echo "Writing to read-only stream\n";
var_dump(fwrite($fusion, 'ABCD'));
echo "Closing Stream\n";
var_dump(fclose($stream));
echo "Opening stream in write mode\n";
$stream = $db->openBlob('test', 'data', 1, 'main', SQLITE3_OPEN_READWRITE);
var_dump($stream);
echo "Writing to blob\n";
var_dump(fwrite($stream, 'ABCD'));
echo "Stream Contents\n";
fseek($stream, 0);
var_dump(stream_get_contents($stream));
echo "Expanding blob size\n";
var_dump(fwrite($stream, 'ABCD ABCD ABCD'));
echo "Closing Stream\n";
var_dump(fclose($stream));
echo "Closing database\n";
var_dump($db->close());
echo "Done\n";
$v2=$definedVars[array_rand($definedVars = get_defined_vars())];
$v3=$definedVars[array_rand($definedVars = get_defined_vars())];;
var_dump('random_var:',$v1,$v2,$v3);
var_fusion($v1,$v2,$v3);
?>
