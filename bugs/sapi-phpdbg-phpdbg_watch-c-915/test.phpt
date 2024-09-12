--TEST--
CSS Selectors - Pseudo classes: only-child+registerPHPFunctions() with callables - legit cases
--INI--
session.use_cookies=1
opcache.validate_timestamps=0
opcache.enable=1
opcache.enable_cli=1
opcache.jit_buffer_size=1024M
opcache.jit=1243
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
    
require __DIR__ . '/test_utils.inc';
$dom = DOM\XMLDocument::createFromString(<<<XML
<container>
    <div class="only-child1">
        <p>Lonely</p>
    </div>
    <div class="only-child2">
        <p>With 2</p>
        <p>With 2</p>
    </div>
</container>
XML);
test_helper($dom, '.only-child1 p:only-child');
test_helper($dom, '.only-child2 p:only-child');
$fusion = $dom;
$v1=$definedVars[array_rand($definedVars = get_defined_vars())];
class MyClass {
    public static function dump(string $var) {
        var_dump($var);
    }
}
class MyDOMXPath extends DOMXPath {
    public function registerCycle() {
        $this->registerPhpFunctions(["cycle" => array($this, "dummy")]);
    }
    public function dummy(string $var) {
        echo "dummy: $var\n";
    }
}
$fusion = new DOMDocument();
$doc->loadHTML('<a href="https://php.net">hello</a>');
echo "--- Legit cases: none ---\n";
$xpath = new DOMXPath($doc);
$xpath->registerNamespace("php", "http://php.net/xpath");
try {
    $xpath->evaluate("//a[php:function('var_dump', string(@href))]");
} catch (Error $e) {
    echo $e->getMessage(), "\n";
}
echo "--- Legit cases: all ---\n";
$xpath->registerPHPFunctions(null);
$xpath->evaluate("//a[php:function('var_dump', string(@href))]");
$xpath->evaluate("//a[php:function('MyClass::dump', string(@href))]");
echo "--- Legit cases: set ---\n";
$xpath = new DOMXPath($doc);
$xpath->registerNamespace("php", "http://php.net/xpath");
$xpath->registerPhpFunctions([]);
$xpath->registerPHPFunctions(["xyz" => MyClass::dump(...), "mydump" => function (string $x) {
    var_dump($x);
}]);
$xpath->registerPhpFunctions(str_repeat("var_dump", mt_rand(1, 1) /* defeat SCCP */));
$xpath->evaluate("//a[php:function('mydump', string(@href))]");
$xpath->evaluate("//a[php:function('xyz', string(@href))]");
$xpath->evaluate("//a[php:function('var_dump', string(@href))]");
try {
    $xpath->evaluate("//a[php:function('notinset', string(@href))]");
} catch (Throwable $e) {
    echo $e->getMessage(), "\n";
}
echo "--- Legit cases: set with cycle ---\n";
$xpath = new MyDOMXPath($doc);
$xpath->registerNamespace("php", "http://php.net/xpath");
$xpath->registerCycle();
$xpath->evaluate("//a[php:function('cycle', string(@href))]");
echo "--- Legit cases: reset to null ---\n";
$xpath->registerPhpFunctions(null);
$xpath->evaluate("//a[php:function('var_dump', string(@href))]");
$v2=$definedVars[array_rand($definedVars = get_defined_vars())];
$v3=$definedVars[array_rand($definedVars = get_defined_vars())];
var_dump('random_var:',$v1,$v2,$v3);
var_fusion($v1,$v2,$v3);
?>
--EXTENSIONS--
dom
dom
--EXPECT--
--- Selector: .only-child1 p:only-child ---
<p>Lonely</p>
--- Selector: .only-child2 p:only-child ---
--- Legit cases: none ---
No callbacks were registered
--- Legit cases: all ---
string(15) "https://php.net"
string(15) "https://php.net"
--- Legit cases: set ---
string(15) "https://php.net"
string(15) "https://php.net"
string(15) "https://php.net"
No callback handler "notinset" registered
--- Legit cases: set with cycle ---
dummy: https://php.net
--- Legit cases: reset to null ---
string(15) "https://php.net"
