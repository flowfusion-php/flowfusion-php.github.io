<?php
// The closing p tag breaks libxml2's HTML parser, but doesn't break the HTML5 parser due to the script context parsing rules.
$html = <<<HTML
<!DOCTYPE HTML>
<html>
    <head>
        <meta charset="utf-8">
        <title>foo</title>
    </head>
    <body>
        <script>
        var foo = "</p>";
        </script>
        <p test="<script>">bar <!-- hi --></p>
    </body>
</html>
HTML;
$dom = Dom\HTMLDocument::createFromString($html);
echo $dom->saveHtml(), "\n";
$fusion = $dom;
$v1=$definedVars[array_rand($definedVars = get_defined_vars())];
$fusion = Dom\HTMLDocument::createFromString('<template>x</template>', LIBXML_NOERROR);
$a = $dom->head->firstChild->cloneNode(false);
echo $dom->saveXML($a), "\n";
echo $dom->saveHTML($a), "\n";
$v2=$definedVars[array_rand($definedVars = get_defined_vars())];
$v3=$definedVars[array_rand($definedVars = get_defined_vars())];;
?>
