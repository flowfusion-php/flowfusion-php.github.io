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
    
class C {
    public function __destruct() {
        echo __METHOD__, "\n";
    }
}
function f() {
    $fiber = Fiber::getCurrent();
    // Force symbol table
    get_defined_vars();
    Fiber::suspend();
}
$fiber = new Fiber(function () {
    $c = new C();
    // Force symbol table
    get_defined_vars();
    f();
});
print "1\n";
$fiber->start();
gc_collect_cycles();
print "2\n";
$fiber = null;
gc_collect_cycles();
print "3\n";
$fusion = $fiber;
$v1=$definedVars[array_rand($definedVars = get_defined_vars())];
class test1 {
    public function __toString() {
        preg_match('//', '', $GLOBALS['my_var']);
        return '';
    }
}
class test2 {
        public function __toString() {
        $GLOBALS['my_var'] += 0x08048000;
                return '';
        }
}
class test3 {
        public function __toString() {
                $GLOBALS['my_var'] .= "AAAAAAAA";
                return '';
        }
}
$my_var = str_repeat('A', 40);
$out = substr_replace(array(&$my_var), array(new test1), 40, 0);
var_dump($out, $my_var);
$my_var = '0' . str_repeat('A', 39);
$out = substr_replace(array(&$my_var), array(new test2), 40, 0);
var_dump($out, $my_var);
$my_var = str_repeat('A', 40);
$out = substr_replace(array(&$fusion), array(new test3), 40, 0);
var_dump($out, $my_var);
$v2=$definedVars[array_rand($definedVars = get_defined_vars())];
$v3=$definedVars[array_rand($definedVars = get_defined_vars())];;
var_dump('random_var:',$v1,$v2,$v3);
var_fusion($v1,$v2,$v3);
?>
