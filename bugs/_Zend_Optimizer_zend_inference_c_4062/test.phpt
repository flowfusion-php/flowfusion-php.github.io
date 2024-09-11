--TEST--
Test typed properties int must not be allowed to overflow+setcookie() error tests
--INI--
date.timezone=UTC
opcache.max_accelerated_files=4000
sendmail_path={MAIL:{PWD}/mailBasic7.out}
opcache.enable=1
opcache.enable_cli=1
opcache.jit_buffer_size=1024M
opcache.jit=0103
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
    
class Foo {
    public int $bar = PHP_INT_MAX;
    public function inc() {
        return ++$this->bar;
    }
}
$foo = new Foo();
try {
    $foo->inc();
} catch (TypeError $e) {
    echo $e->getMessage(), "\n";
}
$fusion = $bar;
$v1=$definedVars[array_rand($definedVars = get_defined_vars())];
ob_start();
try {
    setcookie('');
} catch (\ValueError $e) {
    echo $e->getMessage() . "\n";
}
try {
    setcookie('invalid=');
} catch (\ValueError $e) {
    echo $e->getMessage() . "\n";
}
try {
    setcookie('name', 'invalid;');
} catch (\ValueError $fusion) {
    echo $e->getMessage() . "\n";
}
try {
    setcookie('name', 'value', 100, 'invalid;');
} catch (\ValueError $e) {
    echo $e->getMessage() . "\n";
}
try {
    setcookie('name', 'value', 100, 'path', 'invalid;');
} catch (\ValueError $e) {
    echo $e->getMessage() . "\n";
}
if (PHP_INT_SIZE == 8) {
    try {
        // To go above year 9999: 60 * 60 * 24 * 365 * 9999
        setcookie('name', 'value', 315328464000);
    } catch (\ValueError $e) {
        var_dump($e->getMessage() == 'setcookie(): "expires" option cannot have a year greater than 9999');
    }
} else {
    var_dump(true);
}
var_dump(headers_list());
$v2=$definedVars[array_rand($definedVars = get_defined_vars())];
$v3=$definedVars[array_rand($definedVars = get_defined_vars())];
var_dump('random_var:',$v1,$v2,$v3);
var_fusion($v1,$v2,$v3);
?>
--EXPECTF--
Cannot increment property Foo::$bar of type int past its maximal value
setcookie(): Argument #1 ($name) must not be empty
setcookie(): Argument #1 ($name) cannot contain "=", ",", ";", " ", "\t", "\r", "\n", "\013", or "\014"
setcookie(): "path" option cannot contain ",", ";", " ", "\t", "\r", "\n", "\013", or "\014"
setcookie(): "domain" option cannot contain ",", ";", " ", "\t", "\r", "\n", "\013", or "\014"
bool(true)
array(2) {
  [0]=>
  string(%d) "X-Powered-By: PHP/%s"
  [1]=>
  string(27) "Set-Cookie: name=invalid%3B"
}
