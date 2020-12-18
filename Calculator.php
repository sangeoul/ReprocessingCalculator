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
    public $ore,$price,$mineral=array(0,0,0,0,0,0,0);

}

$UNIT_PRICE=100000.0;
$UNIT_RATE=0.05;

if(isset($_GET["tritanium"])){
    $_GET["M1"]=$_GET["tritanium"];
}
if(isset($_GET["pyerite"])){
    $_GET["M2"]=$_GET["pyerite"];
}
if(isset($_GET["mexallon"])){
    $_GET["M3"]=$_GET["mexallon"];
}
if(isset($_GET["isogen"])){
    $_GET["M4"]=$_GET["isogen"];
}
if(isset($_GET["nocxium"])){
    $_GET["M5"]=$_GET["nocxium"];
}
if(isset($_GET["zydrine"])){
    $_GET["M6"]=$_GET["zydrine"];
}
if(isset($_GET["megacyte"])){
    $_GET["M7"]=$_GET["megacyte"];
}


if(!isset($_GET["yield"])){
    $_GET["yield"]=50.00;
}
if(!isset($_GET["M1"])){
    $_GET["M1"]=0;
}
if(!isset($_GET["M2"])){
    $_GET["M2"]=0;
}
if(!isset($_GET["M3"])){
    $_GET["M3"]=0;
}
if(!isset($_GET["M4"])){
    $_GET["M4"]=0;
}
if(!isset($_GET["M5"])){
    $_GET["M5"]=0;
}
if(!isset($_GET["M6"])){
    $_GET["M6"]=0;
}
if(!isset($_GET["M7"])){
    $_GET["M7"]=0;
}
$minerals=array(intval($_GET["M1"]),intval($_GET["M2"]),intval($_GET["M3"]),intval($_GET["M4"]),intval($_GET["M5"]),intval($_GET["M6"]),intval($_GET["M7"]));
$mineral_prices=array();
$ore_vectors=array();
$mil_vectors=array();
$unit_num=array();
$num_per_unit=array();

$reprocessed=new OreVector();

for($i=0;$i<7;$i++){
    $qr="select price from Industry_Marketorders where typeid=".($i+34)." and quantity>0 and is_buy_order=1  order by time desc ,price desc limit 1;";
    $pricedata=$dbcon->query($qr)->fetch_array();
    $mineral_prices[$i]=$pricedata[0];
}

$qr="select * from Industry_Oreinfo where compressed=1";
$result=$dbcon->query($qr);

for($i=0,$ri=0;$ri<$result->num_rows;$ri++){
    
    $oredata=$result->fetch_array();
    $qr="select * from Industry_Relation where relation_type=1 and item_from_id=".$oredata["typeid"].";";
    $rep_result=$dbcon->query($qr);
    
    //새로운 광물 벡터 생성
    if($rep_result->num_rows>0){
        $ore_vectors[$i]=new OreVector();
        $ore_vectors[$i]->ore=$oredata["typeid"];
        
        //바이가(최저가)불러오기
        $qr="select price,typeid from Industry_Marketorders where typeid=".$oredata["typeid"]." and quantity>0 and is_buy_order=1 order by time desc ,price desc limit 1;";
        
        $pricedata=$dbcon->query($qr)->fetch_array();
       // echo($qr."\n<br>");
        $ore_vectors[$i]->price=$pricedata[0];
        //광물 벡터 내용 채우기
        for($j=0;$j<$rep_result->num_rows;$j++){
            $repdata=$rep_result->fetch_array();
            $ore_vectors[$i]->mineral[($repdata["item_to_id"]-34)] = $repdata["item_to_quantity"]*(floatval($_GET["yield"])/100);
        }

        //단위가격벡터 만들기. 
        $mil_vectors[$i]= new OreVector();
        $mil_vectors[$i]->ore=$ore_vectors[$i]->ore;
        $mil_vectors[$i]->price=$ore_vectors[$i]->price;
        $mil_vectors[$i]->mineral=$ore_vectors[$i]->mineral;
        for($j=0;$j<7;$j++){
            $mil_vectors[$i]->mineral[$j]=($mil_vectors[$i]->mineral[$j]*$UNIT_PRICE)/$mil_vectors[$i]->price;
        }
        $num_per_unit[$i]=$UNIT_PRICE/$mil_vectors[$i]->price;
        $mil_vectors[$i]->price=$UNIT_PRICE;  
        
        //echo($pricedata[1]." : ".$num_per_unit[$i]." DEBUG\n<br>");
        $i++;   
    }


}

/*
for($i=0;$i<sizeof($ore_vectors);$i++){
    $orename=$dbcon->query("select itemname from Industry_Oreinfo where typeid=".$mil_vectors[$i]->ore.";")->fetch_array();

    for($j=0;$j<7;$j++){
        echo($orename[0]."[".$j."] : ".$mil_vectors[$i]->mineral[$j]." DEBUG\n<br>");
    }
}
*/
//$unit_num 초기화

for($i=0;$i<sizeof($ore_vectors);$i++){
    $unit_num[$i]=0;
}

//일단 필요량을 넘길 때까지 더한다.
while(sum_est(0)>$UNIT_PRICE){
    $ore_num=close_vector(0);
    //echo($ore_num." DEBUG\n<br>");
    
    $unit_est=max(sum_est(0)*$UNIT_RATE,$UNIT_PRICE);
    $calc_rate=($unit_est/$ore_vectors[$ore_num]->price);
    
    $unit_num[$ore_num]+=$calc_rate;
    for($i=0;$i<7;$i++){
        $minerals[$i]-=$ore_vectors[$ore_num]->mineral[$i]*$calc_rate;
	    $reprocessed->mineral[$i]+=$ore_vectors[$ore_num]->mineral[$i]*$calc_rate;
    }

}

while(sum_est(0)>0){
    
    $ore_num=close_vector(0);
    $unit_est=sum_est(0);
    $calc_rate=($unit_est/$ore_vectors[$ore_num]->price);

    if($calc_rate<1){

        $calc_rate=1;
    }
    $unit_num[$ore_num]+=$calc_rate;
    for($i=0;$i<7;$i++){
        $minerals[$i]-=$ore_vectors[$ore_num]->mineral[$i]*$calc_rate;
	    $reprocessed->mineral[$i]+=$ore_vectors[$ore_num]->mineral[$i]*$calc_rate;
    }
}


//과도한 초과분은 다시 뺀다.
$continue=1;
$negative_inner_product=array();
while($continue){

    $continue=0;
    for($i=0;$i<sizeof($mil_vectors);$i++){
        if($unit_num[$i]>=1 && check_boundary($i)){
            
            for($j=0;$j<7;$j++){
                //내적 계산
                $negative_inner_product[$i]-=$minerals[$j]*$mil_vectors[$i]->mineral[$j];      
            }

            $continue=1;
        }
        else{
            $negative_inner_product[$i]=0;
        }

    }
    
    if($continue==1){
        //내적의 최댓값을 찾는다.
        for($i=0,$maxx=0;$i<sizeof($negative_inner_product);$i++){
            if($maxx<$negative_inner_product[$i]){
                $maxx=$negative_inner_product[$i];
                $ore_num=$i;
  
            }
        }

        //비율값 (음수)
        $multiply_rate=array();
        for($i=0;$i<7;$i++){
            //errordebug($ore_num.".".$i.":".$ore_vectors[$ore_num]->mineral[$i]);
            if($ore_vectors[$ore_num]->mineral[$i]>0){
                $multiply_rate[$i]=$minerals[$i]/$ore_vectors[$ore_num]->mineral[$i];  
                
            }
            else{
                //1은 디폴트값.
                $multiply_rate[$i]=1;
            }
        }
        //비율값의 절대값이 작아야 한다 (실제값이 커야 한다) -> 최댓값을 찾음.
        //1은 디폴트값.
        $calc_rate=1;
        for($i=0;$i<7;$i++){
            if($multiply_rate[$i]!=1 && ($calc_rate<$multiply_rate[$i] || $calc_rate==1)){
                $calc_rate=$multiply_rate[$i];
            }
        }
        
        $calc_rate=$calc_rate*$UNIT_RATE;
        
        $unit_num[$ore_num]+=$calc_rate;
        for($i=0;$i<7;$i++){
            $minerals[$i]-=$ore_vectors[$ore_num]->mineral[$i]*$calc_rate;
	        $reprocessed->mineral[$i]+=$ore_vectors[$ore_num]->mineral[$i]*$calc_rate;
        }
    }
    else{
        break;
    }

}

$totalsum=0;
echo("Reprocessing Yield : ".$_GET["yield"]."% <br>");
echo("<table>\n");
for($i=0;$i<sizeof($ore_vectors);$i++){
    if($unit_num[$i]>0){
        echo("<tr>\n");
        $orename=$dbcon->query("select itemname from Industry_Oreinfo where typeid=".$ore_vectors[$i]->ore.";")->fetch_array();
        echo("<td>".$orename[0]."</td>\n<td>".number_format(ceil($unit_num[$i]))."</td>\n<td>".number_format((ceil($unit_num[$i])*$ore_vectors[$i]->price),2)."</td>\n");
        echo("</tr>\n");
        $totalsum+=(ceil($unit_num[$i]))*$ore_vectors[$i]->price;
    }

}
echo("</table>\n<br>");
echo("Total : ".number_format($totalsum,2)." ISK<br><br>");

echo("Total Yield : <br>");
echo("Tritanium : ".number_format(floor($reprocessed->mineral[0]))."<br>");
echo("Pyerite : ".number_format(floor($reprocessed->mineral[1]))."<br>");
echo("Mexallon : ".number_format(floor($reprocessed->mineral[2]))."<br>");
echo("Isogen : ".number_format(floor($reprocessed->mineral[3]))."<br>");
echo("Nocxium : ".number_format(floor($reprocessed->mineral[4]))."<br>");
echo("Zydrine : ".number_format(floor($reprocessed->mineral[5]))."<br>");
echo("Megacyte : ".number_format(floor($reprocessed->mineral[6]))."<br>");
function close_vector($negative){
    
    global $minerals;
    global $mil_vectors;
    $returnnum=0;

    if($negative==0){
        $inner_product=array();
        for($i=0;$i<sizeof($mil_vectors);$i++){
            for($j=0;$j<7;$j++){
                //내적 계산
                
                $inner_product[$i]+=max($minerals[$j],0)*$mil_vectors[$i]->mineral[$j];
                
            }
        }
    }
    else {
        $inner_product=array();
        for($i=0;$i<sizeof($mil_vectors);$i++){
            for($j=0;$j<7;$j++){
                //내적 계산
                
                $inner_product[$i]-=max($minerals[$j],0)*$mil_vectors[$i]->mineral[$j];
                
            }
        }
    }
    //내적의 최댓값을 찾는다.
    for($i=0,$maxx=0;$i<sizeof($inner_product);$i++){
        if($maxx<$inner_product[$i]){
            $maxx=$inner_product[$i];
            $returnnum=$i;
        }
    }
    return $returnnum;

}
function check_boundary($ore_num){
    
    global $minerals;
    global $ore_vectors;

    $is_safe=1;
    for($j=0;$j<7 && $is_safe;$j++){
        //광1개를 구매해도 바운더리를 넘는지 체크한다.
        
        $is_safe=$ore_vectors[$ore_num]->mineral[$j]>(-$minerals[$j])?0:1;
        
    }

    return $is_safe;

}
function sum_est($negative){
    global $minerals;
    global $mineral_prices;
    $returnvalue=0;
    if($negative==0){
        for($i=0;$i<7;$i++){
            $returnvalue+=max($minerals[$i],0)*$mineral_prices[$i];
        }
    }
    if($negative==1){
        for($i=0;$i<7;$i++){
            $returnvalue+=(-$minerals[$i])*$mineral_prices[$i];
        }      
    }

    return $returnvalue;

}




?>