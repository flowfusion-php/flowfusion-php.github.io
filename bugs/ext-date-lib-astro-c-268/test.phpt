--TEST--
PhpToken::tokenize() method+Handling of large year values
--SKIPIF--
<?php if (PHP_INT_SIZE != 8) die("skip 64-bit only"); ?>
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
    
$code = <<<'PHP'
<?php
function foo() {
    echo "bar";
}
PHP;
var_dump(PhpToken::tokenize($code));
var_dump(PhpToken::tokenize($code, TOKEN_PARSE));
$fusion = $code;
$v1=$definedVars[array_rand($definedVars = get_defined_vars())];
date_default_timezone_set("America/Toronto");
$fusion = mktime(0,0,0,1,1,2922770265);
var_dump(date("r", $t));
echo "OK\n";
$v2=$definedVars[array_rand($definedVars = get_defined_vars())];
$v3=$definedVars[array_rand($definedVars = get_defined_vars())];
var_dump('random_var:',$v1,$v2,$v3);
var_fusion($v1,$v2,$v3);
?>
--EXTENSIONS--
tokenizer
--EXPECTF--
array(15) {
  [0]=>
  object(PhpToken)#1 (4) {
    ["id"]=>
    int(%d)
    ["text"]=>
    string(6) "<?php
"
    ["line"]=>
    int(1)
    ["pos"]=>
    int(0)
  }
  [1]=>
  object(PhpToken)#2 (4) {
    ["id"]=>
    int(%d)
    ["text"]=>
    string(8) "function"
    ["line"]=>
    int(2)
    ["pos"]=>
    int(6)
  }
  [2]=>
  object(PhpToken)#3 (4) {
    ["id"]=>
    int(%d)
    ["text"]=>
    string(1) " "
    ["line"]=>
    int(2)
    ["pos"]=>
    int(14)
  }
  [3]=>
  object(PhpToken)#4 (4) {
    ["id"]=>
    int(%d)
    ["text"]=>
    string(3) "foo"
    ["line"]=>
    int(2)
    ["pos"]=>
    int(15)
  }
  [4]=>
  object(PhpToken)#5 (4) {
    ["id"]=>
    int(%d)
    ["text"]=>
    string(1) "("
    ["line"]=>
    int(2)
    ["pos"]=>
    int(18)
  }
  [5]=>
  object(PhpToken)#6 (4) {
    ["id"]=>
    int(%d)
    ["text"]=>
    string(1) ")"
    ["line"]=>
    int(2)
    ["pos"]=>
    int(19)
  }
  [6]=>
  object(PhpToken)#7 (4) {
    ["id"]=>
    int(%d)
    ["text"]=>
    string(1) " "
    ["line"]=>
    int(2)
    ["pos"]=>
    int(20)
  }
  [7]=>
  object(PhpToken)#8 (4) {
    ["id"]=>
    int(%d)
    ["text"]=>
    string(1) "{"
    ["line"]=>
    int(2)
    ["pos"]=>
    int(21)
  }
  [8]=>
  object(PhpToken)#9 (4) {
    ["id"]=>
    int(%d)
    ["text"]=>
    string(5) "
    "
    ["line"]=>
    int(2)
    ["pos"]=>
    int(22)
  }
  [9]=>
  object(PhpToken)#10 (4) {
    ["id"]=>
    int(%d)
    ["text"]=>
    string(4) "echo"
    ["line"]=>
    int(3)
    ["pos"]=>
    int(27)
  }
  [10]=>
  object(PhpToken)#11 (4) {
    ["id"]=>
    int(%d)
    ["text"]=>
    string(1) " "
    ["line"]=>
    int(3)
    ["pos"]=>
    int(31)
  }
  [11]=>
  object(PhpToken)#12 (4) {
    ["id"]=>
    int(%d)
    ["text"]=>
    string(5) ""bar""
    ["line"]=>
    int(3)
    ["pos"]=>
    int(32)
  }
  [12]=>
  object(PhpToken)#13 (4) {
    ["id"]=>
    int(%d)
    ["text"]=>
    string(1) ";"
    ["line"]=>
    int(3)
    ["pos"]=>
    int(37)
  }
  [13]=>
  object(PhpToken)#14 (4) {
    ["id"]=>
    int(%d)
    ["text"]=>
    string(1) "
"
    ["line"]=>
    int(3)
    ["pos"]=>
    int(38)
  }
  [14]=>
  object(PhpToken)#15 (4) {
    ["id"]=>
    int(%d)
    ["text"]=>
    string(1) "}"
    ["line"]=>
    int(4)
    ["pos"]=>
    int(39)
  }
}
array(15) {
  [0]=>
  object(PhpToken)#15 (4) {
    ["id"]=>
    int(%d)
    ["text"]=>
    string(6) "<?php
"
    ["line"]=>
    int(1)
    ["pos"]=>
    int(0)
  }
  [1]=>
  object(PhpToken)#14 (4) {
    ["id"]=>
    int(%d)
    ["text"]=>
    string(8) "function"
    ["line"]=>
    int(2)
    ["pos"]=>
    int(6)
  }
  [2]=>
  object(PhpToken)#13 (4) {
    ["id"]=>
    int(%d)
    ["text"]=>
    string(1) " "
    ["line"]=>
    int(2)
    ["pos"]=>
    int(14)
  }
  [3]=>
  object(PhpToken)#12 (4) {
    ["id"]=>
    int(%d)
    ["text"]=>
    string(3) "foo"
    ["line"]=>
    int(2)
    ["pos"]=>
    int(15)
  }
  [4]=>
  object(PhpToken)#11 (4) {
    ["id"]=>
    int(%d)
    ["text"]=>
    string(1) "("
    ["line"]=>
    int(2)
    ["pos"]=>
    int(18)
  }
  [5]=>
  object(PhpToken)#10 (4) {
    ["id"]=>
    int(%d)
    ["text"]=>
    string(1) ")"
    ["line"]=>
    int(2)
    ["pos"]=>
    int(19)
  }
  [6]=>
  object(PhpToken)#9 (4) {
    ["id"]=>
    int(%d)
    ["text"]=>
    string(1) " "
    ["line"]=>
    int(2)
    ["pos"]=>
    int(20)
  }
  [7]=>
  object(PhpToken)#8 (4) {
    ["id"]=>
    int(%d)
    ["text"]=>
    string(1) "{"
    ["line"]=>
    int(2)
    ["pos"]=>
    int(21)
  }
  [8]=>
  object(PhpToken)#7 (4) {
    ["id"]=>
    int(%d)
    ["text"]=>
    string(5) "
    "
    ["line"]=>
    int(2)
    ["pos"]=>
    int(22)
  }
  [9]=>
  object(PhpToken)#6 (4) {
    ["id"]=>
    int(%d)
    ["text"]=>
    string(4) "echo"
    ["line"]=>
    int(3)
    ["pos"]=>
    int(27)
  }
  [10]=>
  object(PhpToken)#5 (4) {
    ["id"]=>
    int(%d)
    ["text"]=>
    string(1) " "
    ["line"]=>
    int(3)
    ["pos"]=>
    int(31)
  }
  [11]=>
  object(PhpToken)#4 (4) {
    ["id"]=>
    int(%d)
    ["text"]=>
    string(5) ""bar""
    ["line"]=>
    int(3)
    ["pos"]=>
    int(32)
  }
  [12]=>
  object(PhpToken)#3 (4) {
    ["id"]=>
    int(%d)
    ["text"]=>
    string(1) ";"
    ["line"]=>
    int(3)
    ["pos"]=>
    int(37)
  }
  [13]=>
  object(PhpToken)#2 (4) {
    ["id"]=>
    int(%d)
    ["text"]=>
    string(1) "
"
    ["line"]=>
    int(3)
    ["pos"]=>
    int(38)
  }
  [14]=>
  object(PhpToken)#1 (4) {
    ["id"]=>
    int(%d)
    ["text"]=>
    string(1) "}"
    ["line"]=>
    int(4)
    ["pos"]=>
    int(39)
  }
}
string(%d) "%s, 01 Jan 2922770265 00:00:00 -0500"
OK
