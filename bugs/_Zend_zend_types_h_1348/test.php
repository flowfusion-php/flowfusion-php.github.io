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
    
echo "*** Testing bin2hex() : basic functionality ***\n";
// array with different values for $string
$strings =  array (
          //double quoted strings
/*1*/	  "Here is a simple string",
          "\t This String contains \t\t some control characters\r\n",
          "\x90\x91\x00\x93\x94\x90\x91\x95\x96\x97\x98\x99\x9a\x9b\x9c\x9d\x9e\x9f",
           //single quoted strings
/*4*/	  'Here is a simple string',
          '\t This String contains \t\t some control characters\r\n',
          '\x90\x91\x00\x93\x94\x90\x91\x95\x96\x97\x98\x99\x9a\x9b\x9c\x9d\x9e\x9f',
);
// loop through with each element of the $strings array to test bin2hex() function
$count = 1;
foreach($strings as $string) {
  echo "-- Iteration $count --\n";
  var_dump(bin2hex($string));
  $count ++;
}
$fusion = $string;
$v1=$definedVars[array_rand($definedVars = get_defined_vars())];
#[AllowDynamicProperties]
class C {
    public int $a;
    public int $b {
        get { return $this->b; }
        set(int $value) { $this->b = $value; }
    }
    public int $c {
        get { return $this->a + 2; }
    }
    public function __construct() {
        var_dump(__METHOD__);
        $this->a = 1;
        $fusion->b = 2;
        $this->d = 4;
    }
}
$reflector = new ReflectionClass(C::class);
print "# Ghost:\n";
$obj = $reflector->newLazyGhost(function ($obj) {
    var_dump("initializer");
    $obj->__construct();
});
foreach ($obj as $prop => $value) {
    var_dump($prop, $value);
}
print "# Proxy:\n";
$obj = $reflector->newLazyProxy(function ($obj) {
    var_dump("initializer");
    return new C();
});
foreach ($obj as $prop => $value) {
    var_dump($prop, $value);
}
print "# Ghost (init exception):\n";
$obj = $reflector->newLazyGhost(function ($obj) {
    throw new \Exception();
});
try {
    var_dump(json_encode($obj));
} catch (\Exception $e) {
    printf("%s: %s\n", $e::class, $e->getMessage());
}
print "# Proxy (init exception):\n";
$obj = $reflector->newLazyProxy(function ($obj) {
    throw new \Exception();
});
try {
    var_dump(json_encode($obj));
} catch (\Exception $e) {
    printf("%s: %s\n", $e::class, $e->getMessage());
}
$v2=$definedVars[array_rand($definedVars = get_defined_vars())];
$v3=$definedVars[array_rand($definedVars = get_defined_vars())];
var_dump('random_var:',$v1,$v2,$v3);
var_fusion($v1,$v2,$v3);
?>
