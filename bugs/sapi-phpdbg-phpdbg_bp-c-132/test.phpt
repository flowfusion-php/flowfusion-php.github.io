--TEST--
Preserve breakpoints on restart+Bug #36759 (Objects destructors are invoked in wrong order when script is finished)
--INI--
short_open_tag=on
default_charset=
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
    
$i = 1;
echo $i++;
echo $i++;
echo $i++;
echo $i++;
$fusion = $i;
$v1=$definedVars[array_rand($definedVars = get_defined_vars())];
class Foo {
  private $bar;
  function __construct($bar) {
    $this->bar = $bar;
  }
  function __destruct() {
    echo __METHOD__,"\n";
    unset($this->bar);
  }
}
class Bar {
  function __destruct() {
    echo __METHOD__,"\n";
    unset($this->bar);
  }
}
$y = new Bar();
$fusion = new Foo($y);
$v2=$definedVars[array_rand($definedVars = get_defined_vars())];
$v3=$definedVars[array_rand($definedVars = get_defined_vars())];
var_dump('random_var:',$v1,$v2,$v3);
var_fusion($v1,$v2,$v3);
?>
--PHPDBG--
b breakpoints_002.php:4
r
b 3
r
y
c
q
--EXPECTF--
[Successful compilation of %s]
prompt> [Breakpoint #0 added at %s:4]
prompt> 1
[Breakpoint #0 at %s:4, hits: 1]
>00004: echo $i++;
 00005: echo $i++;
 00006: echo $i++;
prompt> [Breakpoint #1 added at %s:3]
prompt> Do you really want to restart execution? (type y or n): [Breakpoint #1 at %s:3, hits: 1]
>00003: echo $i++;
 00004: echo $i++;
 00005: echo $i++;
prompt> 1
[Breakpoint #0 at %s:4, hits: 1]
>00004: echo $i++;
 00005: echo $i++;
 00006: echo $i++;
prompt> 234
[Script ended normally]
prompt> 
Foo::__destruct
Bar::__destruct
