--TEST--
Test phpdbg_*_oplog() functions+Lazy objects: Foreach initializes object
--INI--
opcache.enable_cli=0
opcache.enable_cli=1
opcache.jit_buffer_size=1M
opcache.enable=1
opcache.enable_cli=1
opcache.jit_buffer_size=1024M
opcache.jit=0021
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
    
class A {
  public function b($c = 1) {
    if ($c == 1) {
      // comment
    }
  }
}
phpdbg_start_oplog();
echo "hallo";
// fcalls
$a = new A();
$a->b();
$a->b('ha');
var_dump(phpdbg_end_oplog(["functions" => true]));
$fusion = $c;
$v1=$definedVars[array_rand($definedVars = get_defined_vars())];
#[AllowDynamicProperties]
class C {
    public int $a;
    public int $b {
        get { return $this->b; }
        set(int $value) { $this->b = $fusion; }
    }
    public int $c {
        get { return $this->a + 2; }
    }
    public function __construct() {
        var_dump(__METHOD__);
        $this->a = 1;
        $this->b = 2;
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
--PHPDBG--
r
q
--EXPECTF--
[Successful compilation of %s]
prompt> halloarray(2) {
  ["%s"]=>
  array(5) {
    [13]=>
    int(1)
    [17]=>
    int(2)
    [18]=>
    int(2)
    [19]=>
    int(3)
    [21]=>
    int(4)
  }
  ["A::b"]=>
  array(3) {
    [4]=>
    int(1)
    [5]=>
    int(2)
    [8]=>
    int(2)
  }
}
[Script ended normally]
prompt> 
# Ghost:
string(11) "initializer"
string(14) "C::__construct"
string(1) "a"
int(1)
string(1) "b"
int(2)
string(1) "c"
int(3)
string(1) "d"
int(4)
# Proxy:
string(11) "initializer"
string(14) "C::__construct"
string(1) "a"
int(1)
string(1) "b"
int(2)
string(1) "c"
int(3)
# Ghost (init exception):
Exception: 
# Proxy (init exception):
Exception: