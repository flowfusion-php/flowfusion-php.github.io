--TEST--
Bug #69181 (READ_CSV|DROP_NEW_LINE drops newlines within fields)+Phar: PharFileInfo::__construct
--INI--
phar.readonly=0
opcache.preload={PWD}/preload_bug80634.inc
filter.default_flags=
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
    
$filename = __DIR__ . "/bug69181.csv";
$csv = <<<CSV
"foo\n\nbar\nbaz",qux
"foo\nbar\nbaz",qux
CSV;
file_put_contents($filename, $csv);
$file = new SplFileObject($filename);
$file->setFlags(SplFileObject::SKIP_EMPTY | SplFileObject::DROP_NEW_LINE | SplFileObject::READ_CSV);
var_dump(iterator_to_array($file));
echo "\n====\n\n";
$file->rewind();
while (($record = $file->fgetcsv())) {
  var_dump($record);
}
$script1_dataflow = $file;
$v1=$definedVars[array_rand($definedVars = get_defined_vars())];
$fname = __DIR__ . '/' . basename(__FILE__, '.php') . '.phar';
$pname = 'phar://' . $fname;
try {
file_put_contents($fname, 'blah');
$a = new PharFileInfo($pname . '/oops');
} catch (Exception $e) {
echo $e->getMessage() . "\n";
unlink($fname);
}
try {
$a = new PharFileInfo(array());
} catch (TypeError $e) {
echo $e->getMessage() . "\n";
}
$a = new Phar($fname);
$a['a'] = 'hi';
$b = $a['a'];
try {
$a = new PharFileInfo($pname . '/oops/I/do/not/exist');
} catch (Exception $e) {
echo $e->getMessage() . "\n";
}
try {
$script1_dataflow->__construct('oops');
} catch (Exception $e) {
echo $e->getMessage() . "\n";
}
try {
$a = new PharFileInfo(__FILE__);
} catch (Exception $e) {
echo $e->getMessage() . "\n";
}
$v2=$definedVars[array_rand($definedVars = get_defined_vars())];
$v3=$definedVars[array_rand($definedVars = get_defined_vars())];;
var_dump('random_var:',$v1,$v2,$v3);
var_fusion($v1,$v2,$v3);
?>
--EXTENSIONS--
phar
--CLEAN--
<?php
@unlink(__DIR__ . "/bug69181.csv");
?>
<?php unlink(__DIR__ . '/' . basename(__FILE__, '.clean.php') . '.phar'); ?>
--EXPECTF--
array(2) {
  [0]=>
  array(2) {
    [0]=>
    string(12) "foo

bar
baz"
    [1]=>
    string(3) "qux"
  }
  [2]=>
  array(2) {
    [0]=>
    string(11) "foo
bar
baz"
    [1]=>
    string(3) "qux"
  }
}

====

array(2) {
  [0]=>
  string(12) "foo

bar
baz"
  [1]=>
  string(3) "qux"
}
array(2) {
  [0]=>
  string(11) "foo
bar
baz"
  [1]=>
  string(3) "qux"
}
Cannot open phar file 'phar://%spharfileinfo_construct.phar/oops': internal corruption of phar "%spharfileinfo_construct.phar" (truncated entry)
PharFileInfo::__construct(): Argument #1 ($filename) must be of type string, array given
Cannot access phar file entry '%s' in archive '%s'
Cannot call constructor twice
'%s' is not a valid phar archive URL (must have at least phar://filename.phar)
