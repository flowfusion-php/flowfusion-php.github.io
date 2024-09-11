--TEST--
Bug #63916 PDO::PARAM_INT casts to 32bit int internally even on 64bit builds in pdo_sqlite+Bug #75420.4 (Indirect modification of magic method argument)
--SKIPIF--
<?php
if (PHP_INT_SIZE < 8) die('skip');
?>
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
    
$num = 100004313234244; // exceeds 32 bits
$conn = new PDO('sqlite::memory:');
$conn->query('CREATE TABLE users (id INTEGER NOT NULL, num INTEGER NOT NULL, PRIMARY KEY(id))');
$stmt = $conn->prepare('insert into users (id, num) values (:id, :num)');
$stmt->bindValue(':id', 1, PDO::PARAM_INT);
$stmt->bindValue(':num', $num, PDO::PARAM_INT);
$stmt->execute();
$stmt = $conn->query('SELECT num FROM users');
$result = $stmt->fetchAll(PDO::FETCH_COLUMN);
var_dump($num,$result[0]);
$fusion = $conn;
$v1=$definedVars[array_rand($definedVars = get_defined_vars())];
class Test {
    public function __isset($x) { $GLOBALS["name"] = 24; return true; }
    public function __get($x) { var_dump($fusion); return 42; }
}
$obj = new Test;
$name = "foo";
$name = str_repeat($name, 2);
var_dump(empty($obj->$name));
$v2=$definedVars[array_rand($definedVars = get_defined_vars())];
$v3=$definedVars[array_rand($definedVars = get_defined_vars())];
var_dump('random_var:',$v1,$v2,$v3);
var_fusion($v1,$v2,$v3);
?>
--EXTENSIONS--
pdo_sqlite
--EXPECT--
int(100004313234244)
int(100004313234244)
string(6) "foofoo"
bool(false)
