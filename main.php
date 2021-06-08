<form name="Date" method="GET" action="<?=$_SERVER['PHP_SELF']?>">
Начальная дата(В формате дд/мм/гггг):<input type="text" name="date_req1">
Конечная дата(В формате дд/мм/гггг):<input type="text" name="date_req2">
<input type="submit">
</form>
<?php
$date_req1 = isset ($_GET['date_req1']) ? $_GET['date_req1'] : '02/03/2001';
$date_req2 = isset ($_GET['date_req2']) ? $_GET['date_req2'] : '14/03/2001';
$urlID = file_get_contents('http://www.cbr.ru/scripts/XML_val.asp?d=0');
$xmlID = simplexml_load_string ($urlID);
$ID = [];
$i=0;
//print_r ($xmlID);
//Получаем ID всех валют с http://www.cbr.ru/scripts/XML_val.asp?d=0
foreach ($xmlID as $record){
    $ID[$i++]=(string)$record->attributes()->ID;
}
//Пробиваем все ID по запросу http://www.cbr.ru/scripts/XML_dynamic.asp?date_req1='.$date_req1.'&date_req2='.$date_req2.'&VAL_NM_RQ='.$MoneyID
foreach ($ID as $MoneyID){
    $url='http://www.cbr.ru/scripts/XML_dynamic.asp?date_req1='.$date_req1.'&date_req2='.$date_req2.'&VAL_NM_RQ='.$MoneyID;
    $urlVal=file_get_contents($url);
    $xmlVal=simplexml_load_string($urlVal);
    if (isset($xmlVal->Record->Value)){ //отсеиваем валюты где нету записей.
        $sum=0.0;
        $max = 0;
        $min = 1000;
        $k=0;
        foreach ($xmlVal->Record as $rec){
            foreach ($rec->Value as $val){
                $val = str_replace(',', '.', $val);
                $val = (float)$val;
                $name=(string)$xmlVal->attributes()->ID;
                if ($val>$max){
                    $max = $val;
                    $dateMax = $rec->attributes()->Date;
                }    
                if ($val<$min){
                    $min = $val;
                    $dateMin = $rec->attributes()->Date;
                }
                $sum += $val;
                $k++;
            }
        }
        $sum = $sum / $k;
        foreach($xmlID as $record){
            if ((string)$record->attributes()->ID == $name){
                $name = $record->Name;
            }
        }
        echo 'Название валюты:'.$name.'. Средний курс рубля:'.$sum.'. Максимальный курс:'.$max.'Дата максимального курса: '.$dateMax.'Минимальный курс:'.$min.'Дата минимального курса: '.$dateMin;
        echo '<br>';
    }
}
?>