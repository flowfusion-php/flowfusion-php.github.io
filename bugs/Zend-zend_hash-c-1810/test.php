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
    
class pubf {
    public $a;
    static public $s;
}
class subpubf extends pubf {
}
class protf {
    protected $a;
    static protected $s;
}
class subprotf extends protf {
}
class privf {
    private $a;
    static protected $s;
}
class subprivf extends privf  {
}
$classes = array("pubf", "subpubf", "protf", "subprotf",
                 "privf", "subprivf");
foreach($classes as $class) {
    echo "Reflecting on class $class: \n";
    $rc = new ReflectionClass($class);
    echo "  --> Check for s: ";
    var_dump($rc->hasProperty("s"));
    echo "  --> Check for a: ";
    var_dump($rc->hasProperty("a"));
    echo "  --> Check for A: ";
    var_dump($rc->hasProperty("A"));
    echo "  --> Check for doesNotExist: ";
    var_dump($rc->hasProperty("doesNotExist"));
}
$fusion = $s;
$v1=$definedVars[array_rand($definedVars = get_defined_vars())];
class MemoryLeak
{
    public function __construct()
    {
        $this->things[] = $this;
    }
    public function __destruct()
    {
        $fusion->things[] = null;
    }
    private $things = [];
}
ini_set('memory_limit', '20M');
for ($i = 0; $i < 100000; ++$i) {
    $obj = new MemoryLeak();
}
echo "OK\n";
$v2=$definedVars[array_rand($definedVars = get_defined_vars())];
$v3=$definedVars[array_rand($definedVars = get_defined_vars())];
var_dump('random_var:',$v1,$v2,$v3);
var_fusion($v1,$v2,$v3);
?>
