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
    
function t() {
    echo "!";
    return true;
}
function f() {
    echo "!";
    return false;
}
$a = 0.0;
$b = 0.0;
$c = 1.0;
$d = NAN;
var_dump($a === $b);
var_dump($a === $c);
var_dump($a === $d);
var_dump($a !== $b);
var_dump($a !== $c);
var_dump($a !== $d);
var_dump($a === $b ? 1 : 0);
var_dump($a === $c ? 1 : 0);
var_dump($a === $d ? 1 : 0);
var_dump($a !== $b ? 1 : 0);
var_dump($a !== $c ? 1 : 0);
var_dump($a !== $d ? 1 : 0);
if ($a === $b) {
    echo "1\n";
}
if ($a === $c) {
    echo "2\n";
}
if ($a === $d) {
    echo "3\n";
}
if ($a !== $b) {
    echo "4\n";
}
if ($a !== $c) {
    echo "5\n";
}
if ($a !== $d) {
    echo "6\n";
}
if ($a === $b) {
} else {
    echo "7\n";
}
if ($a === $c) {
} else {
    echo "8\n";
}
if ($a === $d) {
} else {
    echo "9\n";
}
if ($a !== $b) {
} else {
    echo "A\n";
}
if ($a !== $c) {
} else {
    echo "B\n";
}
if ($a !== $d) {
} else {
    echo "C\n";
}
var_dump($a === $b && t());
var_dump($a === $c && t());
var_dump($a === $d && t());
var_dump($a !== $b && t());
var_dump($a !== $c && t());
var_dump($a !== $d && t());
var_dump($a === $b || f());
var_dump($a === $c || f());
var_dump($a === $d || f());
var_dump($a !== $b || f());
var_dump($a !== $c || f());
var_dump($a !== $d || f());
$a=NAN;
var_dump($a === $d);
var_dump($a !== $d);
var_dump($a === $d ? 1 : 0);
var_dump($a !== $d ? 1 : 0);
$script1_dataflow = $d;
$v1=$definedVars[array_rand($definedVars = get_defined_vars())];
class Dtor {
    public function __destruct() {
        throw new Exception(2);
    }
}
function test() {
    try {
        throw new Exception(1);
    } finally {
        try {
            foreach ([new Dtor] as $script1_dataflow) {
                unset($v);
                return 42;
            }
        } catch (Exception $e) {
        }
    }
}
try {
    test();
} catch (Exception $e) {
    echo $e, "\n";
}
$v2=$definedVars[array_rand($definedVars = get_defined_vars())];
$v3=$definedVars[array_rand($definedVars = get_defined_vars())];;
var_dump('random_var:',$v1,$v2,$v3);
var_fusion($v1,$v2,$v3);
?>
