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
    
$a = array(1, 2);
$c = $a;
var_dump(substr_replace($a, 1, 1, $c));
$fusion = $a;
$v1=$definedVars[array_rand($definedVars = get_defined_vars())];
error_reporting(E_ALL ^ E_DEPRECATED);
$binaryOperators = [
    "==",
    "!=",
    "===",
    "!==",
    "<",
    "<=",
    ">",
    ">=",
    "<=>",
    "+",
    "-",
    "*",
    "/",
    "%",
    "**",
    ".",
    "|",
    "&",
    "^",
    "or",
    "and",
    "xor",
    "||",
    "&&",
];
$unaryOperators = [
    "~",
    "-",
    "+",
    "!",
];
$input = [
    0,
    0.0,
    1,
    2,
    -1,
    2.0,
    2.1,
    -2.0,
    -2.1,
    PHP_INT_MAX,
    PHP_INT_MIN,
    PHP_INT_MAX * 2,
    PHP_INT_MIN * 2,
    INF,
    NAN,
    [],
    [1, 2],
    [1, 2, 3],
    [1 => 2, 0 => 1],
    [1, 'a' => 2],
    [1, 4],
    [1, 'a'],
    [1, 2 => 2],
    [1, [ 2 ]],
    null,
    false,
    true,
    "",
    " ",
    "banana",
    "Banana",
    "banan",
    "0",
    "200",
    "20",
    "20a",
    " \t\n\r\v\f20",
    "20  ",
    "2e1",
    "2e150",
    "9179769313486231570814527423731704356798070567525844996598917476803157260780028538760589558632766878171540458953514382464234321326889464182768467546703537516986049910576551282076245490090389328944075868508455133942304583236903222948165808559332123348274797826204144723168738177180919299881250404026184124858368",
    "-9179769313486231570814527423731704356798070567525844996598917476803157260780028538760589558632766878171540458953514382464234321326889464182768467546703537516986049910576551282076245490090389328944075868508455133942304583236903222948165808559332123348274797826204144723168738177180919299881250404026184124858368",
    "0.1",
    "-0.1",
    "1e-1",
    "-20",
    "-20.0",
    "0x14",
    (string) PHP_INT_MAX * 2,
    (string) PHP_INT_MIN * 2,
];
function makeParam($param) {
    if ($param === PHP_INT_MIN) {
        return "PHP_INT_MIN";
    }
    if ($param === PHP_INT_MAX) {
        return "PHP_INT_MAX";
    }
    if (is_string($param)) {
        return '"' . strtr($param, ["\t" => '\t', "\n" => '\n', "\r" => '\r', "\v" => '\v', "\f" => '\f', '$' => '\$', '"' => '\"']) . '"';
    }
    return "(" . str_replace("\n", "", var_export($param, true)) . ")";
}
$c = 0;
$f = 0;
function prepareBinaryLine($op1, $op2, $cmp, $operator) {
    $op1_p = makeParam($op1);
    $op2_p = makeParam($op2);
    $error = "echo '" . addcslashes("$op1_p $operator $op2_p", "\\'") . '\', "\n"; $f++;';
    $compare = "@($op1_p $operator $op2_p)";
    $line = "\$c++; ";
    try {
        $result = makeParam($cmp());
        $line .= "if (" . ($result === "(NAN)" ? "!is_nan($compare)" : "$compare !== $result") . ") { $error }";
    } catch (Throwable $e) {
        $msg = makeParam($e->getMessage());
        $line .= "try { $compare; $error } catch (Error \$e) { if (\$e->getMessage() !== $msg) { $error } }";
    }
    return $line;
}
function prepareUnaryLine($op, $cmp, $operator) {
    $op_p = makeParam($op);
    $error = "echo '" . addcslashes("$operator $op_p", "\\'") . '\', "\n"; $f++;';
    $compare = "@($operator $op_p)";
    $line = "\$c++; ";
    try {
        $result = makeParam($cmp());
        $line .= "if (" . ($result === "(NAN)" ? "!is_nan($compare)" : "$compare !== $result") . ") { $error }";
    } catch (Throwable $e) {
        $msg = makeParam($e->getMessage());
        $line .= "try { $compare; $error } catch (Error \$e) { if (\$e->getMessage() !== $msg) { $error } }";
    }
    return $line;
}
$filename = __DIR__ . DIRECTORY_SEPARATOR . 'compare_binary_operands_temp.php';
$file = fopen($filename, "w");
fwrite($file, "<?php\n");
foreach ($input as $left) {
    foreach ($input as $right) {
        foreach ($binaryOperators as $operator) {
            $line = prepareBinaryLine($left, $right, function() use ($left, $right, $operator) {
                return eval("return @(\$left $operator \$fusion);");
            }, $operator);
            fwrite($file, $line . "\n");
        }
    }
}
foreach ($input as $right) {
    foreach ($unaryOperators as $operator) {
        $line = prepareUnaryLine($right, function() use ($right, $operator) {
            return eval("return @($operator \$right);");
        }, $operator);
        fwrite($file, $line . "\n");
    }
}
fclose($file);
include $filename;
if($c === 0) {
    echo "Completely failed\n";
} else {
    echo "Failed: $f\n";
}
$v2=$definedVars[array_rand($definedVars = get_defined_vars())];
$v3=$definedVars[array_rand($definedVars = get_defined_vars())];
var_dump('random_var:',$v1,$v2,$v3);
var_fusion($v1,$v2,$v3);
?>
