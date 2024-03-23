<?php 

session_start();

include_once('../conexao.php');

$_SESSION['cod'] = 2;


if($_SERVER['REQUEST_METHOD'] === 'POST' &&  !empty($_POST['buscar'])){

    $buscar = '%' .$connexao->escape_string($_POST['buscar']) . '%' ;

    $sql = "SELECT * FROM imoveis WHERE referencia LIKE ? or rua LIKE ?";


    $acao = $connexao->prepare($sql);

    $acao->bind_param('ss', $buscar,$buscar);

    $acao->execute();

    $result = $acao->get_result();
 

}

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../estilos/revisa.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <title>Revisão Imóveis</title>
</head>
<body>

     <!-- Arquivos jquery -->

    <script>
     
    </script>


     <!-- Arquivos Gerais -->

     <div class="dash">
        <h1>Dashboard</h1>

     </div>

    <div>
        <h1>Gestão de contrato</h1>

        <a href="./check.php">Criar Checklist</a>

        <div class="input_buscar_imovel">

           <form method="post">

                <div class="busca">
                    <label>Selecione o imóvel</label> </br>
                    <input type="text" id="input_movel" name="buscar" placeholder="Código ou logradouro do imóvel">
                    <button type="submit">Buscar</button>
                </div>

                

           </form>

        </div>

        <div class="lista_imoveis" >
           <?php 

                if(isset($result)){
                    if($result->num_rows > 0){
                        while($dados = $result->fetch_assoc()){

                          echo  '<div>';
                          echo      '<p>'. "$dados[tipo]" . " para " . "$dados[finalidade] ". "em". " $dados[cidade]". "$dados[estado]" . '</p>';
                          echo      '<p>'. 'Observação: ' . "$dados[obs]" . '</p>' .  "<a href='../conexao.php?cod={$dados['codigo_imovel']}'> <button>Iniciar contrato de venda</button> </a>";
                          echo   '</div>';

                        }
                }
                else{
                    echo  '<div>';
                    echo      '<p>'. 'Nenhum imóvel encontrado' .'</p>';
                    echo   '</div>';
                }
                }
           
           ?>
        </div>

    </div>

    

   
    
</body>
</html>