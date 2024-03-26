<?php 

session_start();

include_once('../conexao.php');

if($_SERVER['REQUEST_METHOD'] === 'GET' ){
    $cod_imovel = $_GET['cod'];
    $sql = "SELECT 
    imoveis.codigo_imovel,
    imoveis.tipo,
    imoveis.finalidade,
	imoveis.cep,
    imoveis.rua,
    imoveis.bairro,
    imoveis.cidade,
    imoveis.estado,
    imoveis.num_casa,
	imoveis.obs,
	imoveis.img,
    clientes_proprietario.nome AS nome_proprietario,
    clientes_proprietario.cpf AS cpf_proprietario,
    clientes_proprietario.email AS email_proprietario,
    corretor.codigo_corretor,
    corretor.creci,
    clientes_corretor.nome AS nome_corretor,
    clientes_corretor.cpf AS cpf_corretor,
    clientes_corretor.email AS email_corretor
FROM 
    imoveis
INNER JOIN 
    clientes AS clientes_proprietario ON imoveis.cod_proprietario = clientes_proprietario.codigo_clientes
INNER JOIN 
    corretor ON imoveis.cod_corretor = corretor.codigo_corretor
INNER JOIN 
    clientes AS clientes_corretor ON corretor.id_cliente_corretor = clientes_corretor.codigo_clientes
	
	WHERE imoveis.codigo_imovel = 2

";

    $results = $connexao->query($sql);

   // $dados = $results->fetch_assoc();

    //print_r($dados);
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

    <div>
        <?php 
            while($dados = $results->fetch_assoc()){
                echo  '<div>';
                echo      '<p>'. "$dados[tipo]" . " para " . "$dados[finalidade] ". "em". " $dados[cidade] ". "/" ." $dados[estado]" . '</p>';
                echo      '<p>'. "$dados[rua]" . " num:" . " $dados[num_casa] ". " $dados[bairro] " . '</p>';
                echo      '<p>'. 'Observação: ' . "$dados[obs]" . '</p>';
                echo      '<p>'. 'Proprietário: ' . "$dados[nome_proprietario]" . '</p>';
                echo      '<p>'. 'CPF: ' . "$dados[cpf_proprietario]" . '</p>';
                echo      '<p>'. 'Email: ' . "$dados[email_proprietario]" . '</p>';
                echo      '<p>'. 'Corretor: ' . "$dados[nome_corretor]" . '</p>';
                echo      '<p>'. 'CPF: ' . "$dados[cpf_corretor]" . '</p>';
                echo      '<p>'. 'Email: ' . "$dados[email_corretor]" . '</p>';
                echo   '</div>';
            }
        ?>
    </div>

</body>
</html>