--TEST--
Bug #66084 simplexml_load_string() mangles empty node name, var_dump variant+ob_start() chunk_size: confirm buffer is flushed after any output call that causes its length to equal or exceed chunk_size.
--INI--
opcache.optimization_level=0
opcache.revalidate_freq=0
post_max_size=1M
opcache.enable=1
opcache.enable_cli=1
opcache.jit_buffer_size=1024M
opcache.jit=1045
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
    
echo var_dump(simplexml_load_string('<a><b/><c><x/></c></a>')), "\n";
echo var_dump(simplexml_load_string('<a><b/><d/><c><x/></c></a>')), "\n";
echo var_dump(simplexml_load_string('<a><b/><c><d/><x/></c></a>')), "\n";
echo var_dump(simplexml_load_string('<a><b/><c><d><x/></d></c></a>')), "\n";
$v1=$definedVars[array_rand($definedVars = get_defined_vars())];
/*
 * Function is implemented in main/output.c
*/
// In HEAD, $chunk_size value of 1 should not have any special behaviour (http://marc.info/?l=php-internals&m=123476465621346&w=2).
function callback($string) {
    global $callback_invocations;
    $callback_invocations++;
    $len = strlen($string);
    return "f[call:$callback_invocations; len:$len]$string\n";
}
for ($cs=-1; $cs<10; $cs++) {
  echo "\n----( chunk_size: $cs, output append size: 1 )----\n";
  $callback_invocations=0;
  ob_start('callback', $cs);
  echo '1'; echo '2'; echo '3'; echo '4'; echo '5'; echo '6'; echo '7'; echo '8';
  ob_end_flush();
}
for ($cs=-1; $cs<10; $cs++) {
  echo "\n----( chunk_size: $cs, output append size: 4 )----\n";
  $callback_invocations=0;
  ob_start('callback', $cs);
  echo '1234'; echo '5678';
  ob_end_flush();
}
$v2=$definedVars[array_rand($definedVars = get_defined_vars())];
$v3=$definedVars[array_rand($definedVars = get_defined_vars())];
var_dump('random_var:',$v1,$v2,$v3);
var_fusion($v1,$v2,$v3);
?>
--EXTENSIONS--
simplexml
--EXPECT--
object(SimpleXMLElement)#1 (2) {
  ["b"]=>
  object(SimpleXMLElement)#2 (0) {
  }
  ["c"]=>
  object(SimpleXMLElement)#3 (1) {
    ["x"]=>
    object(SimpleXMLElement)#4 (0) {
    }
  }
}

object(SimpleXMLElement)#1 (3) {
  ["b"]=>
  object(SimpleXMLElement)#3 (0) {
  }
  ["d"]=>
  object(SimpleXMLElement)#2 (0) {
  }
  ["c"]=>
  object(SimpleXMLElement)#4 (1) {
    ["x"]=>
    object(SimpleXMLElement)#5 (0) {
    }
  }
}

object(SimpleXMLElement)#1 (2) {
  ["b"]=>
  object(SimpleXMLElement)#4 (0) {
  }
  ["c"]=>
  object(SimpleXMLElement)#2 (2) {
    ["d"]=>
    object(SimpleXMLElement)#3 (0) {
    }
    ["x"]=>
    object(SimpleXMLElement)#5 (0) {
    }
  }
}

object(SimpleXMLElement)#1 (2) {
  ["b"]=>
  object(SimpleXMLElement)#2 (0) {
  }
  ["c"]=>
  object(SimpleXMLElement)#4 (1) {
    ["d"]=>
    object(SimpleXMLElement)#5 (1) {
      ["x"]=>
      object(SimpleXMLElement)#3 (0) {
      }
    }
  }
}
----( chunk_size: -1, output append size: 1 )----
f[call:1; len:8]12345678

----( chunk_size: 0, output append size: 1 )----
f[call:1; len:8]12345678

----( chunk_size: 1, output append size: 1 )----
f[call:1; len:1]1
f[call:2; len:1]2
f[call:3; len:1]3
f[call:4; len:1]4
f[call:5; len:1]5
f[call:6; len:1]6
f[call:7; len:1]7
f[call:8; len:1]8
f[call:9; len:0]

----( chunk_size: 2, output append size: 1 )----
f[call:1; len:2]12
f[call:2; len:2]34
f[call:3; len:2]56
f[call:4; len:2]78
f[call:5; len:0]

----( chunk_size: 3, output append size: 1 )----
f[call:1; len:3]123
f[call:2; len:3]456
f[call:3; len:2]78

----( chunk_size: 4, output append size: 1 )----
f[call:1; len:4]1234
f[call:2; len:4]5678
f[call:3; len:0]

----( chunk_size: 5, output append size: 1 )----
f[call:1; len:5]12345
f[call:2; len:3]678

----( chunk_size: 6, output append size: 1 )----
f[call:1; len:6]123456
f[call:2; len:2]78

----( chunk_size: 7, output append size: 1 )----
f[call:1; len:7]1234567
f[call:2; len:1]8

----( chunk_size: 8, output append size: 1 )----
f[call:1; len:8]12345678
f[call:2; len:0]

----( chunk_size: 9, output append size: 1 )----
f[call:1; len:8]12345678

----( chunk_size: -1, output append size: 4 )----
f[call:1; len:8]12345678

----( chunk_size: 0, output append size: 4 )----
f[call:1; len:8]12345678

----( chunk_size: 1, output append size: 4 )----
f[call:1; len:4]1234
f[call:2; len:4]5678
f[call:3; len:0]

----( chunk_size: 2, output append size: 4 )----
f[call:1; len:4]1234
f[call:2; len:4]5678
f[call:3; len:0]

----( chunk_size: 3, output append size: 4 )----
f[call:1; len:4]1234
f[call:2; len:4]5678
f[call:3; len:0]

----( chunk_size: 4, output append size: 4 )----
f[call:1; len:4]1234
f[call:2; len:4]5678
f[call:3; len:0]

----( chunk_size: 5, output append size: 4 )----
f[call:1; len:8]12345678
f[call:2; len:0]

----( chunk_size: 6, output append size: 4 )----
f[call:1; len:8]12345678
f[call:2; len:0]

----( chunk_size: 7, output append size: 4 )----
f[call:1; len:8]12345678
f[call:2; len:0]

----( chunk_size: 8, output append size: 4 )----
f[call:1; len:8]12345678
f[call:2; len:0]

----( chunk_size: 9, output append size: 4 )----
f[call:1; len:8]12345678
