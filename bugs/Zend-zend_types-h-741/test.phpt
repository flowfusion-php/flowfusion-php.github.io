--TEST--
Test output_add_rewrite_var() with and without nested session URL-Rewriting+Bug #79821 (array grow during var_dump)
--INI--
session.trans_sid_tags="a=href,area=href,frame=src,form="
url_rewriter.tags="a=href,area=href,frame=src,form="
zend.multibyte=On
sendmail_path={MAIL:{PWD}/mb_send_mail07.eml}
opcache.enable=1
opcache.enable_cli=1
opcache.jit_buffer_size=1024M
opcache.jit=0200
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
    
$testTags = <<<TEST
<a href=""></a>
<a href="./foo.php"></a>
<a href="//php.net/foo.php"></a>
<a href="http://php.net/foo.php"></a>
<a href="bad://php.net/foo.php"></a>
<a href="//www.php.net/foo.php"></a>
<a href="//session-trans-sid.com/foo.php"></a>
<a href="http://session-trans-sid.com/foo.php"></a>
<a href="bad://session-trans-sid.com/foo.php"></a>
<a href="//www.session-trans-sid.com/foo.php"></a>
<a href="//url-rewriter.com/foo.php"></a>
<a href="http://url-rewriter.com/foo.php"></a>
<a href="bad://url-rewriter.com/foo.php"></a>
<a href="//www.url-rewriter.com/foo.php"></a>
<form action="" method="get"> </form>
<form action="./foo.php" method="get"></form>
<form action="//php.net/foo.php" method="get"></form>
<form action="http://php.net/foo.php" method="get"></form>
<form action="bad://php.net/foo.php" method="get"></form>
<form action="//www.php.net/foo.php" method="get"></form>
<form action="//session-trans-sid.com/bar.php" method="get"></form>
<form action="http://session-trans-sid.com/bar.php" method="get"></form>
<form action="bad://session-trans-sid.com/bar.php" method="get"></form>
<form action="//www.session-trans-sid.com/bar.php" method="get"></form>
<form action="//url-rewriter.com/bar.php" method="get"></form>
<form action="http://url-rewriter.com/bar.php" method="get"></form>
<form action="bad://url-rewriter.com/bar.php" method="get"></form>
<form action="//www.url-rewriter.com/bar.php" method="get"></form>
TEST;
ob_start();
ini_set('session.trans_sid_hosts', 'session-trans-sid.com');
ini_set('url_rewriter.hosts', 'url-rewriter.com');
ini_set('session.use_only_cookies', 1);
ini_set('session.use_cookies', 1);
ini_set('session.use_strict_mode', 0);
ini_set('session.use_trans_sid', 0);
output_add_rewrite_var('<name>', '<value>');
echo "URL-Rewriting with output_add_rewrite_var() without transparent session id support\n";
echo $testTags;
ob_flush();
output_reset_rewrite_vars();
ini_set('session.use_only_cookies', 0);
ini_set('session.use_cookies', 0);
ini_set('session.use_strict_mode', 0);
ini_set('session.use_trans_sid', 1);
session_id('testid');
session_start();
output_add_rewrite_var('<NAME>', '<VALUE>');
echo "\nURL-Rewriting with transparent session id support without output_add_rewrite_var()\n";
echo $testTags;
ob_end_flush();
output_add_rewrite_var('<name2>', '<value2>');
echo "\nURL-Rewriting with output_add_rewrite_var() without transparent session id support\n";
echo $testTags;
$fusion = $testTags;
$v1=$definedVars[array_rand($definedVars = get_defined_vars())];
foreach (['var_dump', 'debug_zval_dump', 'var_export'] as $output) {
    $foo = $bar = [];
    for ($i = 0; $i < 3; $fusion++) {
        $foo = [$foo, [&$bar]];
    }
    ob_start(function (string $buffer) use (&$bar) {
        $bar[][] = null;
        return '';
    }, 64);
    $output($foo[0]);
    ob_end_clean();
}
echo "OK\n";
$v2=$definedVars[array_rand($definedVars = get_defined_vars())];
$v3=$definedVars[array_rand($definedVars = get_defined_vars())];;
var_dump('random_var:',$v1,$v2,$v3);
var_fusion($v1,$v2,$v3);
?>
--EXTENSIONS--
session
--EXPECT--
URL-Rewriting with output_add_rewrite_var() without transparent session id support

<a href="?%3Cname%3E=%3Cvalue%3E"></a>
<a href="./foo.php?%3Cname%3E=%3Cvalue%3E"></a>

<a href="//php.net/foo.php"></a>
<a href="http://php.net/foo.php"></a>
<a href="bad://php.net/foo.php"></a>
<a href="//www.php.net/foo.php"></a>

<a href="//session-trans-sid.com/foo.php"></a>
<a href="http://session-trans-sid.com/foo.php"></a>
<a href="bad://session-trans-sid.com/foo.php"></a>
<a href="//www.session-trans-sid.com/foo.php"></a>

<a href="//url-rewriter.com/foo.php?%3Cname%3E=%3Cvalue%3E"></a>
<a href="http://url-rewriter.com/foo.php?%3Cname%3E=%3Cvalue%3E"></a>
<a href="bad://url-rewriter.com/foo.php"></a>
<a href="//www.url-rewriter.com/foo.php"></a>

<form action="" method="get"><input type="hidden" name="&lt;name&gt;" value="&lt;value&gt;" /> </form>
<form action="./foo.php" method="get"><input type="hidden" name="&lt;name&gt;" value="&lt;value&gt;" /></form>

<form action="//php.net/foo.php" method="get"></form>
<form action="http://php.net/foo.php" method="get"></form>
<form action="bad://php.net/foo.php" method="get"></form>
<form action="//www.php.net/foo.php" method="get"></form>

<form action="//session-trans-sid.com/bar.php" method="get"></form>
<form action="http://session-trans-sid.com/bar.php" method="get"></form>
<form action="bad://session-trans-sid.com/bar.php" method="get"></form>
<form action="//www.session-trans-sid.com/bar.php" method="get"></form>

<form action="//url-rewriter.com/bar.php" method="get"><input type="hidden" name="&lt;name&gt;" value="&lt;value&gt;" /></form>
<form action="http://url-rewriter.com/bar.php" method="get"><input type="hidden" name="&lt;name&gt;" value="&lt;value&gt;" /></form>
<form action="bad://url-rewriter.com/bar.php" method="get"></form>
<form action="//www.url-rewriter.com/bar.php" method="get"></form>

URL-Rewriting with transparent session id support without output_add_rewrite_var()

<a href="?PHPSESSID=testid&%3CNAME%3E=%3CVALUE%3E&%3Cname2%3E=%3Cvalue2%3E"></a>
<a href="./foo.php?PHPSESSID=testid&%3CNAME%3E=%3CVALUE%3E&%3Cname2%3E=%3Cvalue2%3E"></a>

<a href="//php.net/foo.php"></a>
<a href="http://php.net/foo.php"></a>
<a href="bad://php.net/foo.php"></a>
<a href="//www.php.net/foo.php"></a>

<a href="//session-trans-sid.com/foo.php?PHPSESSID=testid"></a>
<a href="http://session-trans-sid.com/foo.php?PHPSESSID=testid"></a>
<a href="bad://session-trans-sid.com/foo.php"></a>
<a href="//www.session-trans-sid.com/foo.php"></a>

<a href="//url-rewriter.com/foo.php?%3CNAME%3E=%3CVALUE%3E&%3Cname2%3E=%3Cvalue2%3E"></a>
<a href="http://url-rewriter.com/foo.php?%3CNAME%3E=%3CVALUE%3E&%3Cname2%3E=%3Cvalue2%3E"></a>
<a href="bad://url-rewriter.com/foo.php"></a>
<a href="//www.url-rewriter.com/foo.php"></a>

<form action="" method="get"><input type="hidden" name="&lt;NAME&gt;" value="&lt;VALUE&gt;" /><input type="hidden" name="&lt;name2&gt;" value="&lt;value2&gt;" /><input type="hidden" name="PHPSESSID" value="testid" /> </form>
<form action="./foo.php" method="get"><input type="hidden" name="&lt;NAME&gt;" value="&lt;VALUE&gt;" /><input type="hidden" name="&lt;name2&gt;" value="&lt;value2&gt;" /><input type="hidden" name="PHPSESSID" value="testid" /></form>

<form action="//php.net/foo.php" method="get"></form>
<form action="http://php.net/foo.php" method="get"></form>
<form action="bad://php.net/foo.php" method="get"></form>
<form action="//www.php.net/foo.php" method="get"></form>

<form action="//session-trans-sid.com/bar.php" method="get"><input type="hidden" name="PHPSESSID" value="testid" /></form>
<form action="http://session-trans-sid.com/bar.php" method="get"><input type="hidden" name="PHPSESSID" value="testid" /></form>
<form action="bad://session-trans-sid.com/bar.php" method="get"></form>
<form action="//www.session-trans-sid.com/bar.php" method="get"></form>

<form action="//url-rewriter.com/bar.php" method="get"><input type="hidden" name="&lt;NAME&gt;" value="&lt;VALUE&gt;" /><input type="hidden" name="&lt;name2&gt;" value="&lt;value2&gt;" /></form>
<form action="http://url-rewriter.com/bar.php" method="get"><input type="hidden" name="&lt;NAME&gt;" value="&lt;VALUE&gt;" /><input type="hidden" name="&lt;name2&gt;" value="&lt;value2&gt;" /></form>
<form action="bad://url-rewriter.com/bar.php" method="get"></form>
<form action="//www.url-rewriter.com/bar.php" method="get"></form>

URL-Rewriting with output_add_rewrite_var() without transparent session id support

<a href="?%3CNAME%3E=%3CVALUE%3E&%3Cname2%3E=%3Cvalue2%3E"></a>
<a href="./foo.php?%3CNAME%3E=%3CVALUE%3E&%3Cname2%3E=%3Cvalue2%3E"></a>

<a href="//php.net/foo.php"></a>
<a href="http://php.net/foo.php"></a>
<a href="bad://php.net/foo.php"></a>
<a href="//www.php.net/foo.php"></a>

<a href="//session-trans-sid.com/foo.php"></a>
<a href="http://session-trans-sid.com/foo.php"></a>
<a href="bad://session-trans-sid.com/foo.php"></a>
<a href="//www.session-trans-sid.com/foo.php"></a>

<a href="//url-rewriter.com/foo.php?%3CNAME%3E=%3CVALUE%3E&%3Cname2%3E=%3Cvalue2%3E"></a>
<a href="http://url-rewriter.com/foo.php?%3CNAME%3E=%3CVALUE%3E&%3Cname2%3E=%3Cvalue2%3E"></a>
<a href="bad://url-rewriter.com/foo.php"></a>
<a href="//www.url-rewriter.com/foo.php"></a>

<form action="" method="get"><input type="hidden" name="&lt;NAME&gt;" value="&lt;VALUE&gt;" /><input type="hidden" name="&lt;name2&gt;" value="&lt;value2&gt;" /> </form>
<form action="./foo.php" method="get"><input type="hidden" name="&lt;NAME&gt;" value="&lt;VALUE&gt;" /><input type="hidden" name="&lt;name2&gt;" value="&lt;value2&gt;" /></form>

<form action="//php.net/foo.php" method="get"></form>
<form action="http://php.net/foo.php" method="get"></form>
<form action="bad://php.net/foo.php" method="get"></form>
<form action="//www.php.net/foo.php" method="get"></form>

<form action="//session-trans-sid.com/bar.php" method="get"></form>
<form action="http://session-trans-sid.com/bar.php" method="get"></form>
<form action="bad://session-trans-sid.com/bar.php" method="get"></form>
<form action="//www.session-trans-sid.com/bar.php" method="get"></form>

<form action="//url-rewriter.com/bar.php" method="get"><input type="hidden" name="&lt;NAME&gt;" value="&lt;VALUE&gt;" /><input type="hidden" name="&lt;name2&gt;" value="&lt;value2&gt;" /></form>
<form action="http://url-rewriter.com/bar.php" method="get"><input type="hidden" name="&lt;NAME&gt;" value="&lt;VALUE&gt;" /><input type="hidden" name="&lt;name2&gt;" value="&lt;value2&gt;" /></form>
<form action="bad://url-rewriter.com/bar.php" method="get"></form>
<form action="//www.url-rewriter.com/bar.php" method="get"></form>
OK
