<?php
try {
    sprintf('%2147483648$s, %2$s %1$s', "a", "b");
} catch (ValueError $e) {
    echo $e->getMessage(), "\n";
}
sscanf($e,$e,$e);
?>
