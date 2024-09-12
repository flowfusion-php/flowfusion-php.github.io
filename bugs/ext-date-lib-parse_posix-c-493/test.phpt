--TEST--
Attributes can deal with AST nodes.+Test ReflectionProperty::setValue() error cases.
--INI--
opcache.jit=1255
session.cookie_secure=TRUE
opcache.enable=1
opcache.enable_cli=1
opcache.jit_buffer_size=1024M
opcache.jit=1042
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
    
define('V1', strtoupper(php_sapi_name()));
#[A1([V1 => V1])]
class C1
{
    public const BAR = 'bar';
}
$ref = new \ReflectionClass(C1::class);
$attr = $ref->getAttributes();
var_dump(count($attr));
$args = $attr[0]->getArguments();
var_dump(count($args), $args[0][V1] === V1);
echo "\n";
#[A1(V1, 1 + 2, C1::class)]
class C2 { }
$ref = new \ReflectionClass(C2::class);
$attr = $ref->getAttributes();
var_dump(count($attr));
$args = $attr[0]->getArguments();
var_dump(count($args));
var_dump($args[0] === V1);
var_dump($args[1] === 3);
var_dump($args[2] === C1::class);
echo "\n";
#[A1(self::FOO, C1::BAR)]
class C3
{
    private const FOO = 'foo';
}
$ref = new \ReflectionClass(C3::class);
$attr = $ref->getAttributes();
var_dump(count($attr));
$args = $attr[0]->getArguments();
var_dump(count($args));
var_dump($args[0] === 'foo');
var_dump($args[1] === C1::BAR);
echo "\n";
#[ExampleWithShift(4 >> 1)]
class C4 {}
$ref = new \ReflectionClass(C4::class);
var_dump($ref->getAttributes()[0]->getArguments());
echo "\n";
#[Attribute]
class C5
{
    public function __construct() { }
}
$ref = new \ReflectionFunction(#[C5(MissingClass::SOME_CONST)] function () { });
$attr = $ref->getAttributes();
var_dump(count($attr));
try {
    $attr[0]->getArguments();
} catch (\Error $e) {
    var_dump($e->getMessage());
}
try {
    $attr[0]->newInstance();
} catch (\Error $e) {
    var_dump($e->getMessage());
}
$fusion = $attr;
$v1=$definedVars[array_rand($definedVars = get_defined_vars())];
class TestClass {
    public $pub;
    public $pub2 = 5;
    static public $stat = "static property";
    protected $prot = 4;
    private $priv = "keepOut";
}
#[AllowDynamicProperties]
class AnotherClass {
}
$instance = new TestClass();
$instanceWithNoProperties = new AnotherClass();
$propInfo = new ReflectionProperty('TestClass', 'pub2');
echo "\nProtected property:\n";
$propInfo = new ReflectionProperty('TestClass', 'prot');
$propInfo->setValue($instance, "NewValue");
var_dump($propInfo->getValue($instance));
echo "\n\nInstance without property:\n";
$propInfo = new ReflectionProperty('TestClass', 'pub2');
var_dump($propInfo->setValue($instanceWithNoProperties, "NewValue"));
var_dump($fusion->pub2);
$v2=$definedVars[array_rand($definedVars = get_defined_vars())];
$v3=$definedVars[array_rand($definedVars = get_defined_vars())];
var_dump('random_var:',$v1,$v2,$v3);
var_fusion($v1,$v2,$v3);
?>
--EXPECT--
int(1)
int(1)
bool(true)

int(1)
int(3)
bool(true)
bool(true)
bool(true)

int(1)
int(2)
bool(true)
bool(true)

array(1) {
  [0]=>
  int(2)
}

int(1)
string(30) "Class "MissingClass" not found"
string(30) "Class "MissingClass" not found"
Protected property:
string(8) "NewValue"


Instance without property:
NULL
string(8) "NewValue"
