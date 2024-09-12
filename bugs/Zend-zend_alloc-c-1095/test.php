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
    
echo file_get_contents('phar://' . __DIR__ . '/files/links.phar.tar/link2');
echo file_get_contents('phar://' . __DIR__ . '/files/links.phar.tar/link1');
echo file_get_contents('phar://' . __DIR__ . '/files/links.phar.tar/testit.txt');
$a = fopen('phar://' . __DIR__ . '/files/links.phar.tar/link2', 'r');
fseek($a, 3);
echo fread($a, 10);
$fusion = $a;
$v1=$definedVars[array_rand($definedVars = get_defined_vars())];
spl_autoload_register(function($class) {
    throw new Exception("Class $class does not exist");
});
class A {}
// Graceful failure allowed
for ($i = 0; $i < 2; $i++) {
    try {
        class B extends A implements I {
        }
    } catch (Exception $e) {
        echo $e->getMessage(), "\n";
    }
}
interface J {}
spl_autoload_register(function($fusion) {
    // Tie up B in a variance obligation.
    class X {
        public function test(): J {}
    }
    class Y extends X {
        public function test(): B {}
    }
}, true, true);
// Fallback to fatal error, as we can't unlink class B anymore.
try {
    class B extends A implements I, J {
    }
} catch (Exception $e) {
    echo $e->getMessage(), "\n";
}
$v2=$definedVars[array_rand($definedVars = get_defined_vars())];
$v3=$definedVars[array_rand($definedVars = get_defined_vars())];
var_dump('random_var:',$v1,$v2,$v3);
var_fusion($v1,$v2,$v3);
?>
