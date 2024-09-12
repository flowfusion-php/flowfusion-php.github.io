--TEST--
yield from on multiple trees needing merge+Test strftime() function : usage variation - Checking day related formats which was not supported on Windows before VC14.
--INI--
opcache.jit=0
error_reporting=1
opcache.enable=1
opcache.enable_cli=1
opcache.jit_buffer_size=1024M
opcache.jit=1003
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
    
function from($levels) {
    foreach (range(0, 2 << $levels) as $v) {
        yield $v;
        if ($v == (1 << ($levels - 1)) - 2) {
            throw new Exception();
        }
    }
}
function gen($gen, $level) {
    yield from $gen;
}
$levels = 5;
print "$levels levels\n\n";
$all = array();
$all[] = $gens[0][0] = from($levels);
for ($level = 1; $level < $levels; $level++) {
    for ($i = 0; $i < (1 << $level); $i++) {
        $all[] = $gens[$level][$i] = gen($gens[$level-1][$i >> 1], $level);
    }
}
for ($i = 0; $i < 2; $i++) {
    try {
        foreach ($all as $gen) {
            var_dump($gen->current());
            $gen->next();
            if (!$gen->valid()) {
                break;
            }
        }
    } catch(Exception $e) {
        print "$e\n";
        unset($all[array_search($gen, $all)]);
    }
}
$fusion = $levels;
$v1=$definedVars[array_rand($definedVars = get_defined_vars())];
echo "*** Testing strftime() : usage variation ***\n";
// Initialise function arguments not being substituted (if any)
setlocale(LC_ALL, "C");
date_default_timezone_set("Asia/Calcutta");
$timestamp = mktime(8, 8, 8, 8, 8, 2008);
echo "\n-- Testing strftime() function with Day of the month as decimal single digit format --\n";
$format = "%e";
var_dump( strftime($format) );
var_dump( strftime($format, $fusion) );
$v2=$definedVars[array_rand($definedVars = get_defined_vars())];
$v3=$definedVars[array_rand($definedVars = get_defined_vars())];
var_dump('random_var:',$v1,$v2,$v3);
var_fusion($v1,$v2,$v3);
?>
--EXPECTF--
5 levels

int(0)
int(1)
int(2)
int(3)
int(4)
int(5)
int(6)
int(7)
int(8)
int(9)
int(10)
int(11)
int(12)
int(13)
int(14)
Exception in %s:%d
Stack trace:
#0 %s(%d): from(5)
#1 %s(%d): gen(Object(Generator), 1)
#2 %s(%d): gen(Object(Generator), 2)
#3 [internal function]: gen(Object(Generator), 3)
#4 %s(%d): Generator->next()
#5 {main}
ClosedGeneratorException: Generator yielded from aborted, no return value available in %s:%d
Stack trace:
#0 [internal function]: gen(Object(Generator), 1)
#1 %s(%d): Generator->current()
#2 {main}
*** Testing strftime() : usage variation ***

-- Testing strftime() function with Day of the month as decimal single digit format --

Deprecated: Function strftime() is deprecated since 8.1, use IntlDateFormatter::format() instead in %s on line %d
string(2) "%A%d"

Deprecated: Function strftime() is deprecated since 8.1, use IntlDateFormatter::format() instead in %s on line %d
string(2) " 8"
