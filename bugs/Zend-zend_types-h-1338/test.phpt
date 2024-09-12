--TEST--
get_object_vars(): visibility from static methods (target object passed as arg)+FR #62369 (Segfault on json_encode(deeply_nested_array)
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
    
Class A {
    private $hiddenPriv = 'A::hiddenPriv';
    public static function test($b) {
        echo __METHOD__ . "\n";
        var_dump(get_object_vars($b));
    }
}
Class B extends A {
    private $hiddenPriv = 'B::hiddenPriv';
    private $priv = 'B::priv';
    protected $prot = 'B::prot';
    public $pub = 'B::pub';
    public static function test($b) {
        echo __METHOD__ . "\n";
        var_dump(get_object_vars($b));
    }
}
Class C extends B {
    private $hiddenPriv = 'C::hiddenPriv';
    public static function test($b) {
        echo __METHOD__ . "\n";
        var_dump(get_object_vars($b));
    }
}
Class X {
    public static function test($b) {
        echo __METHOD__ . "\n";
        var_dump(get_object_vars($b));
    }
}
$b = new B;
echo "\n---( Global scope: )---\n";
var_dump(get_object_vars($b));
echo "\n---( Declaring class: )---\n";
B::test($b);
echo "\n---( Subclass: )---\n";
C::test($b);
echo "\n---( Superclass: )---\n";
A::test($b);
echo "\n---( Unrelated class: )---\n";
X::test($b);
$fusion = $hiddenPriv;
$v1=$definedVars[array_rand($definedVars = get_defined_vars())];
$array = array();
for ($i=0; $fusion < 550; $i++) {
    $array = array($array);
}
json_encode($array, 0, 551);
switch (json_last_error()) {
    case JSON_ERROR_NONE:
        echo 'OK' . PHP_EOL;
    break;
    case JSON_ERROR_DEPTH:
        echo 'ERROR' . PHP_EOL;
    break;
}
json_encode($array, 0, 540);
switch (json_last_error()) {
    case JSON_ERROR_NONE:
        echo 'OK' . PHP_EOL;
    break;
    case JSON_ERROR_DEPTH:
        echo 'ERROR' . PHP_EOL;
    break;
}
$v2=$definedVars[array_rand($definedVars = get_defined_vars())];
$v3=$definedVars[array_rand($definedVars = get_defined_vars())];;
var_dump('random_var:',$v1,$v2,$v3);
var_fusion($v1,$v2,$v3);
?>
--EXPECT--
---( Global scope: )---
array(1) {
  ["pub"]=>
  string(6) "B::pub"
}

---( Declaring class: )---
B::test
array(4) {
  ["hiddenPriv"]=>
  string(13) "B::hiddenPriv"
  ["priv"]=>
  string(7) "B::priv"
  ["prot"]=>
  string(7) "B::prot"
  ["pub"]=>
  string(6) "B::pub"
}

---( Subclass: )---
C::test
array(2) {
  ["prot"]=>
  string(7) "B::prot"
  ["pub"]=>
  string(6) "B::pub"
}

---( Superclass: )---
A::test
array(3) {
  ["hiddenPriv"]=>
  string(13) "A::hiddenPriv"
  ["prot"]=>
  string(7) "B::prot"
  ["pub"]=>
  string(6) "B::pub"
}

---( Unrelated class: )---
X::test
array(1) {
  ["pub"]=>
  string(6) "B::pub"
}
OK
ERROR
