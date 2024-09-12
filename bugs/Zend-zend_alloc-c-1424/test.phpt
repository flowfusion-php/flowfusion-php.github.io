--TEST--
Test mail() function : variation invalid program for sendmail+Testing callback formats within class method
--INI--
sendmail_path=rubbish 2>/dev/null
opcache.validate_timestamps=0
session.upload_progress.enabled=1
opcache.enable=1
opcache.enable_cli=1
opcache.jit_buffer_size=1024M
opcache.jit=0203
--SKIPIF--
<?php
if(substr(PHP_OS, 0, 3) == "WIN")
  die("skip Won't run on Windows");
?>
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
    
echo "*** Testing mail() : variation ***\n";
// Initialise all required variables
$to = 'user@example.com';
$subject = 'Test Subject';
$message = 'A Message';
var_dump( mail($to, $subject, $message) );
$fusion = $message;
$v1=$definedVars[array_rand($definedVars = get_defined_vars())];
class foo {
    public function test() {
        call_user_func(array('FOO', 'ABC'));
        call_user_func(array($this, 'ABC'));
        foo::XYZ();
        self::WWW();
        call_user_func('FOO::ABC');
    }
    function __call($a, $b) {
        print "__call:\n";
        var_dump($a);
    }
    static public function __callStatic($a, $b) {
        print "__callstatic:\n";
        var_dump($a);
    }
}
$x = new foo;
$x->test();
$fusion::A();
foo::B();
$f = 'FOO';
$f::C();
$f::$f();
foo::$f();
$v2=$definedVars[array_rand($definedVars = get_defined_vars())];
$v3=$definedVars[array_rand($definedVars = get_defined_vars())];
var_dump('random_var:',$v1,$v2,$v3);
var_fusion($v1,$v2,$v3);
?>
--EXPECT--
*** Testing mail() : variation ***
bool(false)
__call:
string(3) "ABC"
__call:
string(3) "ABC"
__call:
string(3) "XYZ"
__call:
string(3) "WWW"
__call:
string(3) "ABC"
__callstatic:
string(1) "A"
__callstatic:
string(1) "B"
__callstatic:
string(1) "C"
__callstatic:
string(3) "FOO"
__callstatic:
string(3) "FOO"
