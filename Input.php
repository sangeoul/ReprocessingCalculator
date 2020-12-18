<html>
<head>
<style>
td{
    border: solid 1px black;
    border-collapse: collapse;
}
tr{
    border-collapse: collapse;   
}
table{
    border-collapse: collapse;
}
</style>
<script data-ad-client="ca-pub-7625490600882004" async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>    
</head>
<?php

include $_SERVER['DOCUMENT_ROOT']."/CorpESI/shrimp/phplib.php";
dbset();



$TRITANIUM=0;
$PYERITE=1;
$MEXALLON=2;
$ISOGEN=3;
$NOCXIUM=4;
$ZYDRINE=5;
$MEGACYTE=6;

$ID_TRITANIUM=34;
$ID_PYERITE=35;
$ID_MEXALLON=36;
$ID_ISOGEN=37;
$ID_NOCXIUM=38;
$ID_ZYDRINE=39;
$ID_MEGACYTE=40;

class OreVector{
    public $ore,$price,$mineral=array();


}

$UNIT_PRICE=1000000.0;
$UNIT_RATE=0.05;


?>
<body>
I need <br>
<form method=get action="./Calculator.php" target="_blank">
<table>
<tr><th>Mnieral </th><th>amount</th></tr>
<tr><td>Tritanium</td><td><input type=number min=0 id="M1" name="M1" style="width=100" value=0></td></tr>
<tr><td>Pyerite</td><td><input type=number min=0 id="M2" name="M2" style="width=100" value=0></td></tr>
<tr><td>Mexallon</td><td><input type=number min=0 id="M3" name="M3" style="width=100" value=0></td></tr>
<tr><td>Isogen</td><td><input type=number min=0 id="M4" name="M4" style="width=100" value=0></td></tr>
<tr><td>Nocxium</td><td><input type=number min=0 id="M5" name="M5" style="width=100" value=0></td></tr>
<tr><td>Zydrine</td><td><input type=number min=0 id="M6" name="M6" style="width=100" value=0></td></tr>
<tr><td>Megacyte</td><td><input type=number min=0 id="M7" name="M7" style="width=100" value=0></td></tr>
<tr><td>Reprocessing Yield(%)</td><td><input type=number min=50 max=100 step=0.1 id="yield" name="yield" style="width=100" value="50.0"></td></tr>
<tr><td colspan=2><input type=submit value=submit></td></tr>
</table>
</form>
</body>
</html>