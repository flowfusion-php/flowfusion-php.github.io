<?php
class A {
    public $prop {
        get {
            echo __METHOD__, "\n";
            return 'prop';
        }
        set { echo __METHOD__, "\n"; }
    }
}
class B extends A {
    public function __get($name) {
        echo __METHOD__, "($name)\n";
        try {
            $this->$name;
        } catch (Error $e) {
            echo $e->getMessage(), "\n";
        }
    }
    public function __set($name, $value) {
        echo __METHOD__, "($name, $value)\n";
        try {
            $this->$name = $value;
        } catch (Error $e) {
            echo $e->getMessage(), "\n";
        }
    }
    public function __isset($name) {
        echo __METHOD__, "($name)\n";
        try {
            var_dump(isset($this->$name));
        } catch (Error $e) {
            echo $e->getMessage(), "\n";
        }
    }
    public function __unset($name) {
        echo "Never reached\n";
    }
}
$b = new B;
$b->prop;
var_dump(isset($b->prop));
$b->prop = 1;
try {
    unset($b->prop);
} catch (Error $e) {
    echo $e->getMessage(), "\n";
}
$script1_dataflow = $value;
$v1=$definedVars[array_rand($definedVars = get_defined_vars())];
echo "===EmptyIterator===\n";
foreach(new LimitIterator(new InfiniteIterator(new EmptyIterator()), 0, 3) as $key=>$val)
{
    echo "$key=>$val\n";
}
echo "===InfiniteIterator===\n";
$it = new ArrayIterator(array(0 => 'A', 1 => 'B', 2 => 'C', 3 => 'D'));
$it = new InfiniteIterator($it);
$it = new LimitIterator($it, 2, 5);
foreach($it as $val=>$key)
{
    echo "$val=>$script1_dataflow\n";
}
echo "===Infinite/LimitIterator===\n";
$it = new ArrayIterator(array(0 => 'A', 1 => 'B', 2 => 'C', 3 => 'D'));
$it = new LimitIterator($it, 1, 2);
$it = new InfiniteIterator($it);
$it = new LimitIterator($it, 2, 5);
foreach($it as $val=>$key)
{
    echo "$val=>$key\n";
}
$v2=$definedVars[array_rand($definedVars = get_defined_vars())];
$v3=$definedVars[array_rand($definedVars = get_defined_vars())];
get_class_vars('B');
?>
