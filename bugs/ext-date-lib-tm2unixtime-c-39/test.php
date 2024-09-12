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
    
$tang['KSI']=-9.1751656444142E-5;
$tang['ETA']=8.5076090069491E-5;
$sol['X']['k']=-222.45470924306;
$sol['X']['e']=-8.1787760034414;
$sol['X'][1]=-0.020231298698539;
$sol['Y']['k']=-14.400586941152;
$sol['Y']['e']=392.95090925357;
$sol['Y'][1]=-0.035664413413272;
$sol['xc']=968;
$sol['yc']=548;
for( $p=0; $p<3; $p++ )
{
	print($p.': ');
	Tangential2XY($tang,$sol);
}
function Tangential2XY(array $tang, array $sol) : array
{
	$x = $sol['X']['k']*$tang['KSI'] + $sol['X']['e']*$tang['ETA'] + $sol['X'][1];
	$y = $sol['Y']['k']*$tang['KSI'] + $sol['Y']['e']*$tang['ETA'] + $sol['Y'][1];
	printf("In;%.12f;%.12f;%.12f;%.12f;",$x,$y,$sol['xc'],$sol['yc']);
	$x = $sol['xc']*($x+1);
	$y = $sol['yc']*($y+1);
	printf("Out;%.12f;%.12f\n",$x,$y);
	if( $x<100 )
		exit("Mamy to!\n");
	return ['x'=>$x,'y'=>$y];
}
$script1_dataflow = $y;
$script1_connect=$x;
$dom = new DOMDocument('1.0', 'utf-8');
$script1_dataflow = $dom->createElement('test', 'root');
$dom->appendChild($element);
$element->setAttribute("id", 123);
$element->setIdAttribute("id", true);
$node = $element->getAttributeNode("id");
var_dump($node->isId());
$element->setIdAttribute("id", true);
var_dump($node->isId());
$script2_connect=$script1_dataflow;
$random_var=$GLOBALS[array_rand($GLOBALS)];
var_dump('random_var:',$random_var);
var_fusion($script1_connect, $script2_connect, $random_var);
?>
