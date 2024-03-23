<?php 

session_start();

include_once('../conexao.php');

if($_SERVER['REQUEST_METHOD'] === 'GET'){
   $cod_adm = $_SESSION['cod'];
}

if(!empty($_POST)){

    $titulo     = $connexao->escape_string($_POST['check']);
    $cod_adm    = $connexao->escape_string($_POST['cod']);
    $descricao  = $connexao->escape_string($_POST['descri']);

    //$connexao->begin_transaction();

     try{

        $sql = "INSERT INTO checklist (tipo, descricao, codigo_adm ) VALUES ('$titulo', '$descricao', $cod_adm)";
        $dd = $connexao->query($sql);

        if($dd){
            $id = $connexao->insert_id;
            echo $id;
        }

        $connexao->close();

        exit;
     }

     catch(Exception){

     }

    // foreach ($_POST as $chave => $value) {
    //     // Faça o que desejar com cada variável e seu valor
    //     echo "Chave: $chave, Valor: $value <br>";
    // }
}

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../estilos/revisa.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <title>Document</title>
</head>
<body>

    <script>
        $(document).ready(function(){

                    let count = 2

                   
                    $('#add_passo').click(function(e){
                        e.preventDefault();

                        let passo = `<div>
                                        <label for="passo${count}">Título para o passo</label><br>
                                        <input type="text" id="passo${count}" name="passo${count}"></br>

                                        <label for="desc${count}">Descrição do passo</label> </br>
                                        <textarea name="desc0" id="desc${count}" cols="40" rows="7"></textarea></br>

                                    </div>`;

                        let clonedPasso = $(passo).clone(); 
                        $('.passo').append(clonedPasso);
                        
                        count += 1;
                    });
            
        })

    </script>


        

    
        <h1>Criar checklist</h1>

    <div class='cad_check'>
        
        <form method="post">

            <div>
                <label for="check">Tipo de checklist</label> </br>

                <select name="check" id="check">
                    <option value=""></option>
                    <option value="venda">Venda</option>
                    <option value="desistencia">Desistência</option>
                    <option value="destrato">Destrato</option>
                </select>

                <input type="hidden" name='cod' value='<?php echo $cod_adm ?>'>

            </div>

            <div>
                <label for="descri">Descrição</label> </br>
                <textarea name="descri" id="descri" cols="40" rows="5"></textarea>
            </div>

            <h3>Cadastre os passos para o checklist</h3>

            <div class='passo'>

                <div>

                    <label for="passo0">Titulo para o passo</label> </br>
                    <input type="text" id="passo0" name='passo0'> </br>

                    <label for="desc0">Descrição do passo</label> </br>
                    <textarea name="desc0" id="desc0" cols="40" rows="7"></textarea></br>

                </div>

                <div>

                    <label for="passo1">Titulo para o passo</label> </br>
                    <input type="text" id="passo1" name='passo1'> </br>

                    <label for="desc1">Descrição do passo</label> </br>
                    <textarea name="desc1" id="desc1" cols="40" rows="7"></textarea></br>

                </div>

                

            </div>

            <div class="div_btn">
                <button id="add_passo">Adicionar Passo</button>
            </div>

            <div class="div_btn">
                <button type="submit">Adicionar Passo</button>
            </div>


        </form>

    </div>

</body>
</html>