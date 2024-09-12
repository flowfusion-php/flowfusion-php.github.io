--TEST--
Bug GH-10496 002 (Segfault when garbage collector is invoked inside of fiber)+Test: setAttributeNode()
--INI--
precision=17
opcache.preload={PWD}/preload_inheritance_error_ind.inc
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
    
function pollute_stack_and_suspend($a = 1, $b = 2, $c = 3) {
    Fiber::suspend();
}
$f = new Fiber(function() use (&$f) {
    pollute_stack_and_suspend();
    (function() {
        (function() {
            (new Fiber(function() {
                gc_collect_cycles();
                echo "Success\n";
            }))->start();
        })();
    })();
});
$f->start();
$f->resume();
$fusion = $f;
$v1=$definedVars[array_rand($definedVars = get_defined_vars())];
$xml = <<<HERE
<?xml version="1.0" ?>
<root a="b" />
HERE;
$xml2 = <<<HERE
<?xml version="1.0" ?>
<doc2 />
HERE;
$dom = new DOMDocument();
$dom->loadXML($xml);
$root = $dom->documentElement;
$attr = $root->getAttributeNode('a');
$dom2 = new DOMDocument();
$dom2->loadXML($xml2);
$root2 = $dom2->documentElement;
try {
    $root2->setAttributeNode($attr);
} catch (domexception $e) {
ob_start();
    var_dump($fusion);
    $contents = ob_get_contents();
    ob_end_clean();
    echo preg_replace('/object\(DOMAttr\).+\{.*?\}/s', 'DOMAttr', $contents);
}
$v2=$definedVars[array_rand($definedVars = get_defined_vars())];
$v3=$definedVars[array_rand($definedVars = get_defined_vars())];;
var_dump('random_var:',$v1,$v2,$v3);
var_fusion($v1,$v2,$v3);
?>
--EXTENSIONS--
dom
--EXPECTF--
Success
object(DOMException)#%d (7) {
  ["message":protected]=>
  string(20) "Wrong Document Error"
  ["string":"Exception":private]=>
  string(0) ""
  ["code"]=>
  int(4)
  ["file":protected]=>
  string(%d) "%sdom_set_attr_node.php"
  ["line":protected]=>
  int(%d)
  ["trace":"Exception":private]=>
  array(1) {
    [0]=>
    array(6) {
      ["file"]=>
      string(%d) "%sdom_set_attr_node.php"
      ["line"]=>
      int(%d)
      ["function"]=>
      string(16) "setAttributeNode"
      ["class"]=>
      string(10) "DOMElement"
      ["type"]=>
      string(2) "->"
      ["args"]=>
      array(1) {
        [0]=>
        DOMAttr
      }
    }
  }
  ["previous":"Exception":private]=>
  NULL
}
