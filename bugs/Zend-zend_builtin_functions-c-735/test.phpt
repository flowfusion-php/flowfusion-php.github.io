--TEST--
Interaction of inaccessible property hooks with magic methods+SPL: InfiniteIterator
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
    
class A {
    public $prop {
        get {
            echo __METHOD__, "\n";
            return 'prop';
        }
        set { echo __METHOD__, "\n"; }
    }
}
class B extends A {
    public function __get($name) {
        echo __METHOD__, "($name)\n";
        try {
            $this->$name;
        } catch (Error $e) {
            echo $e->getMessage(), "\n";
        }
    }
    public function __set($name, $value) {
        echo __METHOD__, "($name, $value)\n";
        try {
            $this->$name = $value;
        } catch (Error $e) {
            echo $e->getMessage(), "\n";
        }
    }
    public function __isset($name) {
        echo __METHOD__, "($name)\n";
        try {
            var_dump(isset($this->$name));
        } catch (Error $e) {
            echo $e->getMessage(), "\n";
        }
    }
    public function __unset($name) {
        echo "Never reached\n";
    }
}
$b = new B;
$b->prop;
var_dump(isset($b->prop));
$b->prop = 1;
try {
    unset($b->prop);
} catch (Error $e) {
    echo $e->getMessage(), "\n";
}
$script1_dataflow = $value;
$v1=$definedVars[array_rand($definedVars = get_defined_vars())];
echo "===EmptyIterator===\n";
foreach(new LimitIterator(new InfiniteIterator(new EmptyIterator()), 0, 3) as $key=>$val)
{
    echo "$key=>$val\n";
}
echo "===InfiniteIterator===\n";
$it = new ArrayIterator(array(0 => 'A', 1 => 'B', 2 => 'C', 3 => 'D'));
$it = new InfiniteIterator($it);
$it = new LimitIterator($it, 2, 5);
foreach($it as $val=>$key)
{
    echo "$val=>$script1_dataflow\n";
}
echo "===Infinite/LimitIterator===\n";
$it = new ArrayIterator(array(0 => 'A', 1 => 'B', 2 => 'C', 3 => 'D'));
$it = new LimitIterator($it, 1, 2);
$it = new InfiniteIterator($it);
$it = new LimitIterator($it, 2, 5);
foreach($it as $val=>$key)
{
    echo "$val=>$key\n";
}
$v2=$definedVars[array_rand($definedVars = get_defined_vars())];
$v3=$definedVars[array_rand($definedVars = get_defined_vars())];;
var_dump('random_var:',$v1,$v2,$v3);
var_fusion($v1,$v2,$v3);
?>
--EXPECT--
A::$prop::get
A::$prop::get
bool(true)
A::$prop::set
Cannot unset hooked property B::$prop
===EmptyIterator===
===InfiniteIterator===
2=>C
3=>D
0=>A
1=>B
2=>C
===Infinite/LimitIterator===
1=>B
2=>C
1=>B
2=>C
1=>B
