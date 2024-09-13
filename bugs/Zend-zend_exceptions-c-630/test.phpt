--TEST--
Invalid State Error when getting data on DOMCharacterData out of content.+Semi reserved words support: class const
--INI--
session.sid_length=32
opcache.enable_cli=1
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
    
$character_data = new DOMCharacterData();
try {
    print $character_data->data;
} catch (DOMException $exception) {
    echo $exception->getMessage() . "\n";
}
$fusion = $exception;
$v1=$definedVars[array_rand($definedVars = get_defined_vars())];
$tokens = token_get_all('<?php
  class SomeClass {
      const CONST = 1;
      const CONTINUE = (self::CONST + 1);
      const ARRAY = [1, self::CONTINUE => [3, 4], 5];
  }
', TOKEN_PARSE);
array_walk($fusion, function($tk) {
  if(is_array($tk)) {
    if(($t = token_name($tk[0])) == 'T_WHITESPACE') return;
    echo "L{$tk[2]}: ".$t." {$tk[1]}", PHP_EOL;
  }
  else echo $tk, PHP_EOL;
});
echo "Done";
$v2=$definedVars[array_rand($definedVars = get_defined_vars())];
$v3=$definedVars[array_rand($definedVars = get_defined_vars())];
var_dump('random_var:',$v1,$v2,$v3);
var_fusion($v1,$v2,$v3);
?>
--EXTENSIONS--
dom
tokenizer
--EXPECT--
Invalid State Error
L1: T_OPEN_TAG <?php

L2: T_CLASS class
L2: T_STRING SomeClass
{
L3: T_CONST const
L3: T_STRING CONST
=
L3: T_LNUMBER 1
;
L4: T_CONST const
L4: T_STRING CONTINUE
=
(
L4: T_STRING self
L4: T_DOUBLE_COLON ::
L4: T_STRING CONST
+
L4: T_LNUMBER 1
)
;
L5: T_CONST const
L5: T_STRING ARRAY
=
[
L5: T_LNUMBER 1
,
L5: T_STRING self
L5: T_DOUBLE_COLON ::
L5: T_STRING CONTINUE
L5: T_DOUBLE_ARROW =>
[
L5: T_LNUMBER 3
,
L5: T_LNUMBER 4
]
,
L5: T_LNUMBER 5
]
;
}
Done
