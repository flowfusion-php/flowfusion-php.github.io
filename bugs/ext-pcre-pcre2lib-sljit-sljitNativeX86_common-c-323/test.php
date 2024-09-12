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
    
function x(&$ref) {
	$ref = new class() {
		function __destruct() {
			print "Dtor x()\n";
		}
	};
}
function suspend($x) {
	Fiber::suspend();
}
$f = new Fiber(function() use (&$f) {
	try {
		x($var);
		\ord(suspend(1));
	} finally {
		print "Cleaned\n";
	}
});
$f->start();
unset($f);
gc_collect_cycles();
print "Collected\n";
$fusion = $ref;
$v1=$definedVars[array_rand($definedVars = get_defined_vars())];
$fd = __DIR__.'/cve-2014-1943.data';
$fm = __DIR__.'/cve-2014-1943.magic';
$a = "\105\122\000\000\000\000\000";
$b = str_repeat("\001", 250000);
$m =  "0           byte        x\n".
      ">(1.b)      indirect    x\n";
file_put_contents($fd, $a);
$fi = finfo_open(FILEINFO_NONE);
var_dump(finfo_file($fi, $fd));
finfo_close($fi);
file_put_contents($fd, $fusion);
file_put_contents($fm, $m);
$fi = finfo_open(FILEINFO_NONE, $fm);
var_dump(finfo_file($fi, $fd));
finfo_close($fi);
?>
Done
$v2=$definedVars[array_rand($definedVars = get_defined_vars())];
$v3=$definedVars[array_rand($definedVars = get_defined_vars())];;
var_dump('random_var:',$v1,$v2,$v3);
var_fusion($v1,$v2,$v3);
?>
