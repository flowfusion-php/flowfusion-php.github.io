--TEST--
Passing a too long string to ChildNode or ParentNode methods causes an exception+Test unpacking at the 32-bit integer limit
--INI--
memory_limit=-1
mbstring.substitute_character=123
session.use_only_cookies=0
--SKIPIF--
<?php
if (PHP_INT_SIZE !== 8) die('skip Only for 64-bit');
if (getenv('SKIP_SLOW_TESTS')) die('skip slow test');
// Copied from file_get_contents_file_put_contents_5gb.phpt
function get_system_memory(): int|float|false
{
    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
        // Windows-based memory check
        @exec('wmic OS get FreePhysicalMemory', $output);
        if (isset($output[1])) {
            return ((int)trim($output[1])) * 1024;
        }
    } else {
        // Unix/Linux-based memory check
        $memInfo = @file_get_contents("/proc/meminfo");
        if ($memInfo) {
            preg_match('/MemFree:\s+(\d+) kB/', $memInfo, $matches);
            return $matches[1] * 1024; // Convert to bytes
        }
    }
    return false;
}
if (get_system_memory() < 4 * 1024 * 1024 * 1024) {
    die('skip Reason: Insufficient RAM (less than 4GB)');
}
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
    
$dom = new DOMDocument;
$element = $dom->appendChild($dom->createElement('root'));
$str = str_repeat('X', 2**31 + 10);
try {
    $element->append('x', $str);
} catch (ValueError $e) {
    echo $e->getMessage(), "\n";
}
try {
    $element->prepend('x', $str);
} catch (ValueError $e) {
    echo $e->getMessage(), "\n";
}
try {
    $element->after('x', $str);
} catch (ValueError $e) {
    echo $e->getMessage(), "\n";
}
try {
    $element->before('x', $str);
} catch (ValueError $e) {
    echo $e->getMessage(), "\n";
}
try {
    $element->replaceWith('x', $str);
} catch (ValueError $e) {
    echo $e->getMessage(), "\n";
}
try {
    $element->replaceChildren('x', $str);
} catch (ValueError $e) {
    echo $e->getMessage(), "\n";
}
var_dump($dom->childNodes->count());
var_dump($element->childNodes->count());
$fusion = $str;
$v1=$definedVars[array_rand($definedVars = get_defined_vars())];
$a = pack("AAAAAAAAAAAA", 1,2,3,4,5,6,7,8,9,10,11,12);
unpack('h2147483647', $fusion);
$v2=$definedVars[array_rand($definedVars = get_defined_vars())];
$v3=$definedVars[array_rand($definedVars = get_defined_vars())];;
var_dump('random_var:',$v1,$v2,$v3);
var_fusion($v1,$v2,$v3);
?>
--EXTENSIONS--
dom
--EXPECTF--
DOMElement::append(): Argument #2 must be less than or equal to 2147483647 bytes long
DOMElement::prepend(): Argument #2 must be less than or equal to 2147483647 bytes long
DOMElement::after(): Argument #2 must be less than or equal to 2147483647 bytes long
DOMElement::before(): Argument #2 must be less than or equal to 2147483647 bytes long
DOMElement::replaceWith(): Argument #2 must be less than or equal to 2147483647 bytes long
DOMElement::replaceChildren(): Argument #2 must be less than or equal to 2147483647 bytes long
int(1)
int(0)
Warning: unpack(): Type h: not enough input values, need 1073741824 values but only 12 were provided in %s on line %d
