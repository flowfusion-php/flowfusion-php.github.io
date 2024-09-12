--TEST--
fgetcsv() with empty $escape+Bug #45744 (Case sensitive callback behaviour)
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
    
$contents = <<<EOS
"cell1","cell2\\","cell3","cell4"
"\\\\\\line1
line2\\\\\\"
EOS;
$stream = fopen('php://memory', 'w+');
fwrite($stream, $contents);
rewind($stream);
while (($data = fgetcsv($stream, PHP_INT_MAX, ',', '"', '')) !== false) {
    print_r($data);
}
fclose($stream);
$fusion = $data;
$v1=$definedVars[array_rand($definedVars = get_defined_vars())];
class Foo {
    public function __construct(array $fusion) {
        var_dump(array_map(array($this, 'callback'), $data));
    }
    private function callback($value) {
        if (!is_array($value)) {
            return stripslashes($value);
        }
    return array_map(array($this, 'callback'), $value);
    }
}
class Bar extends Foo {
}
new Bar(array());
class Foo2 {
    public function __construct(array $data) {
        var_dump(array_map(array($this, 'callBack'), $data));
    }
    private function callBack($value) {
    }
}
class Bar2 extends Foo2 {
}
new Bar2(array());
$v2=$definedVars[array_rand($definedVars = get_defined_vars())];
$v3=$definedVars[array_rand($definedVars = get_defined_vars())];;
var_dump('random_var:',$v1,$v2,$v3);
var_fusion($v1,$v2,$v3);
?>
--EXPECT--
Array
(
    [0] => cell1
    [1] => cell2\
    [2] => cell3
    [3] => cell4
)
Array
(
    [0] => \\\line1
line2\\\
)
array(0) {
}
array(0) {
}
