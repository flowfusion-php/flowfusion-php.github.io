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
    
class EchoesOnWakeup {
    public function __wakeup() {
        echo "In __wakeup " . spl_object_id($this) . "\n";
    }
    public function __destruct() {
        echo "In __destruct " . spl_object_id($this) . "\n";
    }
}
class ThrowsOnSerialize {
    public function __sleep() {
        throw new RuntimeException("In sleep");
    }
}
$fname = __DIR__ . '/' . basename(__FILE__, '.php') . '.phar.php';
$pname = 'phar://' . $fname;
$file = "<?php __HALT_COMPILER(); ?>";
$files = array();
$files['a'] = array('cont' => 'a', 'meta' => new EchoesOnWakeup());
include 'files/phar_test.inc';
foreach($files as $name => $cont) {
    var_dump(file_get_contents($pname.'/'.$name));
}
unset($files);
$phar = new Phar($fname);
echo "Loading metadata for 'a' without allowed_classes\n";
var_dump($phar['a']->getMetadata(['allowed_classes' => []]));
echo "Loading metadata for 'a' with allowed_classes\n";
var_dump($phar['a']->getMetadata(['allowed_classes' => true]));
unset($phar);
// NOTE: Phar will use the cached value of metadata if setMetaData was called on that Phar path before.
// Save the writes to the phar and use a different file path.
$fname_new = "$fname.copy.php";
copy($fname, $fname_new);
$phar = new Phar($fname_new);
echo "Loading metadata from 'a' from the new phar\n";
var_dump($phar['a']->getMetadata());
echo "Loading metadata from 'a' from the new phar with unserialize options\n";
var_dump($phar['a']->getMetadata(['allowed_classes' => true]));
// PharEntry->setMetaData will do the following:
// 1. serialize, checking for exceptions
// 2. free the original data, checking for exceptions or the data getting set from destructors or error handlers.
// 3. set the new data.
try {
    var_dump($phar['a']->setMetadata(new ThrowsOnSerialize()));
} catch (RuntimeException $e) {
    echo "Caught {$e->getMessage()} at {$e->getFile()}:{$e->getLine()}\n";
    unset($e);
}
var_dump($phar['a']->getMetadata([]));
var_dump($phar['a']->getMetadata(['allowed_classes' => false]));
$fusion = $e;
$v1=$definedVars[array_rand($definedVars = get_defined_vars())];
$now = new DateTimeImmutable();
$simpleInterval = new DateInterval("P2D");
$date = (new ReflectionClass(DateTime::class))->newInstanceWithoutConstructor();
try {
	new DatePeriod($date, new DateInterval('P1D'), 2);
} catch (Error $e) {
	echo get_class($e), ': ', $e->getMessage(), "\n";
}
$date = (new ReflectionClass(DateTime::class))->newInstanceWithoutConstructor();
try {
	new DatePeriod($now, new DateInterval('P1D'), $date);
} catch (Error $e) {
	echo get_class($e), ': ', $e->getMessage(), "\n";
}
$date = (new ReflectionClass(DateTime::class))->newInstanceWithoutConstructor();
$dateperiod = (new ReflectionClass(DatePeriod::class))->newInstanceWithoutConstructor();
$dateinterval = (new ReflectionClass(DateInterval::class))->newInstanceWithoutConstructor();
try {
	$dateperiod->__unserialize(['start' => $date]);
} catch (Error $e) {
	echo get_class($e), ': ', $e->getMessage(), "\n";
}
try {
	$dateperiod->__unserialize(['start' => $now, 'end' => $date]);
} catch (Error $e) {
	echo get_class($e), ': ', $e->getMessage(), "\n";
}
try {
	$dateperiod->__unserialize(['start' => $now, 'end' => $now, 'current' => $date]);
} catch (Error $e) {
	echo get_class($e), ': ', $e->getMessage(), "\n";
}
try {
	$dateperiod->__unserialize(['start' => $now, 'end' => $now, 'current' => $now, 'interval' => $dateinterval]);
} catch (Error $e) {
	echo get_class($fusion), ': ', $e->getMessage(), "\n";
}
try {
	$dateperiod->__unserialize([
		'start' => $now, 'end' => $now, 'current' => $now, 'interval' => $simpleInterval,
		'recurrences' => 2, 'include_start_date' => true, 'include_end_date' => true,
	]);
	echo "DatePeriod::__unserialize: SUCCESS\n";
} catch (Error $e) {
	echo get_class($e), ': ', $e->getMessage(), "\n";
}
echo "OK\n";
$v2=$definedVars[array_rand($definedVars = get_defined_vars())];
$v3=$definedVars[array_rand($definedVars = get_defined_vars())];
var_dump('random_var:',$v1,$v2,$v3);
var_fusion($v1,$v2,$v3);
?>
