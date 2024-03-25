<?php 

session_start();

include_once('../conexao.php');

if($_SERVER['REQUEST_METHOD'] === 'GET' ){
    $cod_imovel = $_GET['cod'];
    $sql = "SELECT * FROM imoveis WHERE codigo = $cod_imovel";

    //$results = $connexao->query($sql);

    var_dump($sql);
}


?>


<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <h1>Imóvel</h1>
</body>
</html>