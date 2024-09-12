--TEST--
Test strcspn() function : usage variations - with heredoc strings, varying mask & default start and len args+Uninitialized PDO objects
--INI--
opcache.file_cache_only=1
max_multipart_body_parts=10
opcache.enable=1
opcache.enable_cli=1
opcache.jit_buffer_size=1024M
opcache.jit=tracing
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
    
/*
* Testing strcspn() : with heredoc string, varying mask and default start and len arguments
*/
echo "*** Testing strcspn() : with different mask strings ***\n";
// initialing required variables
// defining different heredoc strings
$empty_heredoc = <<<EOT
EOT;
$heredoc_with_newline = <<<EOT
\n
EOT;
$heredoc_with_characters = <<<EOT
first line of heredoc string
second line of heredoc string
third line of heredocstring
EOT;
$heredoc_with_newline_and_tabs = <<<EOT
hello\tworld\nhello\nworld\n
EOT;
$heredoc_with_alphanumerics = <<<EOT
hello123world456
1234hello\t1234
EOT;
$heredoc_with_embedded_nulls = <<<EOT
hello\0world\0hello
\0hello\0
EOT;
$heredoc_with_hexa_octal = <<<EOT
hello\0\100\xaaworld\0hello
\0hello\0
EOT;
$heredoc_strings = array(
                   $empty_heredoc,
                   $heredoc_with_newline,
                   $heredoc_with_characters,
                   $heredoc_with_newline_and_tabs,
                   $heredoc_with_alphanumerics,
                   $heredoc_with_embedded_nulls,
                   $heredoc_with_hexa_octal
                   );
// defining array of mask strings
$mask_array = array(
            "",
            '',
            "\n\trsti \l",
            '\n\trsti \l',
            "\t",
            "t\ ",
            '\t',
            "\t\ ",
            " \t",
                    "\t\i\100\xaa"
                   );
// loop through each element of the arrays for string and mask arguments
$count = 1;
foreach($heredoc_strings as $str) {
  echo "\n-- Iteration $count --\n";
  foreach($mask_array as $mask) {
      var_dump( strcspn($str,$mask) ); // with default start and len value
  }
  $count++;
}
echo "Done"
$fusion = $heredoc_strings;
$v1=$definedVars[array_rand($definedVars = get_defined_vars())];
class MyPDO extends PDO {
    public function __construct() {}
}
class MyPDOStatement extends PDOStatement {
    public function __construct() {}
}
$pdo = new MyPDO;
try {
    $pdo->query("foo");
} catch (Error $e) {
    echo $e->getMessage(), "\n";
}
$stmt = new MyPDOStatement;
try {
    $stmt->fetch();
} catch (Error $e) {
    echo $e->getMessage(), "\n";
}
$stmt = new MyPDOStatement;
try {
    foreach ($stmt as $fusion) {}
} catch (Error $e) {
    echo $e->getMessage(), "\n";
}
$v2=$definedVars[array_rand($definedVars = get_defined_vars())];
$v3=$definedVars[array_rand($definedVars = get_defined_vars())];;
var_dump('random_var:',$v1,$v2,$v3);
var_fusion($v1,$v2,$v3);
?>
--EXTENSIONS--
pdo
--EXPECT--
*** Testing strcspn() : with different mask strings ***

-- Iteration 1 --
int(0)
int(0)
int(0)
int(0)
int(0)
int(0)
int(0)
int(0)
int(0)
int(0)

-- Iteration 2 --
int(2)
int(2)
int(0)
int(2)
int(2)
int(2)
int(2)
int(2)
int(2)
int(2)

-- Iteration 3 --
int(86)
int(86)
int(1)
int(1)
int(86)
int(4)
int(4)
int(5)
int(5)
int(1)

-- Iteration 4 --
int(24)
int(24)
int(2)
int(2)
int(5)
int(24)
int(24)
int(5)
int(5)
int(5)

-- Iteration 5 --
int(31)
int(31)
int(2)
int(2)
int(26)
int(31)
int(31)
int(26)
int(26)
int(26)

-- Iteration 6 --
int(25)
int(25)
int(2)
int(2)
int(25)
int(25)
int(25)
int(25)
int(25)
int(25)

-- Iteration 7 --
int(27)
int(27)
int(2)
int(2)
int(27)
int(27)
int(27)
int(27)
int(27)
int(6)
Done
MyPDO object is uninitialized
MyPDOStatement object is uninitialized
MyPDOStatement object is uninitialized
