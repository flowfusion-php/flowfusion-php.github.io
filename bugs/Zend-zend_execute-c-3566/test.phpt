--TEST--
GH-14969: Crash on coercion with throwing __toString()+Bug #55509 (segfault on x86_64 using more than 2G memory)
--INI--
memory_limit=2100M
default_charset=ISO-8859-1
default_charset=cp1251
--SKIPIF--
<?php
if (PHP_INT_SIZE == 4) {
  die('skip Not for 32-bits OS');
}
$zend_mm_enabled = getenv("USE_ZEND_ALLOC");
if ($zend_mm_enabled === "0") {
    die("skip Zend MM disabled");
}
if (getenv("SKIP_SLOW_TESTS")) die("skip slow test");
// check the available memory
if (PHP_OS == 'Linux') {
  $lines = file('/proc/meminfo');
  $infos = array();
  foreach ($lines as $line) {
    $tmp = explode(":", $line);
    $index = strtolower($tmp[0]);
    $value = (int)ltrim($tmp[1], " ")*1024;
    $infos[$index] = $value;
  }
  $freeMemory = $infos['memfree']+$infos['buffers']+$infos['cached'];
  if ($freeMemory < 2100*1024*1024) {
    die('skip Not enough memory.');
  }
}
elseif (PHP_OS == 'FreeBSD') {
  $lines = explode("\n",`sysctl -a`);
  $infos = array();
  foreach ($lines as $line) {
    if (!$line){
      continue;
    }
    $tmp = explode(":", $line);
    if (count($tmp) < 2) {
      continue;
    }
    $index = strtolower($tmp[0]);
    $value = trim($tmp[1], " ");
    $infos[$index] = $value;
  }
  $freeMemory = ($infos['vm.stats.vm.v_inactive_count']*$infos['hw.pagesize'])
                +($infos['vm.stats.vm.v_cache_count']*$infos['hw.pagesize'])
                +($infos['vm.stats.vm.v_free_count']*$infos['hw.pagesize']);
  if ($freeMemory < 2100*1024*1024) {
    die('skip Not enough memory.');
  }
} elseif (PHP_OS == "WINNT") {
  $s = trim(shell_exec("wmic OS get FreeVirtualMemory /Value 2>nul"));
  $freeMemory = explode('=', $s)[1]*1;
  if ($freeMemory < 2.1*1024*1024) {
    die('skip Not enough memory.');
  }
}
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
    
class C {
    public function __toString() {
        global $c;
        $c = [];
        throw new Exception(__METHOD__);
    }
}
class D {
    public string $prop;
}
$c = new C();
$d = new D();
try {
    $d->prop = $c;
} catch (Throwable $e) {
    echo $e->getMessage(), "\n";
}
var_dump($d);
$c = new C();
$d->prop = 'foo';
try {
    $d->prop = $c;
} catch (Throwable $e) {
    echo $e->getMessage(), "\n";
}
var_dump($d);
$fusion = $d;
$v1=$definedVars[array_rand($definedVars = get_defined_vars())];
$a1 = str_repeat("1", 1024 * 1024 * 1024 * 0.5);
echo "1\n";
$a2 = str_repeat("2", 1024 * 1024 * 1024 * 0.5);
echo "2\n";
$a3 = str_repeat("3", 1024 * 1024 * 1024 * 0.5);
echo "3\n";
$a4 = str_repeat("4", 1024 * 1024 * 1024 * 0.5);
echo "4\n";
$fusion = str_repeat("5", 1024 * 1024 * 1024 * 0.5);
echo "5\n";
$v2=$definedVars[array_rand($definedVars = get_defined_vars())];
$v3=$definedVars[array_rand($definedVars = get_defined_vars())];;
var_dump('random_var:',$v1,$v2,$v3);
var_fusion($v1,$v2,$v3);
?>
--EXPECTF--
C::__toString
object(D)#%d (0) {
  ["prop"]=>
  uninitialized(string)
}
C::__toString
object(D)#2 (1) {
  ["prop"]=>
  string(3) "foo"
}
1
2
3
4

Fatal error: Allowed memory size of %d bytes exhausted%s(tried to allocate %d bytes) in %sbug55509.php on line %d
