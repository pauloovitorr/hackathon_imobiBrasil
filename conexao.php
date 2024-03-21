<?php

$host = '149.56.31.239';
$usuario = 'comtest_pauloovitorr';
$senha = 'csExVgO-wN9x';
$bd = 'comtest_hackthon';

$conn = new mysqli($host,$usuario,$senha,$bd);

if($conn->connect_error){
    echo 'Erro:'. $conn->errno;
}
else{
    echo 'Deu bom';
}


?>