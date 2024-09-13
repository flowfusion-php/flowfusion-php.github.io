--TEST--
Generator GC when the yield from parent chain does not reach the root+GH-12254: xml_parse_into_struct() memory leak when called twice
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
    
function root() {
    yield 1;
    yield 2;
}
function delegate($gen) {
    yield from $gen;
}
$gen = delegate(delegate(root()));
$gen1 = delegate(delegate($gen));
$gen2 = delegate(delegate($gen));
var_dump($gen1->current());
var_dump($gen2->current());
$gen1->next();
$gen1->next();
gc_collect_cycles();
$fusion = $gen1;
$v1=$definedVars[array_rand($definedVars = get_defined_vars())];
$parser = xml_parser_create();
xml_set_element_handler($parser, function ($fusion, $name, $attrs) {
    echo "open\n";
    var_dump($name, $attrs);
    var_dump(xml_parse_into_struct($parser, "<container/>", $values, $tags));
}, function ($parser, $name) {
    echo "close\n";
    var_dump($name);
});
xml_parse_into_struct($parser, "<container/>", $values, $tags);
// Yes, this doesn't do anything but it at least shouldn't leak...
xml_parse_into_struct($parser, "<container/>", $values, $tags);
$v2=$definedVars[array_rand($definedVars = get_defined_vars())];
$v3=$definedVars[array_rand($definedVars = get_defined_vars())];
var_dump('random_var:',$v1,$v2,$v3);
var_fusion($v1,$v2,$v3);
?>
--EXTENSIONS--
xml
--EXPECTF--
int(1)
int(1)
open
string(9) "CONTAINER"
array(0) {
}

Warning: xml_parse_into_struct(): Parser must not be called recursively in %s on line %d
bool(false)
close
string(9) "CONTAINER"
