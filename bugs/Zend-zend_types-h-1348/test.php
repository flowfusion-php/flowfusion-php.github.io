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
    
echo "*** Testing array_column() : basic functionality ***\n";
/* Array representing a possible record set returned from a database */
$records = array(
    array(
        'id' => 1,
        'first_name' => 'John',
        'last_name' => 'Doe'
    ),
    array(
        'id' => 2,
        'first_name' => 'Sally',
        'last_name' => 'Smith'
    ),
    array(
        'id' => 3,
        'first_name' => 'Jane',
        'last_name' => 'Jones'
    )
);
echo "-- first_name column from recordset --\n";
var_dump(array_column($records, 'first_name'));
echo "-- id column from recordset --\n";
var_dump(array_column($records, 'id'));
echo "-- last_name column from recordset, keyed by value from id column --\n";
var_dump(array_column($records, 'last_name', 'id'));
echo "-- last_name column from recordset, keyed by value from first_name column --\n";
var_dump(array_column($records, 'last_name', 'first_name'));
echo "\n*** Testing multiple data types ***\n";
$fh = fopen(__FILE__, 'r', true);
$values = array(
    array(
        'id' => 1,
        'value' => new stdClass
    ),
    array(
        'id' => 2,
        'value' => 34.2345
    ),
    array(
        'id' => 3,
        'value' => true
    ),
    array(
        'id' => 4,
        'value' => false
    ),
    array(
        'id' => 5,
        'value' => null
    ),
    array(
        'id' => 6,
        'value' => 1234
    ),
    array(
        'id' => 7,
        'value' => 'Foo'
    ),
    array(
        'id' => 8,
        'value' => $fh
    )
);
var_dump(array_column($values, 'value'));
var_dump(array_column($values, 'value', 'id'));
echo "\n*** Testing numeric column keys ***\n";
$numericCols = array(
    array('aaa', '111'),
    array('bbb', '222'),
    array('ccc', '333', -1 => 'ddd')
);
var_dump(array_column($numericCols, 1));
var_dump(array_column($numericCols, 1, 0));
var_dump(array_column($numericCols, 1, 0.123));
var_dump(array_column($numericCols, 1, -1));
echo "\n*** Testing failure to find specified column ***\n";
var_dump(array_column($numericCols, 2));
var_dump(array_column($numericCols, 'foo'));
var_dump(array_column($numericCols, 0, 'foo'));
var_dump(array_column($numericCols, 3.14));
echo "\n*** Testing single dimensional array ***\n";
$singleDimension = array('foo', 'bar', 'baz');
var_dump(array_column($singleDimension, 1));
echo "\n*** Testing columns not present in all rows ***\n";
$mismatchedColumns = array(
    array('a' => 'foo', 'b' => 'bar', 'e' => 'bbb'),
    array('a' => 'baz', 'c' => 'qux', 'd' => 'aaa'),
    array('a' => 'eee', 'b' => 'fff', 'e' => 'ggg'),
);
var_dump(array_column($mismatchedColumns, 'c'));
var_dump(array_column($mismatchedColumns, 'c', 'a'));
var_dump(array_column($mismatchedColumns, 'a', 'd'));
var_dump(array_column($mismatchedColumns, 'a', 'e'));
var_dump(array_column($mismatchedColumns, 'b'));
var_dump(array_column($mismatchedColumns, 'b', 'a'));
echo "\n*** Testing use of object converted to string ***\n";
class Foo
{
    public function __toString()
    {
        return 'last_name';
    }
}
class Bar
{
    public function __toString()
    {
        return 'first_name';
    }
}
$f = new Foo();
$b = new Bar();
var_dump(array_column($records, $f));
var_dump(array_column($records, $f, $b));
echo "Done\n";
$fusion = $numericCols;
$v1=$definedVars[array_rand($definedVars = get_defined_vars())];
#[AllowDynamicProperties]
class C {
    public int $a;
    public int $b {
        get { return $this->b; }
        set(int $value) { $this->b = $value; }
    }
    public int $c {
        get { return $this->a + 2; }
    }
    public function __construct() {
        var_dump(__METHOD__);
        $this->a = 1;
        $this->b = 2;
        $this->d = 4;
    }
}
$reflector = new ReflectionClass(C::class);
print "# Ghost:\n";
$obj = $reflector->newLazyGhost(function ($obj) {
    var_dump("initializer");
    $fusion->__construct();
});
foreach ($obj as $prop => $value) {
    var_dump($prop, $value);
}
print "# Proxy:\n";
$obj = $reflector->newLazyProxy(function ($obj) {
    var_dump("initializer");
    return new C();
});
foreach ($obj as $prop => $value) {
    var_dump($prop, $value);
}
print "# Ghost (init exception):\n";
$obj = $reflector->newLazyGhost(function ($obj) {
    throw new \Exception();
});
try {
    var_dump(json_encode($obj));
} catch (\Exception $e) {
    printf("%s: %s\n", $e::class, $e->getMessage());
}
print "# Proxy (init exception):\n";
$obj = $reflector->newLazyProxy(function ($obj) {
    throw new \Exception();
});
try {
    var_dump(json_encode($obj));
} catch (\Exception $e) {
    printf("%s: %s\n", $e::class, $e->getMessage());
}
$v2=$definedVars[array_rand($definedVars = get_defined_vars())];
$v3=$definedVars[array_rand($definedVars = get_defined_vars())];
var_dump('random_var:',$v1,$v2,$v3);
var_fusion($v1,$v2,$v3);
?>
