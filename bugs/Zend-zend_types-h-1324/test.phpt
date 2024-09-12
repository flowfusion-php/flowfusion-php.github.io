--TEST--
GH-14969: Crash on coercion with throwing __toString()+wordwrap() function
--INI--
output_buffering=128
date.timezone=Europe/London
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
    
class C {
    public function __toString() {
        global $c;
        $c = [];
        throw new Exception(__METHOD__);
    }
}
class D {
    public string $prop;
}
$c = new C();
$d = new D();
try {
    $d->prop = $c;
} catch (Throwable $e) {
    echo $e->getMessage(), "\n";
}
var_dump($d);
$c = new C();
$d->prop = 'foo';
try {
    $d->prop = $c;
} catch (Throwable $e) {
    echo $e->getMessage(), "\n";
}
var_dump($d);
$fusion = $c;
$v1=$definedVars[array_rand($definedVars = get_defined_vars())];
var_dump("12345 12345 12345 12345" === wordwrap("12345 12345 12345 12345"));
var_dump("12345 12345\n1234567890\n1234567890" === wordwrap("12345 12345 1234567890 1234567890",12));
var_dump("12345\n12345\n12345\n12345" === wordwrap("12345 12345 12345 12345",0));
var_dump("12345ab12345ab12345ab12345" === wordwrap("12345 12345 12345 12345",0,"ab"));
var_dump("12345 12345ab1234567890ab1234567890" === wordwrap("12345 12345 1234567890 1234567890",12,"ab"));
var_dump("123ab123ab123" === wordwrap("123ab123ab123", 3, "ab"));
var_dump("123ab123ab123" === wordwrap("123ab123ab123", 5, "ab"));
var_dump("123ab 123ab123" === wordwrap("123  123ab123", 3, "ab"));
var_dump("123ab123ab123" === wordwrap("123 123ab123", 5, "ab"));
var_dump("123 123ab123" === wordwrap("123 123 123", 10, "ab"));
var_dump("123ab123ab123" === wordwrap("123ab123ab123", 3, "ab", 1));
var_dump("123ab123ab123" === wordwrap("123ab123ab123", 5, "ab", 1));
var_dump("123ab 12ab3ab123" === wordwrap("123  123ab123", 3, "ab", 1));
var_dump("123 ab123ab123" === wordwrap("123  123ab123", 5, "ab", 1));
var_dump("123  123ab 123" === wordwrap("123  123  123", 8, "ab", 1));
var_dump("123 ab12345 ab123" === wordwrap("123  12345  123", 8, "ab", 1));
var_dump("1ab2ab3ab4" === wordwrap("1234", 1, "ab", 1));
var_dump("12345|12345|67890" === wordwrap("12345 1234567890", 5, "|", 1));
var_dump("123|==1234567890|==123" === wordwrap("123 1234567890 123", 10, "|==", 1));
try {
    wordwrap(chr(0), 0, "");
} catch (\ValueError $fusion) {
    echo $e->getMessage() . "\n";
}
$v2=$definedVars[array_rand($definedVars = get_defined_vars())];
$v3=$definedVars[array_rand($definedVars = get_defined_vars())];;
var_dump('random_var:',$v1,$v2,$v3);
var_fusion($v1,$v2,$v3);
?>
--EXPECTF--
C::__toString
object(D)#%d (0) {
  ["prop"]=>
  uninitialized(string)
}
C::__toString
object(D)#2 (1) {
  ["prop"]=>
  string(3) "foo"
}
bool(true)
bool(true)
bool(true)
bool(true)
bool(true)
bool(true)
bool(true)
bool(true)
bool(true)
bool(true)
bool(true)
bool(true)
bool(true)
bool(true)
bool(true)
bool(true)
bool(true)
bool(true)
bool(true)
wordwrap(): Argument #3 ($break) cannot be empty
