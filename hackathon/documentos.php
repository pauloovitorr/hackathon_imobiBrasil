<?php 

session_start();

include_once('../conexao.php');

$cod_administrador = $_SESSION['codigo_adm'];

if(empty($_SESSION['codigo_adm'])){
  header('location:index.php');
}

if(empty($_GET['contrato'])){
  header('location:index.php');
}

if($_SERVER['REQUEST_METHOD'] === 'GET'){
    $cod_contrato = $_GET['contrato'];

    $sql = "SELECT 
	  contrato.codigo_contrato,
    contrato.tipo,
    contrato.titulo,
    contrato.referencia,
    contrato.imoveis_codigo,
    imoveis.cidade,
    imoveis.estado,
    imoveis.cod_proprietario,
    imoveis.cod_corretor,
    imoveis.codigo_imovel,
    
    
    proprietario.codigo_clientes AS cod_proprietario,
    proprietario.nome  AS nome_proprietario,
    proprietario.cpf  AS cpf_proprietario,
     
    corretorimovel.codigo_clientes AS cod_corretor,
    corretorimovel.nome AS nome_corretor,
    corretorimovel.cpf AS cpf_corretor,
    corretor.creci,
    corretor.id_cliente_corretor
    
FROM 
    contrato
INNER JOIN
	imoveis ON imoveis.codigo_imovel = contrato.imoveis_codigo
INNER JOIN
	clientes AS proprietario ON imoveis.cod_proprietario = proprietario.codigo_clientes
INNER JOIN
	corretor ON imoveis.cod_corretor = corretor.codigo_corretor
INNER JOIN
	clientes AS corretorimovel ON corretor.id_cliente_corretor = corretorimovel.codigo_clientes
WHERE
	contrato.codigo_contrato = $cod_contrato";


$dados_contrato = $connexao->query($sql);

$dados_contrato = $dados_contrato->fetch_assoc();


$sql2 = "SELECT 
          
          codigo_documento, 
          DATE_FORMAT(dt_criacao,'%d/%m/%Y %H:%i:%s') AS dt_formatada,
          tipo_doc,
          nome,
          path

          FROM documentos 

        WHERE cod_adm = $cod_administrador AND codigo_contrato = $cod_contrato ORDER BY tipo_doc";

$dd = $connexao->query($sql2);




$sql22 = "SELECT * FROM documentos WHERE codigo_contrato = $cod_contrato ";
    $re = $connexao->query($sql22);

    if($re-> num_rows > 0){
      
      $sql3 = "UPDATE contrato SET status_contrato = 'ativo', dt_atualizacao = NOW() ,desc_status = 'Contrato ativo, confirme todos os dados' WHERE codigo_contrato = $cod_contrato ";

      $connexao->query($sql3);
    }
    else{
      $sql33 = "UPDATE contrato SET status_contrato = 'execução', desc_status = 'Contrato pendente de vincular documentos' WHERE codigo_contrato = $cod_contrato ";

      $connexao->query($sql33);
    }


}

// Paulo

if($_SERVER['REQUEST_METHOD'] === 'POST'){

  $count = 1;

  $lista_mover = array();


  $cod_adm = $connexao->escape_string($_SESSION['codigo_adm']);
  $cod_contrato = $connexao->escape_string($_GET['contrato']);
  $pasta = 'documentos/';

  $connexao->begin_transaction();

  try{

    foreach($_FILES as $chaveCampo => $arquivos){

      $nome_original     = $arquivos['name'][0];
      $tmp      = $arquivos['tmp_name'][0];
      $tamanho  = $arquivos['size'][0];

      

      if($count == 1){
        $tipooo = 'Contrato Principal';
        $count += 1;
      }
      elseif($count == 2){
        $tipooo = 'Contrato secundário (intermédio)';
        $count += 1;
    
      }
      elseif($count == 3){
        $tipooo = 'Documentos gerais';
        $count += 1;
      }
      

      if($tamanho > 3845728){

           throw new Exception("Arquivo $nome_original é muito grande !! Máximo 3 MB");  
      }
      else{

        if($nome_original !== ''){

            $novo_nome3 = uniqid() . uniqid();
            $extensao3 = strtolower(pathinfo($nome_original , PATHINFO_EXTENSION));
            $caminho = $pasta.$novo_nome3.'.'. $extensao3;

            $dadosmover = array();

            array_push($dadosmover, [$tmp, $caminho]);

            $resposta = array_push($lista_mover,$dadosmover);
      
            
            if($resposta){
          
              $sql ="INSERT INTO documentos (dt_criacao, tipo_doc , cod_adm, path, nome, codigo_contrato) VALUES (NOW(),'$tipooo' , $cod_adm,'$caminho','$nome_original', $cod_contrato ) ";

              $connexao->query($sql);
            }
            else{
              echo '<p> Deu ruim </p>';
              
              throw new Exception('Uma condição inválida ocorreu.');
            }

        }
    
      }

  }



  $tipooo = '';

  $file3 = $_FILES['documentos_pessoas3'];

  for ($indice = 1; $indice < count($file3['name']); $indice++) {

    $nomeArquivo = $file3['name'][$indice];
    $tmpArquivo = $file3['tmp_name'][$indice];
    $tamanhoArquivo = $file3['size'][$indice];
 
    if($tamanhoArquivo > 3845728){

         throw new Exception("Arquivo $nomeArquivo é muito grande !! Máximo 3 MB");

    }
    else{

      $novo_nome3 = uniqid() . uniqid();
      $extensao3 = strtolower(pathinfo($nomeArquivo , PATHINFO_EXTENSION));
      $caminho3 = $pasta.$novo_nome3.'.'. $extensao3;

      $dadosmover2 = array();

      array_push($dadosmover2, [$tmpArquivo,$caminho3]);

      $resposta = array_push($lista_mover,$dadosmover2);
  
      if($resposta){
    
        $sql2 ="INSERT INTO documentos (dt_criacao, tipo_doc, cod_adm, path, nome, codigo_contrato) VALUES (NOW(), 'Documentos gerais', $cod_adm,'$caminho3','$nomeArquivo', $cod_contrato  ) ";

        $connexao->query($sql2);

      }
      else{
        echo "<p>". 'Deu ruim' ."</p>";
         throw new Exception('Uma condição inválida ocorreu.');
      }
  
    }
}

  

   $res_bb = $connexao->commit();


    if($res_bb){

      for($i=0; $i <count($lista_mover); $i++){
        $temporarios = $lista_mover[$i][0][0];
        $caminho_fixo = $lista_mover[$i][0][1];
    
        move_uploaded_file($temporarios, $caminho_fixo);
      }
    }

    $sql3 = "UPDATE contrato SET status_contrato = 'ativo', desc_status = 'Contrato ativo, confirme todos os dados' WHERE codigo_contrato = $cod_contrato ";

    $connexao->query($sql3);
    
    header('Location: ' . "documentos.php?contrato=$cod_contrato");
  }

  catch( Exception $err){
    $connexao->rollback();
  }




}



?>




<html>
  <head>
    <meta charset="UTF-8" />

    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />

    <title>ImobiBrasil Sites para Imobiliárias e Corretores</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

    <meta
      name="description"
      content="ImobiBrasil - Sites para Imobiliárias e Corretores - www.imobibrasil.com.br"
    />
    <meta
      name="keywords"
      content="ImobiBrasil, Sites para Imobiliárias e Corretores, www.imobibrasil.com.br"
    />
    <meta name="author" content="ImobiBrasil www.imobibrasil.com.br" />

    <meta name="copyright" content="Original content copyright (c);" />
    <meta name="robots" content="noindex,nofollow" />
    <meta http-equiv="pragma" content="no-cache" />
    <meta name="language" content="pt-br" />
    <link rel="stylesheet" href="./styles/hackt.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <link
      rel="shortcut icon"
      href="https://admin01.imobibrasil.net/imobiliarias/imagens/imobibrasil.ico"
    />
    <link
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
      type="text/css"
      rel="stylesheet"
    />
    <link href="./styles/style.css" rel="stylesheet" type="text/css" />
    <link href="./styles/styles.css" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" href="../hackathon/styles/hackt.css" >

    <script language="javascript" src="./scripts/jquery-3.3.1.min.js"></script>

    <link href="./styles/config.css" rel="stylesheet" type="text/css" />

    <link
      href="./styles/jquery.modal.min.css"
      rel="stylesheet"
      type="text/css"
    />


    <style>
      .conteudo {
        max-width: 100%;
      }
      body {
        overflow: auto !important;
      }
      article p {
        margin: 5 auto;
        padding: 0;
        font-size: 12px;
        overflow: hidden;
        color: #6a6c6f;
      }
      h3 {
        color: #6a6c6f;
        margin: 0px;
        font-size: 20px;
      }
      hr {
        border-color: #e4e4e3 !important;
        border-top: none;
      }
      a,
      a:visited,
      a:hover,
      a:active {
        text-decoration: none;
        color: #6a6c6f;
        font-family: ubuntu;
      }

      .caixa-modulos {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        color: #6a6c6f;
        justify-content: flex-start;
        flex-direction: row;
        margin-bottom: 80px;
      }
      .item-modulo {
        border: 1px solid #ced4da;
        border-radius: 2px;
        margin: 10px;
        width: calc(25% - 20px);
        min-width: 225px;
        transition: ease 0.2s;
      }
      .item-modulo:hover {
        border: 1px solid #ccc;
        -webkit-box-shadow: 0px 0px 14px 0px rgba(50, 50, 50, 0.4);
        -moz-box-shadow: 0px 0px 14px 0px rgba(50, 50, 50, 0.4);
        box-shadow: 0px 0px 14px 0px rgba(50, 50, 50, 0.4);
        transition: ease 0.2s;
      }
      .item-conteudo {
        overflow: hidden;
        margin: 0px;
        padding: 15px 10px;
        text-align: center;
        height: 115px;
        position: relative;
        display: flex;
      }
      .item-conteudo .info-botao {
        position: absolute;
        top: 5px;
        right: 5px;
        border-radius: 50%;
        padding: 4px 0px;
        background-color: #ccc;
        height: 20px;
        width: 20px;
        cursor: pointer;
        cursor: hand;
      }
      .modal {
        color: #6a6c6f;
      }
      .modal-titulo {
        font-size: 24px;
        font-family: "ubuntu";
        font-weight: bold;
        margin-bottom: 0px;
      }
      .modal-subtitulo {
        margin-top: 5px;
        font-family: "ubuntu";
        font-size: 14px;
      }
      .modal-bloco-texto {
        font-family: arial;
        line-height: 1.6em;
        margin-top: 25px;
        margin-bottom: 25px;
      }
      .modal-item-lista {
        font-family: arial;
      }
      .modal-botao-comece {
        text-align: center;
        font-size: 13px;
        letter-spacing: 1px;
        font-weight: bold;
        font-family: ubuntu;
        padding: 10px;
        background-color: #5cb85c;
        display: block;
        width: 150px;
        border-radius: 4px;
        color: #fff;
        text-transform: uppercase;
        margin: 0 auto;
        margin-top: 45px;
        margin-bottom: 15px;
        cursor: pointer;
      }
      .texto-modulo {
        margin-top: 0px;
      }
      .texto-modulo p:first-child {
        font-size: 15px;
        font-weight: bolder;
        font-family: ubuntu;
        margin-bottom: 10px;
        margin-top: 2px;
      }
      .texto-modulo p {
        font-size: 12px;
        font-family: ubuntu;
        text-align: left;
      }
      .icone-modulo {
        padding: 10px;
        border-radius: 20px;
        position: relative;
        width: 100%;
        height: 120px;
        pointer-events: none;
      }
      .icone-modulo i {
        color: white;
        font-size: 16px;
      }
      .icone-modulo img {
        max-height: 50px;
        max-width: 50px;
      }
      .fa-check {
        color: #f3a600;
        font-size: 20px;
        margin-right: 15px;
        margin-top: 15px;
      }
      .item-rodape {
        width: 100%;
        overflow: hidden;
        padding: 10;
        background-color: #ebeff2;
        border-top: 1px solid #ced4da;
        font-size: 12px;
      }
      .item-editar {
        float: left;
        width: 60%;
        position: relative;
        margin-left: 5px;
      }
      .item-editar span {
        font-size: 11px;
      }
      .item-acessar {
        float: left;
        width: 35%;
        position: relative;
        text-align: end;
      }
      .item-acessar::before {
        width: 1px;
        background-color: #ced4da;
        height: 15px;
        content: "";
        position: absolute;
        left: 5px;
      }
      .onoffswitch {
        position: relative;
        width: 35px;
        margin-right: 20px;
        float: left;
      }
      .onoffswitch-checkbox {
        display: none;
      }
      .onoffswitch-label {
        display: block;
        overflow: hidden;
        cursor: pointer;
        height: 11px;
        padding: 0;
        line-height: 20px;
        border: 0px solid #ffffff;
        border-radius: 30px;
        background-color: #9e9e9e;
        transition: all 0.4s ease;
      }
      .onoffswitch-label:before {
        content: "";
        display: block;
        width: 16px;
        height: 16px;
        margin: -4px;
        background: #ffffff;
        position: absolute;
        top: 0;
        bottom: 0;
        right: 27px;
        border-radius: 30px;
        box-shadow: 0 6px 12px 0px #757575;
        transition: all 0.4s ease;
      }
      .onoffswitch-label-ativo {
        display: block;
        overflow: hidden;
        cursor: pointer;
        height: 11px;
        padding: 0;
        line-height: 20px;
        border: 0px solid #ffffff;
        border-radius: 30px;
        background-color: #018bbd;
        transition: all 0.4s ease;
      }
      .onoffswitch-label-ativo:before {
        content: "";
        display: block;
        width: 16px;
        height: 16px;
        margin: -4px;
        background: #ffffff;
        position: absolute;
        top: 0;
        bottom: 0;
        right: 0px;
        border-radius: 30px;
        box-shadow: 3px 6px 18px 0px rgba(0, 0, 0, 0.2);
        transition: all 0.4s ease;
      }

      @media screen and (max-width: 559px) {
        .item-modulo {
          width: 100%;
        }
      }
      @media screen and (min-width: 560px) and (max-width: 990px) {
        .item-modulo {
          width: calc(50% - 20px);
        }
      }
      @media screen and (min-width: 991px) and (max-width: 1200px) {
        .item-modulo {
          width: calc(33% - 20px);
        }
      }

      .label-modulo-necessario {
        position: absolute;
        width: 80px;
        right: 35px;
        top: 5px;
        color: #fff;
        background: #2196f3;
        text-align: center;
        border-radius: 5px;
        font-weight: bold;
        font-size: 10px;
        padding: 4px;
        cursor: pointer;
      }
    </style>
    <style>
      .swal2-popup.swal2-toast {
        box-sizing: border-box;
        grid-column: 1/4 !important;
        grid-row: 1/4 !important;
        grid-template-columns: min-content auto min-content;
        padding: 1em;
        overflow-y: hidden;
        background: #fff;
        box-shadow: 0 0 1px rgba(0, 0, 0, 0.075), 0 1px 2px rgba(0, 0, 0, 0.075),
          1px 2px 4px rgba(0, 0, 0, 0.075), 1px 3px 8px rgba(0, 0, 0, 0.075),
          2px 4px 16px rgba(0, 0, 0, 0.075);
        pointer-events: all;
      }
      .swal2-popup.swal2-toast > * {
        grid-column: 2;
      }
      .swal2-popup.swal2-toast .swal2-title {
        margin: 0.5em 1em;
        padding: 0;
        font-size: 1em;
        text-align: initial;
      }
      .swal2-popup.swal2-toast .swal2-loading {
        justify-content: center;
      }
      .swal2-popup.swal2-toast .swal2-input {
        height: 2em;
        margin: 0.5em;
        font-size: 1em;
      }
      .swal2-popup.swal2-toast .swal2-validation-message {
        font-size: 1em;
      }
      .swal2-popup.swal2-toast .swal2-footer {
        margin: 0.5em 0 0;
        padding: 0.5em 0 0;
        font-size: 0.8em;
      }
      .swal2-popup.swal2-toast .swal2-close {
        grid-column: 3/3;
        grid-row: 1/99;
        align-self: center;
        width: 0.8em;
        height: 0.8em;
        margin: 0;
        font-size: 2em;
      }
      .swal2-popup.swal2-toast .swal2-html-container {
        margin: 0.5em 1em;
        padding: 0;
        overflow: initial;
        font-size: 1em;
        text-align: initial;
      }
      .swal2-popup.swal2-toast .swal2-html-container:empty {
        padding: 0;
      }
      .swal2-popup.swal2-toast .swal2-loader {
        grid-column: 1;
        grid-row: 1/99;
        align-self: center;
        width: 2em;
        height: 2em;
        margin: 0.25em;
      }
      .swal2-popup.swal2-toast .swal2-icon {
        grid-column: 1;
        grid-row: 1/99;
        align-self: center;
        width: 2em;
        min-width: 2em;
        height: 2em;
        margin: 0 0.5em 0 0;
      }
      .swal2-popup.swal2-toast .swal2-icon .swal2-icon-content {
        display: flex;
        align-items: center;
        font-size: 1.8em;
        font-weight: bold;
      }
      .swal2-popup.swal2-toast .swal2-icon.swal2-success .swal2-success-ring {
        width: 2em;
        height: 2em;
      }
      .swal2-popup.swal2-toast
        .swal2-icon.swal2-error
        [class^="swal2-x-mark-line"] {
        top: 0.875em;
        width: 1.375em;
      }
      .swal2-popup.swal2-toast
        .swal2-icon.swal2-error
        [class^="swal2-x-mark-line"][class$="left"] {
        left: 0.3125em;
      }
      .swal2-popup.swal2-toast
        .swal2-icon.swal2-error
        [class^="swal2-x-mark-line"][class$="right"] {
        right: 0.3125em;
      }
      .swal2-popup.swal2-toast .swal2-actions {
        justify-content: flex-start;
        height: auto;
        margin: 0;
        margin-top: 0.5em;
        padding: 0 0.5em;
      }
      .swal2-popup.swal2-toast .swal2-styled {
        margin: 0.25em 0.5em;
        padding: 0.4em 0.6em;
        font-size: 1em;
      }
      .swal2-popup.swal2-toast .swal2-success {
        border-color: #a5dc86;
      }
      .swal2-popup.swal2-toast
        .swal2-success
        [class^="swal2-success-circular-line"] {
        position: absolute;
        width: 1.6em;
        height: 3em;
        border-radius: 50%;
      }
      .swal2-popup.swal2-toast
        .swal2-success
        [class^="swal2-success-circular-line"][class$="left"] {
        top: -0.8em;
        left: -0.5em;
        transform: rotate(-45deg);
        transform-origin: 2em 2em;
        border-radius: 4em 0 0 4em;
      }
      .swal2-popup.swal2-toast
        .swal2-success
        [class^="swal2-success-circular-line"][class$="right"] {
        top: -0.25em;
        left: 0.9375em;
        transform-origin: 0 1.5em;
        border-radius: 0 4em 4em 0;
      }
      .swal2-popup.swal2-toast .swal2-success .swal2-success-ring {
        width: 2em;
        height: 2em;
      }
      .swal2-popup.swal2-toast .swal2-success .swal2-success-fix {
        top: 0;
        left: 0.4375em;
        width: 0.4375em;
        height: 2.6875em;
      }
      .swal2-popup.swal2-toast .swal2-success [class^="swal2-success-line"] {
        height: 0.3125em;
      }
      .swal2-popup.swal2-toast
        .swal2-success
        [class^="swal2-success-line"][class$="tip"] {
        top: 1.125em;
        left: 0.1875em;
        width: 0.75em;
      }
      .swal2-popup.swal2-toast
        .swal2-success
        [class^="swal2-success-line"][class$="long"] {
        top: 0.9375em;
        right: 0.1875em;
        width: 1.375em;
      }
      .swal2-popup.swal2-toast
        .swal2-success.swal2-icon-show
        .swal2-success-line-tip {
        animation: swal2-toast-animate-success-line-tip 0.75s;
      }
      .swal2-popup.swal2-toast
        .swal2-success.swal2-icon-show
        .swal2-success-line-long {
        animation: swal2-toast-animate-success-line-long 0.75s;
      }
      .swal2-popup.swal2-toast.swal2-show {
        animation: swal2-toast-show 0.5s;
      }
      .swal2-popup.swal2-toast.swal2-hide {
        animation: swal2-toast-hide 0.1s forwards;
      }
      div:where(.swal2-container) {
        display: grid;
        position: fixed;
        z-index: 1060;
        inset: 0;
        box-sizing: border-box;
        grid-template-areas: "top-start     top            top-end" "center-start  center         center-end" "bottom-start  bottom-center  bottom-end";
        grid-template-rows: minmax(min-content, auto) minmax(min-content, auto) minmax(
            min-content,
            auto
          );
        height: 100%;
        padding: 0.625em;
        overflow-x: hidden;
        transition: background-color 0.1s;
        -webkit-overflow-scrolling: touch;
      }
      div:where(.swal2-container).swal2-backdrop-show,
      div:where(.swal2-container).swal2-noanimation {
        background: rgba(0, 0, 0, 0.4);
      }
      div:where(.swal2-container).swal2-backdrop-hide {
        background: rgba(0, 0, 0, 0) !important;
      }
      div:where(.swal2-container).swal2-top-start,
      div:where(.swal2-container).swal2-center-start,
      div:where(.swal2-container).swal2-bottom-start {
        grid-template-columns: minmax(0, 1fr) auto auto;
      }
      div:where(.swal2-container).swal2-top,
      div:where(.swal2-container).swal2-center,
      div:where(.swal2-container).swal2-bottom {
        grid-template-columns: auto minmax(0, 1fr) auto;
      }
      div:where(.swal2-container).swal2-top-end,
      div:where(.swal2-container).swal2-center-end,
      div:where(.swal2-container).swal2-bottom-end {
        grid-template-columns: auto auto minmax(0, 1fr);
      }
      div:where(.swal2-container).swal2-top-start > .swal2-popup {
        align-self: start;
      }
      div:where(.swal2-container).swal2-top > .swal2-popup {
        grid-column: 2;
        place-self: start center;
      }
      div:where(.swal2-container).swal2-top-end > .swal2-popup,
      div:where(.swal2-container).swal2-top-right > .swal2-popup {
        grid-column: 3;
        place-self: start end;
      }
      div:where(.swal2-container).swal2-center-start > .swal2-popup,
      div:where(.swal2-container).swal2-center-left > .swal2-popup {
        grid-row: 2;
        align-self: center;
      }
      div:where(.swal2-container).swal2-center > .swal2-popup {
        grid-column: 2;
        grid-row: 2;
        place-self: center center;
      }
      div:where(.swal2-container).swal2-center-end > .swal2-popup,
      div:where(.swal2-container).swal2-center-right > .swal2-popup {
        grid-column: 3;
        grid-row: 2;
        place-self: center end;
      }
      div:where(.swal2-container).swal2-bottom-start > .swal2-popup,
      div:where(.swal2-container).swal2-bottom-left > .swal2-popup {
        grid-column: 1;
        grid-row: 3;
        align-self: end;
      }
      div:where(.swal2-container).swal2-bottom > .swal2-popup {
        grid-column: 2;
        grid-row: 3;
        place-self: end center;
      }
      div:where(.swal2-container).swal2-bottom-end > .swal2-popup,
      div:where(.swal2-container).swal2-bottom-right > .swal2-popup {
        grid-column: 3;
        grid-row: 3;
        place-self: end end;
      }
      div:where(.swal2-container).swal2-grow-row > .swal2-popup,
      div:where(.swal2-container).swal2-grow-fullscreen > .swal2-popup {
        grid-column: 1/4;
        width: 100%;
      }
      div:where(.swal2-container).swal2-grow-column > .swal2-popup,
      div:where(.swal2-container).swal2-grow-fullscreen > .swal2-popup {
        grid-row: 1/4;
        align-self: stretch;
      }
      div:where(.swal2-container).swal2-no-transition {
        transition: none !important;
      }
      div:where(.swal2-container) div:where(.swal2-popup) {
        display: none;
        position: relative;
        box-sizing: border-box;
        grid-template-columns: minmax(0, 100%);
        width: 32em;
        max-width: 100%;
        padding: 0 0 1.25em;
        border: none;
        border-radius: 5px;
        background: #fff;
        color: #545454;
        font-family: inherit;
        font-size: 1rem;
      }
      div:where(.swal2-container) div:where(.swal2-popup):focus {
        outline: none;
      }
      div:where(.swal2-container) div:where(.swal2-popup).swal2-loading {
        overflow-y: hidden;
      }
      div:where(.swal2-container) h2:where(.swal2-title) {
        position: relative;
        max-width: 100%;
        margin: 0;
        padding: 0.8em 1em 0;
        color: inherit;
        font-size: 1.875em;
        font-weight: 600;
        text-align: center;
        text-transform: none;
        word-wrap: break-word;
      }
      div:where(.swal2-container) div:where(.swal2-actions) {
        display: flex;
        z-index: 1;
        box-sizing: border-box;
        flex-wrap: wrap;
        align-items: center;
        justify-content: center;
        width: auto;
        margin: 1.25em auto 0;
        padding: 0;
      }
      div:where(.swal2-container)
        div:where(.swal2-actions):not(.swal2-loading)
        .swal2-styled[disabled] {
        opacity: 0.4;
      }
      div:where(.swal2-container)
        div:where(.swal2-actions):not(.swal2-loading)
        .swal2-styled:hover {
        background-image: linear-gradient(
          rgba(0, 0, 0, 0.1),
          rgba(0, 0, 0, 0.1)
        );
      }
      div:where(.swal2-container)
        div:where(.swal2-actions):not(.swal2-loading)
        .swal2-styled:active {
        background-image: linear-gradient(
          rgba(0, 0, 0, 0.2),
          rgba(0, 0, 0, 0.2)
        );
      }
      div:where(.swal2-container) div:where(.swal2-loader) {
        display: none;
        align-items: center;
        justify-content: center;
        width: 2.2em;
        height: 2.2em;
        margin: 0 1.875em;
        animation: swal2-rotate-loading 1.5s linear 0s infinite normal;
        border-width: 0.25em;
        border-style: solid;
        border-radius: 100%;
        border-color: #2778c4 rgba(0, 0, 0, 0) #2778c4 rgba(0, 0, 0, 0);
      }
      div:where(.swal2-container) button:where(.swal2-styled) {
        margin: 0.3125em;
        padding: 0.625em 1.1em;
        transition: box-shadow 0.1s;
        box-shadow: 0 0 0 3px rgba(0, 0, 0, 0);
        font-weight: 500;
      }
      div:where(.swal2-container) button:where(.swal2-styled):not([disabled]) {
        cursor: pointer;
      }
      div:where(.swal2-container) button:where(.swal2-styled).swal2-confirm {
        border: 0;
        border-radius: 0.25em;
        background: initial;
        background-color: #7066e0;
        color: #fff;
        font-size: 1em;
      }
      div:where(.swal2-container)
        button:where(.swal2-styled).swal2-confirm:focus {
        box-shadow: 0 0 0 3px rgba(112, 102, 224, 0.5);
      }
      div:where(.swal2-container) button:where(.swal2-styled).swal2-deny {
        border: 0;
        border-radius: 0.25em;
        background: initial;
        background-color: #dc3741;
        color: #fff;
        font-size: 1em;
      }
      div:where(.swal2-container) button:where(.swal2-styled).swal2-deny:focus {
        box-shadow: 0 0 0 3px rgba(220, 55, 65, 0.5);
      }
      div:where(.swal2-container) button:where(.swal2-styled).swal2-cancel {
        border: 0;
        border-radius: 0.25em;
        background: initial;
        background-color: #6e7881;
        color: #fff;
        font-size: 1em;
      }
      div:where(.swal2-container)
        button:where(.swal2-styled).swal2-cancel:focus {
        box-shadow: 0 0 0 3px rgba(110, 120, 129, 0.5);
      }
      div:where(.swal2-container)
        button:where(.swal2-styled).swal2-default-outline:focus {
        box-shadow: 0 0 0 3px rgba(100, 150, 200, 0.5);
      }
      div:where(.swal2-container) button:where(.swal2-styled):focus {
        outline: none;
      }
      div:where(.swal2-container)
        button:where(.swal2-styled)::-moz-focus-inner {
        border: 0;
      }
      div:where(.swal2-container) div:where(.swal2-footer) {
        margin: 1em 0 0;
        padding: 1em 1em 0;
        border-top: 1px solid #eee;
        color: inherit;
        font-size: 1em;
        text-align: center;
      }
      div:where(.swal2-container) .swal2-timer-progress-bar-container {
        position: absolute;
        right: 0;
        bottom: 0;
        left: 0;
        grid-column: auto !important;
        overflow: hidden;
        border-bottom-right-radius: 5px;
        border-bottom-left-radius: 5px;
      }
      div:where(.swal2-container) div:where(.swal2-timer-progress-bar) {
        width: 100%;
        height: 0.25em;
        background: rgba(0, 0, 0, 0.2);
      }
      div:where(.swal2-container) img:where(.swal2-image) {
        max-width: 100%;
        margin: 2em auto 1em;
      }
      div:where(.swal2-container) button:where(.swal2-close) {
        z-index: 2;
        align-items: center;
        justify-content: center;
        width: 1.2em;
        height: 1.2em;
        margin-top: 0;
        margin-right: 0;
        margin-bottom: -1.2em;
        padding: 0;
        overflow: hidden;
        transition: color 0.1s, box-shadow 0.1s;
        border: none;
        border-radius: 5px;
        background: rgba(0, 0, 0, 0);
        color: #ccc;
        font-family: monospace;
        font-size: 2.5em;
        cursor: pointer;
        justify-self: end;
      }
      div:where(.swal2-container) button:where(.swal2-close):hover {
        transform: none;
        background: rgba(0, 0, 0, 0);
        color: #f27474;
      }
      div:where(.swal2-container) button:where(.swal2-close):focus {
        outline: none;
        box-shadow: inset 0 0 0 3px rgba(100, 150, 200, 0.5);
      }
      div:where(.swal2-container) button:where(.swal2-close)::-moz-focus-inner {
        border: 0;
      }
      div:where(.swal2-container) .swal2-html-container {
        z-index: 1;
        justify-content: center;
        margin: 1em 1.6em 0.3em;
        padding: 0;
        overflow: auto;
        color: inherit;
        font-size: 1.125em;
        font-weight: normal;
        line-height: normal;
        text-align: center;
        word-wrap: break-word;
        word-break: break-word;
      }
      div:where(.swal2-container) input:where(.swal2-input),
      div:where(.swal2-container) input:where(.swal2-file),
      div:where(.swal2-container) textarea:where(.swal2-textarea),
      div:where(.swal2-container) select:where(.swal2-select),
      div:where(.swal2-container) div:where(.swal2-radio),
      div:where(.swal2-container) label:where(.swal2-checkbox) {
        margin: 1em 2em 3px;
      }
      div:where(.swal2-container) input:where(.swal2-input),
      div:where(.swal2-container) input:where(.swal2-file),
      div:where(.swal2-container) textarea:where(.swal2-textarea) {
        box-sizing: border-box;
        width: auto;
        transition: border-color 0.1s, box-shadow 0.1s;
        border: 1px solid #d9d9d9;
        border-radius: 0.1875em;
        background: rgba(0, 0, 0, 0);
        box-shadow: inset 0 1px 1px rgba(0, 0, 0, 0.06),
          0 0 0 3px rgba(0, 0, 0, 0);
        color: inherit;
        font-size: 1.125em;
      }
      div:where(.swal2-container) input:where(.swal2-input).swal2-inputerror,
      div:where(.swal2-container) input:where(.swal2-file).swal2-inputerror,
      div:where(.swal2-container)
        textarea:where(.swal2-textarea).swal2-inputerror {
        border-color: #f27474 !important;
        box-shadow: 0 0 2px #f27474 !important;
      }
      div:where(.swal2-container) input:where(.swal2-input):focus,
      div:where(.swal2-container) input:where(.swal2-file):focus,
      div:where(.swal2-container) textarea:where(.swal2-textarea):focus {
        border: 1px solid #b4dbed;
        outline: none;
        box-shadow: inset 0 1px 1px rgba(0, 0, 0, 0.06),
          0 0 0 3px rgba(100, 150, 200, 0.5);
      }
      div:where(.swal2-container) input:where(.swal2-input)::placeholder,
      div:where(.swal2-container) input:where(.swal2-file)::placeholder,
      div:where(.swal2-container) textarea:where(.swal2-textarea)::placeholder {
        color: #ccc;
      }
      div:where(.swal2-container) .swal2-range {
        margin: 1em 2em 3px;
        background: #fff;
      }
      div:where(.swal2-container) .swal2-range input {
        width: 80%;
      }
      div:where(.swal2-container) .swal2-range output {
        width: 20%;
        color: inherit;
        font-weight: 600;
        text-align: center;
      }
      div:where(.swal2-container) .swal2-range input,
      div:where(.swal2-container) .swal2-range output {
        height: 2.625em;
        padding: 0;
        font-size: 1.125em;
        line-height: 2.625em;
      }
      div:where(.swal2-container) .swal2-input {
        height: 2.625em;
        padding: 0 0.75em;
      }
      div:where(.swal2-container) .swal2-file {
        width: 75%;
        margin-right: auto;
        margin-left: auto;
        background: rgba(0, 0, 0, 0);
        font-size: 1.125em;
      }
      div:where(.swal2-container) .swal2-textarea {
        height: 6.75em;
        padding: 0.75em;
      }
      div:where(.swal2-container) .swal2-select {
        min-width: 50%;
        max-width: 100%;
        padding: 0.375em 0.625em;
        background: rgba(0, 0, 0, 0);
        color: inherit;
        font-size: 1.125em;
      }
      div:where(.swal2-container) .swal2-radio,
      div:where(.swal2-container) .swal2-checkbox {
        align-items: center;
        justify-content: center;
        background: #fff;
        color: inherit;
      }
      div:where(.swal2-container) .swal2-radio label,
      div:where(.swal2-container) .swal2-checkbox label {
        margin: 0 0.6em;
        font-size: 1.125em;
      }
      div:where(.swal2-container) .swal2-radio input,
      div:where(.swal2-container) .swal2-checkbox input {
        flex-shrink: 0;
        margin: 0 0.4em;
      }
      div:where(.swal2-container) label:where(.swal2-input-label) {
        display: flex;
        justify-content: center;
        margin: 1em auto 0;
      }
      div:where(.swal2-container) div:where(.swal2-validation-message) {
        align-items: center;
        justify-content: center;
        margin: 1em 0 0;
        padding: 0.625em;
        overflow: hidden;
        background: #f0f0f0;
        color: #666;
        font-size: 1em;
        font-weight: 300;
      }
      div:where(.swal2-container) div:where(.swal2-validation-message)::before {
        content: "!";
        display: inline-block;
        width: 1.5em;
        min-width: 1.5em;
        height: 1.5em;
        margin: 0 0.625em;
        border-radius: 50%;
        background-color: #f27474;
        color: #fff;
        font-weight: 600;
        line-height: 1.5em;
        text-align: center;
      }
      div:where(.swal2-container) .swal2-progress-steps {
        flex-wrap: wrap;
        align-items: center;
        max-width: 100%;
        margin: 1.25em auto;
        padding: 0;
        background: rgba(0, 0, 0, 0);
        font-weight: 600;
      }
      div:where(.swal2-container) .swal2-progress-steps li {
        display: inline-block;
        position: relative;
      }
      div:where(.swal2-container) .swal2-progress-steps .swal2-progress-step {
        z-index: 20;
        flex-shrink: 0;
        width: 2em;
        height: 2em;
        border-radius: 2em;
        background: #2778c4;
        color: #fff;
        line-height: 2em;
        text-align: center;
      }
      div:where(.swal2-container)
        .swal2-progress-steps
        .swal2-progress-step.swal2-active-progress-step {
        background: #2778c4;
      }
      div:where(.swal2-container)
        .swal2-progress-steps
        .swal2-progress-step.swal2-active-progress-step
        ~ .swal2-progress-step {
        background: #add8e6;
        color: #fff;
      }
      div:where(.swal2-container)
        .swal2-progress-steps
        .swal2-progress-step.swal2-active-progress-step
        ~ .swal2-progress-step-line {
        background: #add8e6;
      }
      div:where(.swal2-container)
        .swal2-progress-steps
        .swal2-progress-step-line {
        z-index: 10;
        flex-shrink: 0;
        width: 2.5em;
        height: 0.4em;
        margin: 0 -1px;
        background: #2778c4;
      }
      div:where(.swal2-icon) {
        position: relative;
        box-sizing: content-box;
        justify-content: center;
        width: 5em;
        height: 5em;
        margin: 2.5em auto 0.6em;
        border: 0.25em solid rgba(0, 0, 0, 0);
        border-radius: 50%;
        border-color: #000;
        font-family: inherit;
        line-height: 5em;
        cursor: default;
        user-select: none;
      }
      div:where(.swal2-icon) .swal2-icon-content {
        display: flex;
        align-items: center;
        font-size: 3.75em;
      }
      div:where(.swal2-icon).swal2-error {
        border-color: #f27474;
        color: #f27474;
      }
      div:where(.swal2-icon).swal2-error .swal2-x-mark {
        position: relative;
        flex-grow: 1;
      }
      div:where(.swal2-icon).swal2-error [class^="swal2-x-mark-line"] {
        display: block;
        position: absolute;
        top: 2.3125em;
        width: 2.9375em;
        height: 0.3125em;
        border-radius: 0.125em;
        background-color: #f27474;
      }
      div:where(.swal2-icon).swal2-error
        [class^="swal2-x-mark-line"][class$="left"] {
        left: 1.0625em;
        transform: rotate(45deg);
      }
      div:where(.swal2-icon).swal2-error
        [class^="swal2-x-mark-line"][class$="right"] {
        right: 1em;
        transform: rotate(-45deg);
      }
      div:where(.swal2-icon).swal2-error.swal2-icon-show {
        animation: swal2-animate-error-icon 0.5s;
      }
      div:where(.swal2-icon).swal2-error.swal2-icon-show .swal2-x-mark {
        animation: swal2-animate-error-x-mark 0.5s;
      }
      div:where(.swal2-icon).swal2-warning {
        border-color: #facea8;
        color: #f8bb86;
      }
      div:where(.swal2-icon).swal2-warning.swal2-icon-show {
        animation: swal2-animate-error-icon 0.5s;
      }
      div:where(.swal2-icon).swal2-warning.swal2-icon-show .swal2-icon-content {
        animation: swal2-animate-i-mark 0.5s;
      }
      div:where(.swal2-icon).swal2-info {
        border-color: #9de0f6;
        color: #3fc3ee;
      }
      div:where(.swal2-icon).swal2-info.swal2-icon-show {
        animation: swal2-animate-error-icon 0.5s;
      }
      div:where(.swal2-icon).swal2-info.swal2-icon-show .swal2-icon-content {
        animation: swal2-animate-i-mark 0.8s;
      }
      div:where(.swal2-icon).swal2-question {
        border-color: #c9dae1;
        color: #87adbd;
      }
      div:where(.swal2-icon).swal2-question.swal2-icon-show {
        animation: swal2-animate-error-icon 0.5s;
      }
      div:where(.swal2-icon).swal2-question.swal2-icon-show
        .swal2-icon-content {
        animation: swal2-animate-question-mark 0.8s;
      }
      div:where(.swal2-icon).swal2-success {
        border-color: #a5dc86;
        color: #a5dc86;
      }
      div:where(.swal2-icon).swal2-success
        [class^="swal2-success-circular-line"] {
        position: absolute;
        width: 3.75em;
        height: 7.5em;
        border-radius: 50%;
      }
      div:where(.swal2-icon).swal2-success
        [class^="swal2-success-circular-line"][class$="left"] {
        top: -0.4375em;
        left: -2.0635em;
        transform: rotate(-45deg);
        transform-origin: 3.75em 3.75em;
        border-radius: 7.5em 0 0 7.5em;
      }
      div:where(.swal2-icon).swal2-success
        [class^="swal2-success-circular-line"][class$="right"] {
        top: -0.6875em;
        left: 1.875em;
        transform: rotate(-45deg);
        transform-origin: 0 3.75em;
        border-radius: 0 7.5em 7.5em 0;
      }
      div:where(.swal2-icon).swal2-success .swal2-success-ring {
        position: absolute;
        z-index: 2;
        top: -0.25em;
        left: -0.25em;
        box-sizing: content-box;
        width: 100%;
        height: 100%;
        border: 0.25em solid rgba(165, 220, 134, 0.3);
        border-radius: 50%;
      }
      div:where(.swal2-icon).swal2-success .swal2-success-fix {
        position: absolute;
        z-index: 1;
        top: 0.5em;
        left: 1.625em;
        width: 0.4375em;
        height: 5.625em;
        transform: rotate(-45deg);
      }
      div:where(.swal2-icon).swal2-success [class^="swal2-success-line"] {
        display: block;
        position: absolute;
        z-index: 2;
        height: 0.3125em;
        border-radius: 0.125em;
        background-color: #a5dc86;
      }
      div:where(.swal2-icon).swal2-success
        [class^="swal2-success-line"][class$="tip"] {
        top: 2.875em;
        left: 0.8125em;
        width: 1.5625em;
        transform: rotate(45deg);
      }
      div:where(.swal2-icon).swal2-success
        [class^="swal2-success-line"][class$="long"] {
        top: 2.375em;
        right: 0.5em;
        width: 2.9375em;
        transform: rotate(-45deg);
      }
      div:where(.swal2-icon).swal2-success.swal2-icon-show
        .swal2-success-line-tip {
        animation: swal2-animate-success-line-tip 0.75s;
      }
      div:where(.swal2-icon).swal2-success.swal2-icon-show
        .swal2-success-line-long {
        animation: swal2-animate-success-line-long 0.75s;
      }
      div:where(.swal2-icon).swal2-success.swal2-icon-show
        .swal2-success-circular-line-right {
        animation: swal2-rotate-success-circular-line 4.25s ease-in;
      }
      [class^="swal2"] {
        -webkit-tap-highlight-color: rgba(0, 0, 0, 0);
      }
      .swal2-show {
        animation: swal2-show 0.3s;
      }
      .swal2-hide {
        animation: swal2-hide 0.15s forwards;
      }
      .swal2-noanimation {
        transition: none;
      }
      .swal2-scrollbar-measure {
        position: absolute;
        top: -9999px;
        width: 50px;
        height: 50px;
        overflow: scroll;
      }
      .swal2-rtl .swal2-close {
        margin-right: initial;
        margin-left: 0;
      }
      .swal2-rtl .swal2-timer-progress-bar {
        right: 0;
        left: auto;
      }
      @keyframes swal2-toast-show {
        0% {
          transform: translateY(-0.625em) rotateZ(2deg);
        }
        33% {
          transform: translateY(0) rotateZ(-2deg);
        }
        66% {
          transform: translateY(0.3125em) rotateZ(2deg);
        }
        100% {
          transform: translateY(0) rotateZ(0deg);
        }
      }
      @keyframes swal2-toast-hide {
        100% {
          transform: rotateZ(1deg);
          opacity: 0;
        }
      }
      @keyframes swal2-toast-animate-success-line-tip {
        0% {
          top: 0.5625em;
          left: 0.0625em;
          width: 0;
        }
        54% {
          top: 0.125em;
          left: 0.125em;
          width: 0;
        }
        70% {
          top: 0.625em;
          left: -0.25em;
          width: 1.625em;
        }
        84% {
          top: 1.0625em;
          left: 0.75em;
          width: 0.5em;
        }
        100% {
          top: 1.125em;
          left: 0.1875em;
          width: 0.75em;
        }
      }
      @keyframes swal2-toast-animate-success-line-long {
        0% {
          top: 1.625em;
          right: 1.375em;
          width: 0;
        }
        65% {
          top: 1.25em;
          right: 0.9375em;
          width: 0;
        }
        84% {
          top: 0.9375em;
          right: 0;
          width: 1.125em;
        }
        100% {
          top: 0.9375em;
          right: 0.1875em;
          width: 1.375em;
        }
      }
      @keyframes swal2-show {
        0% {
          transform: scale(0.7);
        }
        45% {
          transform: scale(1.05);
        }
        80% {
          transform: scale(0.95);
        }
        100% {
          transform: scale(1);
        }
      }
      @keyframes swal2-hide {
        0% {
          transform: scale(1);
          opacity: 1;
        }
        100% {
          transform: scale(0.5);
          opacity: 0;
        }
      }
      @keyframes swal2-animate-success-line-tip {
        0% {
          top: 1.1875em;
          left: 0.0625em;
          width: 0;
        }
        54% {
          top: 1.0625em;
          left: 0.125em;
          width: 0;
        }
        70% {
          top: 2.1875em;
          left: -0.375em;
          width: 3.125em;
        }
        84% {
          top: 3em;
          left: 1.3125em;
          width: 1.0625em;
        }
        100% {
          top: 2.8125em;
          left: 0.8125em;
          width: 1.5625em;
        }
      }
      @keyframes swal2-animate-success-line-long {
        0% {
          top: 3.375em;
          right: 2.875em;
          width: 0;
        }
        65% {
          top: 3.375em;
          right: 2.875em;
          width: 0;
        }
        84% {
          top: 2.1875em;
          right: 0;
          width: 3.4375em;
        }
        100% {
          top: 2.375em;
          right: 0.5em;
          width: 2.9375em;
        }
      }
      @keyframes swal2-rotate-success-circular-line {
        0% {
          transform: rotate(-45deg);
        }
        5% {
          transform: rotate(-45deg);
        }
        12% {
          transform: rotate(-405deg);
        }
        100% {
          transform: rotate(-405deg);
        }
      }
      @keyframes swal2-animate-error-x-mark {
        0% {
          margin-top: 1.625em;
          transform: scale(0.4);
          opacity: 0;
        }
        50% {
          margin-top: 1.625em;
          transform: scale(0.4);
          opacity: 0;
        }
        80% {
          margin-top: -0.375em;
          transform: scale(1.15);
        }
        100% {
          margin-top: 0;
          transform: scale(1);
          opacity: 1;
        }
      }
      @keyframes swal2-animate-error-icon {
        0% {
          transform: rotateX(100deg);
          opacity: 0;
        }
        100% {
          transform: rotateX(0deg);
          opacity: 1;
        }
      }
      @keyframes swal2-rotate-loading {
        0% {
          transform: rotate(0deg);
        }
        100% {
          transform: rotate(360deg);
        }
      }
      @keyframes swal2-animate-question-mark {
        0% {
          transform: rotateY(-360deg);
        }
        100% {
          transform: rotateY(0);
        }
      }
      @keyframes swal2-animate-i-mark {
        0% {
          transform: rotateZ(45deg);
          opacity: 0;
        }
        25% {
          transform: rotateZ(-25deg);
          opacity: 0.4;
        }
        50% {
          transform: rotateZ(15deg);
          opacity: 0.8;
        }
        75% {
          transform: rotateZ(-5deg);
          opacity: 1;
        }
        100% {
          transform: rotateX(0);
          opacity: 1;
        }
      }
      body.swal2-shown:not(.swal2-no-backdrop):not(.swal2-toast-shown) {
        overflow: hidden;
      }
      body.swal2-height-auto {
        height: auto !important;
      }
      body.swal2-no-backdrop .swal2-container {
        background-color: rgba(0, 0, 0, 0) !important;
        pointer-events: none;
      }
      body.swal2-no-backdrop .swal2-container .swal2-popup {
        pointer-events: all;
      }
      body.swal2-no-backdrop .swal2-container .swal2-modal {
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.4);
      }
      @media print {
        body.swal2-shown:not(.swal2-no-backdrop):not(.swal2-toast-shown) {
          overflow-y: scroll !important;
        }
        body.swal2-shown:not(.swal2-no-backdrop):not(.swal2-toast-shown)
          > [aria-hidden="true"] {
          display: none;
        }
        body.swal2-shown:not(.swal2-no-backdrop):not(.swal2-toast-shown)
          .swal2-container {
          position: static !important;
        }
      }
      body.swal2-toast-shown .swal2-container {
        box-sizing: border-box;
        width: 360px;
        max-width: 100%;
        background-color: rgba(0, 0, 0, 0);
        pointer-events: none;
      }
      body.swal2-toast-shown .swal2-container.swal2-top {
        inset: 0 auto auto 50%;
        transform: translateX(-50%);
      }
      body.swal2-toast-shown .swal2-container.swal2-top-end,
      body.swal2-toast-shown .swal2-container.swal2-top-right {
        inset: 0 0 auto auto;
      }
      body.swal2-toast-shown .swal2-container.swal2-top-start,
      body.swal2-toast-shown .swal2-container.swal2-top-left {
        inset: 0 auto auto 0;
      }
      body.swal2-toast-shown .swal2-container.swal2-center-start,
      body.swal2-toast-shown .swal2-container.swal2-center-left {
        inset: 50% auto auto 0;
        transform: translateY(-50%);
      }
      body.swal2-toast-shown .swal2-container.swal2-center {
        inset: 50% auto auto 50%;
        transform: translate(-50%, -50%);
      }
      body.swal2-toast-shown .swal2-container.swal2-center-end,
      body.swal2-toast-shown .swal2-container.swal2-center-right {
        inset: 50% 0 auto auto;
        transform: translateY(-50%);
      }
      body.swal2-toast-shown .swal2-container.swal2-bottom-start,
      body.swal2-toast-shown .swal2-container.swal2-bottom-left {
        inset: auto auto 0 0;
      }
      body.swal2-toast-shown .swal2-container.swal2-bottom {
        inset: auto auto 0 50%;
        transform: translateX(-50%);
      }
      body.swal2-toast-shown .swal2-container.swal2-bottom-end,
      body.swal2-toast-shown .swal2-container.swal2-bottom-right {
        inset: auto 0 0 auto;
      }
    </style>
  </head>
  <body>
    <header>
      <div class="container" id="ct_topo1">
        <div class="logo">
          <a
            href="https://admin01.imobibrasil.net/imobiliarias/inicial.php"
            title="Inicial"
            ><img src="./img/logo.png" alt="imobibrasil"
          /></a>
        </div>

        <div class="menu">
          <div id="cssmenu">
            <div id="menu-button">
              <i class="fa fa-bars" aria-hidden="true"></i>
            </div>

            <ul>
              <li class="active has-sub" id="menuItem_conf">
                <a
                  href="https://admin01.imobibrasil.net/imobiliarias/modulos-listar.php#"
                  ><span>Configurações</span></a
                >
                <ul>
                  <li>
                    <a
                      href="https://admin01.imobibrasil.net/imobiliarias/configuracoes_gerais.php"
                      >Configurações Gerais</a
                    >
                  </li>
                  <li>
                    <a
                      href="https://admin01.imobibrasil.net/imobiliarias/configuracoes_menus_locacaovenda.php"
                      >Busca Rápida</a
                    >
                  </li>
                  <li>
                    <a
                      href="https://admin01.imobibrasil.net/imobiliarias/cadastro_usuarios_listar.php"
                      >Usuários Adicionais</a
                    >
                  </li>
                  <li>
                    <a
                      href="https://admin01.imobibrasil.net/imobiliarias/cadastro_senha.php"
                      >Senha Principal</a
                    >
                  </li>
                  <li>
                    <a href="javascript:void(0)" class="btnAbrirNovidades"
                      >Novidades</a
                    >
                  </li>
                </ul>
              </li>

              <li class="active has-sub" id="menuItem_design">
                <a
                  href="https://admin01.imobibrasil.net/imobiliarias/configuracoes_modelo.php"
                  ><span>Design</span></a
                >
                <ul>
                  <li>
                    <a
                      href="https://admin01.imobibrasil.net/imobiliarias/configuracoes_modelo.php"
                      >Modelos</a
                    >
                  </li>
                  <li>
                    <a
                      href="https://admin01.imobibrasil.net/imobiliarias/configuracoes_gerais_imagens_alt.php?cod=1"
                      >Logotipo</a
                    >
                  </li>
                  <li>
                    <a
                      href="https://admin01.imobibrasil.net/imobiliarias/configuracoes_gerais_imagens.php"
                      >Banners</a
                    >
                  </li>
                  <li>
                    <a
                      href="https://admin01.imobibrasil.net/imobiliarias/configuracoes_gerais_paginas.php"
                      >Páginas</a
                    >
                  </li>
                </ul>
              </li>

              <li class="active has-sub" id="menuItem_CRM">
                <a
                  href="https://admin01.imobibrasil.net/imobiliarias/modulos-listar.php#"
                  ><span>CRM Imóveis</span></a
                >
                <ul>
                  <li>
                    <a
                      href="https://admin01.imobibrasil.net/imobiliarias/imovel_inserir.php#topo"
                      >Imóveis: Incluir</a
                    >
                  </li>
                  <li>
                    <a
                      href="https://admin01.imobibrasil.net/imobiliarias/imovel_listar.php"
                      >Imóveis: Listar</a
                    >
                  </li>

                  <li>
                    <a
                      href="https://admin01.imobibrasil.net/imobiliarias/clientes_listar.php"
                      >Clientes</a
                    >
                  </li>
                  <li>
                    <a
                      href="https://admin01.imobibrasil.net/imobiliarias/imoveis-compativeis.php"
                      >Imóveis Compatíveis</a
                    >
                  </li>
                  <li>
                    <a
                      href="https://admin01.imobibrasil.net/imobiliarias/negocios_listar.php"
                      >Negócios</a
                    >
                  </li>
                  <li>
                    <a
                      href="./dash.php"
                      >Dashboard de vendas</a
                    >
                  </li>
                  <li>
                    <a
                      href="./index.php"
                      >Gestão de contratos</a
                    >
                  </li>
                  <li>
                    <a
                      href="https://admin01.imobibrasil.net/imobiliarias/portais_parceiros_listar.php"
                      >Integrações Portais</a
                    >
                  </li>
                </ul>
              </li>

              <li class="active has-sub" id="menuItem_Modulos">
                <a
                  href="https://admin01.imobibrasil.net/imobiliarias/modulos-listar.php#"
                  ><span>Módulos</span></a
                >
                <ul>
                  <li>
                    <a
                      href="https://admin01.imobibrasil.net/imobiliarias/mod_agendacomp_listar.php"
                      >Agenda</a
                    >
                  </li>
                  <li>
                    <a
                      href="https://admin01.imobibrasil.net/imobiliarias/areaprivada_inicial.php"
                      >Área Privada</a
                    >
                  </li>
                  <li>
                    <a
                      href="https://admin01.imobibrasil.net/imobiliarias/bairros_especial_listar.php"
                      >Bairros que Amamos</a
                    >
                  </li>
                  <li>
                    <a
                      href="https://admin01.imobibrasil.net/imobiliarias/mod_catalogo_imoveis_listar.php"
                      >Catálogo de Imóveis</a
                    >
                  </li>
                  <li>
                    <a
                      href="https://admin01.imobibrasil.net/imobiliarias/chaves_mod_controledechaves.php"
                      >Controle de Chaves</a
                    >
                  </li>
                  <li>
                    <a
                      href="https://admin01.imobibrasil.net/imobiliarias/documentos-gerenciador.php"
                      >Documentos</a
                    >
                  </li>
                  <li>
                    <a
                      href="https://admin01.imobibrasil.net/imobiliarias/api_dwv_configuracoes.php"
                      >DWV</a
                    >
                  </li>
                  <li>
                    <a
                      href="https://admin01.imobibrasil.net/imobiliarias/mod_formularios_inicial.php"
                      >Formulários</a
                    >
                  </li>
                  <li>
                    <a
                      href="https://admin01.imobibrasil.net/imobiliarias/contratos-listar.php"
                      >Gerar Contratos</a
                    >
                  </li>
                  <li>
                    <a
                      href="https://admin01.imobibrasil.net/imobiliarias/atend_listar.php"
                      >Gerenciador de Atendimentos</a
                    >
                  </li>
                  <li>
                    <a
                      href="https://admin01.imobibrasil.net/imobiliarias/hotsite_listar.php"
                      >HotSite</a
                    >
                  </li>
                  <li>
                    <a
                      href="https://admin01.imobibrasil.net/imobiliarias/ferramentas_conversao_listar.php"
                      >Pop-up Conversão</a
                    >
                  </li>
                  <li>
                    <a
                      href="https://admin01.imobibrasil.net/imobiliarias/mod_social-pixel-post.php"
                      >Social Pixel</a
                    >
                  </li>
                  <li>
                    <a
                      href="https://admin01.imobibrasil.net/imobiliarias/mod_imobr-negocios.php"
                      >imoBR Negócios</a
                    >
                  </li>
                  <li>
                    <a
                      href="https://admin01.imobibrasil.net/imobiliarias/orulo_listar_imoveis.php"
                      >Órulo</a
                    >
                  </li>
                  <li>
                    <a
                      href="https://admin01.imobibrasil.net/imobiliarias/mod_whatsapp_inicial.php"
                      >WhatsApp Lead</a
                    >
                  </li>
                  <li>
                    <a
                      href="https://admin01.imobibrasil.net/imobiliarias/modulos-listar.php"
                      class="menu-add-modulo"
                      >Adicionar Módulo</a
                    >
                  </li>
                </ul>
              </li>

              <li class="active has-sub" id="menuItem_Relatorios">
                <a
                  href="https://admin01.imobibrasil.net/imobiliarias/modulos-listar.php#"
                  ><span>Relatórios</span></a
                >
                <ul>
                  <li>
                    <a
                      href="https://admin01.imobibrasil.net/imobiliarias/relatorios-indicadores-listar.php"
                      ><span>Indicadores</span></a
                    >
                  </li>
                  <li>
                    <a
                      href="https://admin01.imobibrasil.net/imobiliarias/relatorios_imoveis.php"
                      ><span>Imóveis</span></a
                    >
                  </li>
                  <li>
                    <a
                      href="https://admin01.imobibrasil.net/imobiliarias/relatorios_clientes.php"
                      ><span>Clientes</span></a
                    >
                  </li>
                </ul>
              </li>

              <li class="active has-sub" id="menuItem_Msg">
                <a
                  href="https://admin01.imobibrasil.net/imobiliarias/caixa-mensagem-listar.php"
                  id="cssmenumsg"
                  ><span>Mensagens</span></a
                >
              </li>
            </ul>
          </div>
        </div>
      </div>
    </header>

    <section class="sub-menu">
      <div class="container" id="ct_topo2">
        <div class="tp-submenu-partemenu">
          <div>
            <ul style="display: flex; align-items: center">
              <li>
                <a
                  href="https://admin01.imobibrasil.net/imobiliarias/inicial.php"
                  target="_self"
                  >Inicial</a
                >
              </li>
              <li>
                <a
                  id="abrir_modal_suporte"
                  href="https://admin01.imobibrasil.net/imobiliarias/modulos-listar.php#"
                  target="_self"
                  >Suporte</a
                >
              </li>
              <li>
                <i
                  class="fa fa-graduation-cap"
                  aria-hidden="true"
                  style="margin-right: 5px"
                ></i
                ><a href="https://ajuda.imobibrasil.com.br/" target="_blank"
                  ><span class="ct2_verSite">Tutoriais de </span>Ajuda</a
                >
              </li>
              <li>
                <i class="fa fa-television" aria-hidden="true"></i> &nbsp;<a
                  href="http://localhost/projeto_financiamento_imobiliario/tela_imovel.php"
                  target="_blank"
                  ><span class="ct2_verSite">Visualizar</span>
                  <span>Site</span></a
                >
              </li>

              <li class="last">
                <i class="fa fa-sign-out" aria-hidden="true"></i> &nbsp;<a
                  href="https://admin01.imobibrasil.net/imobiliarias/identificacao.php?acao=sair"
                  title="Sair"
                  ><span>Sair</span></a
                >
              </li>

              <li class="last busca_aprimorada_li_botao" style="display: block">
                <form action="javascript:" style="margin-bottom: 0px">
                  <div class="search-bar">
                    <input
                      type="search"
                      class="input-search"
                      id="nova_busca_aprimorada_busca_ajax"
                      style="margin-left: -23px"
                      name="search"
                      autocomplete="off"
                      pattern=".*\S.*"
                      required=""
                    />
                    <button
                      class="search-btn"
                      type="submit"
                      style="border: none !important"
                    >
                      <span>Busca</span>
                    </button>
                  </div>
                  <div style="position: relative; width: 100%">
                    <input
                      type="hidden"
                      name="campos_busca_gerados[]"
                      id="campos_busca_gerados"
                      value=""
                    />
                    <div
                      id="paginas-exibicao__linha__input__resultado_topo"
                      style="
                        display: flex;
                        flex-direction: column;
                        position: absolute;
                        background-color: rgb(255, 255, 255);
                        z-index: 999;
                        width: 100%;
                        top: 10px;
                        box-shadow: rgba(0, 0, 0, 0.3) 0px 4px 8px 0px;
                        max-height: 200px;
                        overflow-y: auto;
                      "
                    ></div>
                  </div>
                </form>
              </li>

              <script src="./scripts/inicial_busca.js"></script>
              <link href="./styles/inicial_busca.css" rel="stylesheet" />
              <script>
                document.addEventListener("DOMContentLoaded", function () {
                  var elemento = document.querySelector(
                    ".busca_aprimorada_li_botao"
                  );
                  if (elemento) {
                    elemento.style.display = "block";
                  }
                });
              </script>
            </ul>
          </div>
        </div>

        <div class="tp-submenu-persona">
          <div class="tp-submenu-persona_User">
            <a
              href="https://admin01.imobibrasil.net/imobiliarias/cadastro_senha.php?pag=img"
              class="tp-submenu-persona-Link"
            >
              <div class="tp-submenu-persona_User_Img">
                <img src="./img/20210813114151367.png" border="0" /><img
                  src="./img/20210813114151367.png"
                  id="tp_submenu-persona_User_Img-2"
                />
              </div>
            </a>

            <div class="tp-submenu-persona_User_Text">
              <span>Apresentação ImobiBrasil</span>
              <small>Administrador</small>
            </div>
          </div>
        </div>

        <script src="./scripts/tooltip-balao.js" defer=""></script>
        <style>
          .tp-submenu-partemenu {
            width: calc(100% - 340px);
          }
          .tp-submenu-persona_novidades_icon {
            width: 40px;
            height: 40px;
          }
          @media only screen and (max-width: 719px) {
            .tp-submenu-partemenu {
              width: calc(100% - 270px);
            }
            .tp-submenu-persona {
              width: calc(100% - 143px);
            }
            .tp-submenu-partemenu {
              width: 100%;
            }
            .tp-submenu-notificacao {
              width: calc(100% - 280px) !important;
            }
          }
          .tp-submenu-novidades__box:hover .tp-submenu-bell {
            color: #2ca62f !important;
          }
          .tp-submenu-notificacao__box:hover .tp-submenu-bell {
            color: #2ca62f !important;
          }

          /* barra de busca */
          .input-box {
            position: relative;
            width: 100%;
            max-width: 60px;
            height: 55px;
            margin: 0 50px;
            background-color: #fff;
            border-radius: 6px;
            transition: all 0.5s ease-in-out;
          }
          .input-box.open {
            max-width: 350px;
          }
          .input-box input {
            position: relative;
            width: 100%;
            height: 100%;
            font-size: 16px;
            font-weight: 400;
            color: #333;
            padding: 0 15px;
            border: none;
            border-radius: 6px;
            outline: none;
            transition: all 0.5s ease-in-out;
          }
          .input-box.open input {
            padding: 0 15px 0 65px;
          }

          .input-box .search {
            position: absolute;
            top: 0;
            left: 0;
            width: 60px;
            height: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
            background-color: #fff;
            border-radius: 6px;
            cursor: pointer;
          }
          .input-box.open .search {
            border-radius: 6px 0 0 6px;
          }
          .search .search-icon {
            font-size: 30px;
            color: #1c1c1c;
          }
          .input-box .close-icon {
            position: absolute;
            top: 50%;
            right: -45px;
            font-size: 30px;
            color: #1c1c1c;
            padding: 5px;
            transform: translateY(-50%);
            transition: all 0.5s ease-in-out;
            cursor: pointer;
            pointer-events: none;
            opacity: 0;
          }
          .input-box.open .close-icon {
            transform: translateY(-50%) rotate(180deg);
            pointer-events: auto;
            opacity: 1;
          }

          .container-fechar-search-busca-mobile:active {
            user-select: none;
          }
          .container-fechar-search-busca-mobile {
            user-select: none;
          }
          .icone-fechar-search-busca-mobile:active {
            user-select: none;
          }
          .icone-fechar-search-busca-mobile {
            user-select: none;
          }

          .container-search-busca-mobile:focus-within {
            position: absolute;
            left: 0px;
            width: 100%;
            z-index: 9999999;
            margin-top: 60px;
          }
          .container-background-modal-search-busca-mobile {
            display: none;
          }
          .container-fechar-search-busca-mobile {
            display: none;
            padding: 7px;
          }
          .container-search-busca-mobile:focus-within .search-bar-mobile {
            width: 100% !important;
          }
          .container-search-busca-mobile:focus-within
            + .container-background-modal-search-busca-mobile {
            display: block;
          }
          .container-search-busca-mobile:focus-within
            + .container-fechar-search-busca-mobile {
            display: block;
          }
          .container-search-busca-mobile:focus-within
            + .tp-submenu-novidades__box {
            display: none !important;
          }
          .container-search-busca-mobile:focus-within
            + .tp-submenu-notificacao__box {
            display: none !important;
          }

          .search-wrapper {
            width: 50px;
            height: 50px;
            display: flex;
            background: #ffff;
            align-items: center;
            justify-content: center;
            outline: none;
            border-radius: 0.375em 0.375em 0.375em 0.375em;
            overflow: hidden;
            transition: 400ms ease-in-out;
            position: relative;
            display: none;
          }

          .container-geral-busca {
            display: none;
          }

          .activeSearch {
            width: 350px;
            border: 1px solid #f4c332;
            position: absolute;
            left: 0;
            width: 100%;
            margin-top: 30px;
            z-index: 99999;
          }

          .search-wrapper input {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            margin-left: 10px;
            margin-right: 2px;
            outline: none;
            border: none;
            font-size: 1.2rem;
            box-sizing: border-box;
            padding-left: 10px;
          }
          .search-botao {
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            background: #ffff;
            width: 50px;
            height: 50px;
            cursor: pointer;
            border: none;
            margin-left: auto;
            z-index: 1;
          }
          .search-botao i {
            font-size: 25px;
          }
          .searchActive {
            background: #f4c332;
            position: relative;
            float: right;
            right: -10px;
          }
          .searchActive i {
            color: #fff;
            font-size: 25px;
          }

          .select2-results__group {
            padding: 8px;
          }

          .select2-results__option--selectable-mobile {
            margin: 12px;
            cursor: pointer;
            padding-left: 23px;
            color: #4d4d4d;
            position: relative;
          }
        </style>
        <div class="tp-submenu-notificacao">
          <div style="display: flex; align-items: center; gap: 9px">
            <div class="tp-submenu-novidades__box btnAbrirNovidades">
              <a class="tp-submenu-persona-Link">
                <div class="tp-submenu-persona_novidades_icon tp-bell-pulse">
                  <i class="fa fa-star tp-submenu-bell"></i>
                </div>
              </a>
            </div>

            <div class="tp-submenu-notificacao__box">
              <a class="tp-submenu-persona-Link">
                <div class="tp-submenu-persona_User_Img tp-bell-pulse">
                  <i
                    class="fa-regular fa-bell tp-submenu-bell notificacoes-nao-lidas"
                  ></i>
                  <span class="pulse">1</span>
                </div>
              </a>
            </div>
          </div>
          <div class="tp-central-notificacao-dropdown">
            <div class="tp-central-notificacao-dropdown-heading">
              <h5>
                <span class="badge"></span> Notificações
                <i class="tp-central-notificacao-dropdown-heading-fechar">X</i>
              </h5>
            </div>

            <div class="tp-central-notificacao-dropdown-body">
              <div class="notify-details notify-nao-lido">
                <div
                  data-balao="Imóveis"
                  compatíveis=""
                  class="balao notify-details-icone"
                >
                  <a
                    href="https://admin01.imobibrasil.net/imobiliarias/imoveis-compativeis.php"
                  >
                    <i class="fa fa-home"></i>
                  </a>
                </div>
                <div class="notify-details-corpo">
                  <a
                    href="https://admin01.imobibrasil.net/imobiliarias/imoveis-compativeis.php"
                  >
                    <p style="font-size: 12px">Imóveis Compatíveis</p>
                    <p class="notify-details-corpo_titulo">
                      Resumo do dia: 5 novos Imóveis Compatíveis!
                    </p>
                    <small></small
                    ><small
                      ><p class="notify-details-data">
                        <strong>01/03/2024</strong>
                      </p></small
                    >
                  </a>
                  <span
                    data-codigo="695345"
                    data-balao="Marcar"
                    lido=""
                    class="balao marcar-lido"
                    ><i class="fa fa-circle"></i
                  ></span>
                </div>
              </div>
              <div class="notify-details notify-nao-lido">
                <div data-balao="Agenda" class="balao notify-details-icone">
                  <a
                    href="https://admin01.imobibrasil.net/imobiliarias/mod_agendacomp_listar.php"
                  >
                    <i class="fa fa-calendar"></i>
                  </a>
                </div>
                <div class="notify-details-corpo">
                  <a
                    href="https://admin01.imobibrasil.net/imobiliarias/mod_agendacomp_listar.php"
                  >
                    <p style="font-size: 12px">Agenda</p>
                    <p class="notify-details-corpo_titulo">Atendimento</p>
                    <small></small
                    ><small
                      ><p class="notify-details-data">
                        Compromisso: <strong>27/02/2024 - 10:00</strong>
                      </p></small
                    >
                  </a>
                  <span
                    data-codigo="692243"
                    data-balao="Marcar"
                    lido=""
                    class="balao marcar-lido"
                    ><i class="fa fa-circle"></i
                  ></span>
                </div>
              </div>
              <div class="notify-details notify-nao-lido">
                <div data-balao="Mensagens" class="balao notify-details-icone">
                  <a
                    href="https://admin01.imobibrasil.net/imobiliarias/caixa-mensagem-listar.php"
                  >
                    <i class="fa-solid fa-comments"></i>
                  </a>
                </div>
                <div class="notify-details-corpo">
                  <a
                    href="https://admin01.imobibrasil.net/imobiliarias/caixa-mensagem-listar.php"
                  >
                    <p style="font-size: 12px">Mensagens</p>
                    <p class="notify-details-corpo_titulo">
                      Resumo do dia: você tem uma nova mensagem!
                    </p>
                    <small></small
                    ><small
                      ><p class="notify-details-data">
                        <strong>15/02/2024</strong>
                      </p></small
                    >
                  </a>
                  <span
                    data-codigo="681297"
                    data-balao="Marcar"
                    lido=""
                    class="balao marcar-lido"
                    ><i class="fa fa-circle"></i
                  ></span>
                </div>
              </div>
              <div class="notify-details notify-nao-lido">
                <div data-balao="Mensagens" class="balao notify-details-icone">
                  <a
                    href="https://admin01.imobibrasil.net/imobiliarias/caixa-mensagem-listar.php"
                  >
                    <i class="fa-solid fa-comments"></i>
                  </a>
                </div>
                <div class="notify-details-corpo">
                  <a
                    href="https://admin01.imobibrasil.net/imobiliarias/caixa-mensagem-listar.php"
                  >
                    <p style="font-size: 12px">Mensagens</p>
                    <p class="notify-details-corpo_titulo">
                      Resumo do dia: você tem uma nova mensagem!
                    </p>
                    <small></small
                    ><small
                      ><p class="notify-details-data">
                        <strong>12/02/2024</strong>
                      </p></small
                    >
                  </a>
                  <span
                    data-codigo="678789"
                    data-balao="Marcar"
                    lido=""
                    class="balao marcar-lido"
                    ><i class="fa fa-circle"></i
                  ></span>
                </div>
              </div>
              <div class="notify-details notify-nao-lido">
                <div data-balao="Mensagens" class="balao notify-details-icone">
                  <a
                    href="https://admin01.imobibrasil.net/imobiliarias/caixa-mensagem-listar.php"
                  >
                    <i class="fa-solid fa-comments"></i>
                  </a>
                </div>
                <div class="notify-details-corpo">
                  <a
                    href="https://admin01.imobibrasil.net/imobiliarias/caixa-mensagem-listar.php"
                  >
                    <p style="font-size: 12px">Mensagens</p>
                    <p class="notify-details-corpo_titulo">
                      Resumo do dia: você tem uma nova mensagem!
                    </p>
                    <small></small
                    ><small
                      ><p class="notify-details-data">
                        <strong>08/02/2024</strong>
                      </p></small
                    >
                  </a>
                  <span
                    data-codigo="675864"
                    data-balao="Marcar"
                    lido=""
                    class="balao marcar-lido"
                    ><i class="fa fa-circle"></i
                  ></span>
                </div>
              </div>
              <div class="notify-details notify-nao-lido">
                <div
                  data-balao="Imóveis"
                  Compatíveis=""
                  class="balao notify-details-icone"
                >
                  <a
                    href="https://admin01.imobibrasil.net/imobiliarias/imoveis-compativeis.php"
                  >
                    <i class="fa fa-home"></i>
                  </a>
                </div>
                <div class="notify-details-corpo">
                  <a
                    href="https://admin01.imobibrasil.net/imobiliarias/imoveis-compativeis.php"
                  >
                    <p style="font-size: 12px">Imóveis Compatíveis</p>
                    <p class="notify-details-corpo_titulo">
                      Resumo do dia: um novo imóvel compatível!
                    </p>
                    <small></small
                    ><small
                      ><p class="notify-details-data">
                        <strong>30/01/2024</strong>
                      </p></small
                    >
                  </a>
                  <span
                    data-codigo="667535"
                    data-balao="Marcar"
                    lido=""
                    class="balao marcar-lido"
                    ><i class="fa fa-circle"></i
                  ></span>
                </div>
              </div>
              <div class="notify-details notify-nao-lido">
                <div
                  data-balao="Imóveis"
                  Compatíveis=""
                  class="balao notify-details-icone"
                >
                  <a
                    href="https://admin01.imobibrasil.net/imobiliarias/imoveis-compativeis.php"
                  >
                    <i class="fa fa-home"></i>
                  </a>
                </div>
                <div class="notify-details-corpo">
                  <a
                    href="https://admin01.imobibrasil.net/imobiliarias/imoveis-compativeis.php"
                  >
                    <p style="font-size: 12px">Imóveis Compatíveis</p>
                    <p class="notify-details-corpo_titulo">
                      Resumo do dia: 44 novos Imóveis Compatíveis!
                    </p>
                    <small></small
                    ><small
                      ><p class="notify-details-data">
                        <strong>29/01/2024</strong>
                      </p></small
                    >
                  </a>
                  <span
                    data-codigo="666443"
                    data-balao="Marcar"
                    lido=""
                    class="balao marcar-lido"
                    ><i class="fa fa-circle"></i
                  ></span>
                </div>
              </div>
              <div class="notify-details notify-nao-lido">
                <div
                  data-balao="Imóveis"
                  Compatíveis=""
                  class="balao notify-details-icone"
                >
                  <a
                    href="https://admin01.imobibrasil.net/imobiliarias/imoveis-compativeis.php"
                  >
                    <i class="fa fa-home"></i>
                  </a>
                </div>
                <div class="notify-details-corpo">
                  <a
                    href="https://admin01.imobibrasil.net/imobiliarias/imoveis-compativeis.php"
                  >
                    <p style="font-size: 12px">Imóveis Compatíveis</p>
                    <p class="notify-details-corpo_titulo">
                      Resumo do dia: um novo imóvel compatível!
                    </p>
                    <small></small
                    ><small
                      ><p class="notify-details-data">
                        <strong>26/01/2024</strong>
                      </p></small
                    >
                  </a>
                  <span
                    data-codigo="664085"
                    data-balao="Marcar"
                    lido=""
                    class="balao marcar-lido"
                    ><i class="fa fa-circle"></i
                  ></span>
                </div>
              </div>
              <div class="notify-details notify-nao-lido">
                <div data-balao="Mensagens" class="balao notify-details-icone">
                  <a
                    href="https://admin01.imobibrasil.net/imobiliarias/caixa-mensagem-listar.php"
                  >
                    <i class="fa-solid fa-comments"></i>
                  </a>
                </div>
                <div class="notify-details-corpo">
                  <a
                    href="https://admin01.imobibrasil.net/imobiliarias/caixa-mensagem-listar.php"
                  >
                    <p style="font-size: 12px">Mensagens</p>
                    <p class="notify-details-corpo_titulo">
                      Resumo do dia: você tem 2 novas mensagens!
                    </p>
                    <small></small
                    ><small
                      ><p class="notify-details-data">
                        <strong>26/01/2024</strong>
                      </p></small
                    >
                  </a>
                  <span
                    data-codigo="664084"
                    data-balao="Marcar"
                    lido=""
                    class="balao marcar-lido"
                    ><i class="fa-solid fa-circle"></i
                  ></span>
                </div>
              </div>
              <div class="notify-details notify-nao-lido">
                <div data-balao="Negócios" class="balao notify-details-icone">
                  <a
                    href="https://admin01.imobibrasil.net/imobiliarias/negocios_historico.php"
                  >
                    <i class="fa-solid fa-handshake"></i>
                  </a>
                </div>
                <div class="notify-details-corpo">
                  <a
                    href="https://admin01.imobibrasil.net/imobiliarias/negocios_historico.php"
                  >
                    <p style="font-size: 12px">Negócios</p>
                    <p class="notify-details-corpo_titulo">
                      Negócio de acaritoss2
                    </p>
                    <small></small
                    ><small
                      ><p class="notify-details-data">
                        Previsão: <strong>22/01/2024 </strong>
                      </p></small
                    >
                  </a>
                  <span
                    data-codigo="660866"
                    data-balao="Marcar"
                    lido=""
                    class="balao marcar-lido"
                    ><i class="fa fa-circle"></i
                  ></span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <link
          href="./styles/central-notificacoes-topo.css"
          type="text/css"
          rel="stylesheet"
        />
        <script
          type="text/javascript"
          src="./scripts/sweetalert2@11.js"
          charset="utf-8"
        ></script>

        <style>
          @import url("https://fonts.googleapis.com/css2?family=Ubuntu:wght@400;700&display=swap");
          #conteudo_modal_contatos_suporte {
            display: none;
          }

          .topo-modal-contatos-container-popup {
            width: unset;
          }
          .topo-modal-contatos-container .modal-contatos {
            max-width: 600px;
            margin: 0 auto;
            font-family: "Ubuntu", sans-serif;
            border-radius: 5px;
            overflow: hidden;
            transition: 0.8s;
            background-color: #fff;
          }

          .topo-modal-contatos-container .modal-contatos a {
            text-decoration: none;
          }

          .topo-modal-contatos-container .modal-contatos ul {
            list-style: none;
          }

          .topo-modal-contatos-container .modal-contatos-header {
            background-color: #ebeff2;
            border-top-right-radius: 5px;
            border-bottom-left-radius: 5px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 21.55px 15px;
          }

          .topo-modal-contatos-container .modal-contatos-header h4 {
            font-weight: 800;
            font-family: ubuntu;
            font-size: 22px;
            line-height: 1.1;
            color: #6a6c6f;
            margin: 0px;
          }

          .topo-modal-contatos-container .modal-contatos-header button {
            border: none;
            color: #6a6c6f;
            opacity: 1;
            font-size: 21px;
            font-weight: bold;
            line-height: 1;
            text-shadow: 0 1px 0 #fff;
            position: relative;
            top: 0px;
            cursor: pointer;
          }

          .topo-modal-contatos-container .modal-contatos-body {
            padding: 0px 15px 15px;
            margin-top: 21px;
          }

          .topo-modal-contatos-container .modal-contatos-buttons a {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            height: 50px;
            border-radius: 4px;
            font-weight: bold;
            color: #fff;
            transition: 0.3s ease-in;
            box-shadow: inset 0 -2px 0 0 rgb(0 0 0 / 12%);
          }

          .topo-modal-contatos-container .modal-contatos-buttons a:hover {
            filter: brightness(0.9);
          }

          .topo-modal-contatos-container .modal-contatos-buttons-group {
            margin-top: 21px;
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(190px, 1fr));
            gap: 21px;
          }

          .topo-modal-contatos-container .modal-btn-whatsapp {
            background: #2ca62f;
            border-color: #4cae4c;
          }

          .topo-modal-contatos-container .modal-btn-telegram p {
            max-width: 80%;
          }
          .topo-modal-contatos-container .modal-btn-telegram {
            background: #0088cc;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            height: 50px;
            border-radius: 4px;
            font-weight: bold;
            color: #fff;
            transition: 0.3s ease-in;
            box-shadow: inset 0 -2px 0 0 rgb(0 0 0 / 12%);
            margin-top: 15px;
            max-width: 460px;
            font-size: 12px;
            font-weight: normal;
          }

          .topo-modal-contatos-container .modal-btn-chat {
            background: #f8c300;
            color: #000 !important;
          }

          .topo-modal-contatos-container .modal-btn-centralAjuda {
            background: #2ca62f;
          }

          .topo-modal-contatos-container .modal-btn-suporte {
            background: #007fe2;
          }

          .topo-modal-contatos-container .modal-contatos-video {
            margin-top: 21px;
          }

          .topo-modal-contatos-container .modal-contatos-info {
            margin-top: 15px;
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 0px 15px;
            align-items: center;
          }

          .topo-modal-contatos-container .modal-contatos-info p,
          .modal-contatos-info a {
            display: flex;
            gap: 10px;
            color: #767d82;
            font-size: 13px;
            margin: 5px;
            align-items: center;
          }

          .topo-modal-contatos-container .modal-contatos-atendimento {
            text-align: center;
            font-weight: bold;
            font-size: 13px;
          }

          .topo-modal-contatos-container .modal-contatos-btn-mais {
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
            background-color: #007fe2;
            border: none;
            border-radius: 4px;
            color: #fff;
            font-size: 16px;
            cursor: pointer;
            font-weight: bold;
          }

          .topo-modal-contatos-container .modal-contatos-btn-mais svg {
            transform: initial;
            transition: 0.8s;
          }

          .topo-modal-contatos-container .modal-contatos-btn-mais.active svg {
            transform: rotate(180deg);
            transition: 0.8s;
          }

          .topo-modal-contatos-container .modal-contatos-lista-container {
            display: none;
            justify-content: center;
            border: 1px solid #ccc;
            border-radius: 4px;
            margin-top: 21px;
            transition: 0.8s ease-in;
            transform: translateY(-20px);
          }

          .topo-modal-contatos-container
            .modal-contatos-lista-container.active {
            display: flex;
            animation: fade 0.7s forwards;
            padding: 15px;
          }

          @keyframes fade {
            from {
              opacity: 0;
              transform: translateY(100px);
            }
            to {
              opacity: 1;
              transform: initial;
              transition: opacity 0.5s linear;
            }
          }

          .topo-modal-contatos-container .modal-contatos-lista {
            list-style: none;
            padding: 0px;
            display: flex;
            flex-direction: column;
            gap: 6px;
            font-size: 14px;
          }

          .topo-modal-contatos-container .modal-contatos-lista a {
            color: #767d82;
          }

          .topo-modal-contatos-container .modal-contatos-lista span {
            font-weight: bold;
          }
          .topo-modal-contatos-container-fechar {
            color: #595959;
            font-weight: bold;
            padding: 10px;
          }

          .topo-modal-contatos-container-header {
            align-items: flex-start;
          }
          .swal2-container {
            z-index: 999999;
          }
          @media screen and (max-width: 719px) {
            .topo-modal-contatos-container .modal-contatos {
              margin-top: 0px;
            }
            .topo-modal-contatos-container .modal-contatos-header button {
              font-size: 32px;
            }
            .modal-contatos-info {
              justify-content: center;
            }

            .topo-modal-contatos-container .modal-contatos-header {
              padding: 11px 15px;
            }
          }
          .tp-submenu-User-fa {
            font-size: 30px !important;
            color: #dadada !important;
            line-height: 38px !important;
          }
          .tp-submenu-bell {
            font-size: 24px !important;
            color: #cbcbcb !important;
          }
        </style>

        <div id="conteudo_modal_contatos_suporte">
          <section class="topo-modal-contatos-container">
            <div class="modal-contatos">
              <div class="modal-contatos-body">
                <div class="modal-contatos-buttons">
                  <div class="modal-contatos-buttons-group">
                    <a
                      href="https://admin01.imobibrasil.net/imobiliarias/suporte.php"
                      class="modal-btn-suporte"
                    >
                      <svg
                        width="20"
                        xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 0 576 512"
                      >
                        <path
                          d="M402.6 83.2l90.2 90.2c3.8 3.8 3.8 10 0 13.8L274.4 405.6l-92.8 10.3c-12.4 1.4-22.9-9.1-21.5-21.5l10.3-92.8L388.8 83.2c3.8-3.8 10-3.8 13.8 0zm162-22.9l-48.8-48.8c-15.2-15.2-39.9-15.2-55.2 0l-35.4 35.4c-3.8 3.8-3.8 10 0 13.8l90.2 90.2c3.8 3.8 10 3.8 13.8 0l35.4-35.4c15.2-15.3 15.2-40 0-55.2zM384 346.2V448H64V128h229.8c3.2 0 6.2-1.3 8.5-3.5l40-40c7.6-7.6 2.2-20.5-8.5-20.5H48C21.5 64 0 85.5 0 112v352c0 26.5 21.5 48 48 48h352c26.5 0 48-21.5 48-48V306.2c0-10.7-12.9-16-20.5-8.5l-40 40c-2.2 2.3-3.5 5.3-3.5 8.5z"
                          fill="white"
                        ></path>
                      </svg>
                      Solicitar Suporte
                    </a>

                    <a
                      target="_blank"
                      href="https://web.whatsapp.com/send?phone=5518988227436&amp;text=Ol%C3%A1,%20preciso%20de%20suporte.%20Meu%20site%20%C3%A9:%20www.imobbrasil.com.br"
                      class="modal-btn-whatsapp"
                    >
                      <svg
                        width="21"
                        height="21"
                        viewBox="0 0 21 21"
                        fill="none"
                        xmlns="http://www.w3.org/2000/svg"
                      >
                        <path
                          d="M17.8547 3.05156C15.8906 1.08281 13.275 0 10.4953 0C4.75781 0 0.0890625 4.66875 0.0890625 10.4062C0.0890625 12.2391 0.567188 14.0297 1.47656 15.6094L0 21L5.51719 19.5516C7.03594 20.3813 8.74687 20.8172 10.4906 20.8172H10.4953C16.2281 20.8172 21 16.1484 21 10.4109C21 7.63125 19.8188 5.02031 17.8547 3.05156ZM10.4953 19.0641C8.93906 19.0641 7.41562 18.6469 6.08906 17.8594L5.775 17.6719L2.50313 18.5297L3.375 15.3375L3.16875 15.0094C2.30156 13.6313 1.84688 12.0422 1.84688 10.4062C1.84688 5.63906 5.72812 1.75781 10.5 1.75781C12.8109 1.75781 14.9812 2.65781 16.6125 4.29375C18.2437 5.92969 19.2469 8.1 19.2422 10.4109C19.2422 15.1828 15.2625 19.0641 10.4953 19.0641ZM15.2391 12.5859C14.9813 12.4547 13.7016 11.8266 13.4625 11.7422C13.2234 11.6531 13.05 11.6109 12.8766 11.8734C12.7031 12.1359 12.2063 12.7172 12.0516 12.8953C11.9016 13.0688 11.7469 13.0922 11.4891 12.9609C9.96094 12.1969 8.95781 11.5969 7.95 9.86719C7.68281 9.40781 8.21719 9.44063 8.71406 8.44687C8.79844 8.27344 8.75625 8.12344 8.69062 7.99219C8.625 7.86094 8.10469 6.58125 7.88906 6.06094C7.67813 5.55469 7.4625 5.625 7.30313 5.61563C7.15313 5.60625 6.97969 5.60625 6.80625 5.60625C6.63281 5.60625 6.35156 5.67188 6.1125 5.92969C5.87344 6.19219 5.20312 6.82031 5.20312 8.1C5.20312 9.37969 6.13594 10.6172 6.2625 10.7906C6.39375 10.9641 8.09531 13.5891 10.7062 14.7188C12.3562 15.4312 13.0031 15.4922 13.8281 15.3703C14.3297 15.2953 15.3656 14.7422 15.5812 14.1328C15.7969 13.5234 15.7969 13.0031 15.7313 12.8953C15.6703 12.7781 15.4969 12.7125 15.2391 12.5859Z"
                          fill="white"
                        ></path>
                      </svg>
                      WhatsApp
                    </a>

                    <a
                      target="_blank"
                      href="https://tawk.to/chat/591adfa164f23d19a89b250b/1bn99j27u"
                      class="modal-btn-chat"
                    >
                      <svg
                        width="25"
                        height="31"
                        viewBox="0 0 29 31"
                        fill="none"
                        xmlns="http://www.w3.org/2000/svg"
                      >
                        <path
                          d="M14.5256 5C6.50249 5 0.00159581 10.1953 0.00159581 16.6071C0.00159581 19.2651 1.13129 21.6992 3.00372 23.6496C2.15943 25.8538 0.401047 27.7165 0.372677 27.7372C-0.00322709 28.1278 -0.102523 28.6998 0.110253 29.1881C0.330167 29.6763 0.815977 30 1.36182 30C4.85135 30 7.55218 28.5631 9.25439 27.4191C10.8431 27.9241 12.6702 28.2143 14.5256 28.2143C22.5486 28.2143 29 23.0173 29 16.6071C29 10.197 22.5486 5 14.5256 5ZM14.5312 25.5357C13.0134 25.5357 11.5172 25.3055 10.0839 24.8594L8.79309 24.4618L7.68666 25.2291C6.87811 25.7938 5.7643 26.4222 4.42409 26.8474C4.84255 26.171 5.23945 25.4104 5.55209 24.6013L6.15467 23.0388L4.98468 21.8184C3.96166 20.7422 2.72756 18.9621 2.72756 16.6071C2.72756 11.6825 8.01859 7.67857 14.5295 7.67857C21.0405 7.67857 26.3315 11.6825 26.3315 16.6071C26.3315 21.5318 21.0393 25.5357 14.5312 25.5357Z"
                          fill="black"
                        ></path>
                        <path
                          d="M10.4801 18.1023C10.1648 18.1023 9.89418 17.9893 9.66832 17.7635C9.44247 17.5376 9.32955 17.267 9.32955 16.9517C9.32955 16.6364 9.44247 16.3658 9.66832 16.1399C9.89418 15.9141 10.1648 15.8011 10.4801 15.8011C10.7955 15.8011 11.0661 15.9141 11.2919 16.1399C11.5178 16.3658 11.6307 16.6364 11.6307 16.9517C11.6307 17.1605 11.5774 17.3523 11.4709 17.527C11.3686 17.7017 11.2301 17.8423 11.0554 17.9489C10.8849 18.0511 10.6932 18.1023 10.4801 18.1023ZM15.4371 18.1023C15.1218 18.1023 14.8512 17.9893 14.6254 17.7635C14.3995 17.5376 14.2866 17.267 14.2866 16.9517C14.2866 16.6364 14.3995 16.3658 14.6254 16.1399C14.8512 15.9141 15.1218 15.8011 15.4371 15.8011C15.7525 15.8011 16.0231 15.9141 16.2489 16.1399C16.4748 16.3658 16.5877 16.6364 16.5877 16.9517C16.5877 17.1605 16.5344 17.3523 16.4279 17.527C16.3256 17.7017 16.1871 17.8423 16.0124 17.9489C15.842 18.0511 15.6502 18.1023 15.4371 18.1023ZM20.3942 18.1023C20.0788 18.1023 19.8082 17.9893 19.5824 17.7635C19.3565 17.5376 19.2436 17.267 19.2436 16.9517C19.2436 16.6364 19.3565 16.3658 19.5824 16.1399C19.8082 15.9141 20.0788 15.8011 20.3942 15.8011C20.7095 15.8011 20.9801 15.9141 21.206 16.1399C21.4318 16.3658 21.5447 16.6364 21.5447 16.9517C21.5447 17.1605 21.4915 17.3523 21.3849 17.527C21.2827 17.7017 21.1442 17.8423 20.9695 17.9489C20.799 18.0511 20.6072 18.1023 20.3942 18.1023Z"
                          fill="black"
                        ></path>
                      </svg>
                      Chat
                    </a>

                    <a
                      target="_blank"
                      href="https://ajuda.imobibrasil.com.br/"
                      class="modal-btn-centralAjuda"
                    >
                      <svg
                        xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 0 640 512"
                        width="25"
                        height="25"
                      >
                        <path
                          d="M622.34 153.2L343.4 67.5c-15.2-4.67-31.6-4.67-46.79 0L17.66 153.2c-23.54 7.23-23.54 38.36 0 45.59l48.63 14.94c-10.67 13.19-17.23 29.28-17.88 46.9C38.78 266.15 32 276.11 32 288c0 10.78 5.68 19.85 13.86 25.65L20.33 428.53C18.11 438.52 25.71 448 35.94 448h56.11c10.24 0 17.84-9.48 15.62-19.47L82.14 313.65C90.32 307.85 96 298.78 96 288c0-11.57-6.47-21.25-15.66-26.87.76-15.02 8.44-28.3 20.69-36.72L296.6 284.5c9.06 2.78 26.44 6.25 46.79 0l278.95-85.7c23.55-7.24 23.55-38.36 0-45.6zM352.79 315.09c-28.53 8.76-52.84 3.92-65.59 0l-145.02-44.55L128 384c0 35.35 85.96 64 192 64s192-28.65 192-64l-14.18-113.47-145.03 44.56z"
                          fill="white"
                        ></path>
                      </svg>
                      Tutoriais de Ajuda
                    </a>
                  </div>
                </div>
                <div class="modal-contatos-info">
                  <a href="tel:1140636343" title="Telefone (11) 4063-6343">
                    <svg
                      width="18"
                      height="18"
                      viewBox="0 0 18 18"
                      fill="none"
                      xmlns="http://www.w3.org/2000/svg"
                    >
                      <path
                        d="M17.4867 12.7194L13.5491 11.0319C13.3809 10.9602 13.1939 10.9451 13.0164 10.9889C12.8389 11.0326 12.6804 11.1329 12.5647 11.2745L10.8209 13.405C8.08423 12.1147 5.88181 9.91225 4.59149 7.17555L6.72199 5.43177C6.86391 5.31634 6.96436 5.15782 7.00813 4.9802C7.0519 4.80258 7.03661 4.61553 6.96457 4.44738L5.27705 0.509807C5.19798 0.328542 5.05815 0.180544 4.88165 0.0913347C4.70516 0.00212529 4.50307 -0.0227045 4.31023 0.0211269L0.653918 0.864892C0.467997 0.907825 0.302119 1.01251 0.183355 1.16186C0.064592 1.3112 -4.28284e-05 1.4964 2.12917e-08 1.68721C2.12917e-08 10.7049 7.30911 18 16.3128 18C16.5037 18.0001 16.6889 17.9355 16.8384 17.8168C16.9878 17.698 17.0925 17.5321 17.1355 17.3461L17.9792 13.6898C18.0228 13.496 17.9974 13.2931 17.9075 13.116C17.8176 12.9389 17.6688 12.7987 17.4867 12.7194Z"
                        fill="#7F8082"
                      ></path>
                    </svg>
                    (11) 4063-6343
                  </a>
                  <a href="tel:7140629662" title="Telefone (71) 4062-9662 ">
                    <svg
                      width="18"
                      height="18"
                      viewBox="0 0 18 18"
                      fill="none"
                      xmlns="http://www.w3.org/2000/svg"
                    >
                      <path
                        d="M17.4867 12.7194L13.5491 11.0319C13.3809 10.9602 13.1939 10.9451 13.0164 10.9889C12.8389 11.0326 12.6804 11.1329 12.5647 11.2745L10.8209 13.405C8.08423 12.1147 5.88181 9.91225 4.59149 7.17555L6.72199 5.43177C6.86391 5.31634 6.96436 5.15782 7.00813 4.9802C7.0519 4.80258 7.03661 4.61553 6.96457 4.44738L5.27705 0.509807C5.19798 0.328542 5.05815 0.180544 4.88165 0.0913347C4.70516 0.00212529 4.50307 -0.0227045 4.31023 0.0211269L0.653918 0.864892C0.467997 0.907825 0.302119 1.01251 0.183355 1.16186C0.064592 1.3112 -4.28284e-05 1.4964 2.12917e-08 1.68721C2.12917e-08 10.7049 7.30911 18 16.3128 18C16.5037 18.0001 16.6889 17.9355 16.8384 17.8168C16.9878 17.698 17.0925 17.5321 17.1355 17.3461L17.9792 13.6898C18.0228 13.496 17.9974 13.2931 17.9075 13.116C17.8176 12.9389 17.6688 12.7987 17.4867 12.7194Z"
                        fill="#7F8082"
                      ></path>
                    </svg>
                    (71) 4062-9662
                  </a>

                  <a href="tel:2140636693" title="Telefone (21) 4063-6693">
                    <svg
                      width="18"
                      height="18"
                      viewBox="0 0 18 18"
                      fill="none"
                      xmlns="http://www.w3.org/2000/svg"
                    >
                      <path
                        d="M17.4867 12.7194L13.5491 11.0319C13.3809 10.9602 13.1939 10.9451 13.0164 10.9889C12.8389 11.0326 12.6804 11.1329 12.5647 11.2745L10.8209 13.405C8.08423 12.1147 5.88181 9.91225 4.59149 7.17555L6.72199 5.43177C6.86391 5.31634 6.96436 5.15782 7.00813 4.9802C7.0519 4.80258 7.03661 4.61553 6.96457 4.44738L5.27705 0.509807C5.19798 0.328542 5.05815 0.180544 4.88165 0.0913347C4.70516 0.00212529 4.50307 -0.0227045 4.31023 0.0211269L0.653918 0.864892C0.467997 0.907825 0.302119 1.01251 0.183355 1.16186C0.064592 1.3112 -4.28284e-05 1.4964 2.12917e-08 1.68721C2.12917e-08 10.7049 7.30911 18 16.3128 18C16.5037 18.0001 16.6889 17.9355 16.8384 17.8168C16.9878 17.698 17.0925 17.5321 17.1355 17.3461L17.9792 13.6898C18.0228 13.496 17.9974 13.2931 17.9075 13.116C17.8176 12.9389 17.6688 12.7987 17.4867 12.7194Z"
                        fill="#7F8082"
                      ></path>
                    </svg>
                    (21) 4063-6693
                  </a>

                  <button
                    type="button"
                    class="modal-contatos-btn-mais"
                    id="open-list"
                  >
                    +
                  </button>
                </div>
                <div class="modal-contatos-lista-container">
                  <ul class="modal-contatos-lista">
                    <li>
                      <a href="tel:1832220557" title="Telefone (18) 3222-0557"
                        >(18) <span> 3222-0557</span> - PRESIDENTE PRUDENTE /
                        SP</a
                      >
                    </li>
                    <li>
                      <a href="tel:1140636343" title="Telefone (11) 4063-6343"
                        >(11) <span> 4063-6343</span> - SP</a
                      >
                    </li>
                    <li>
                      <a href="tel:2140636693" title="Telefone (21) 4063-6693"
                        >(21) <span> 4063-6693</span> - RIO DE JANEIRO / RJ</a
                      >
                    </li>
                    <li>
                      <a href="tel:3140627793" title="Telefone (31) 4062-7793"
                        >(31) <span> 4062-7793</span> - BELO HORIZONTE / MG</a
                      >
                    </li>
                    <li>
                      <a href="tel:4140639662" title="Telefone (41) 4063-9662"
                        >(41) <span> 4063-9662</span> - CURITIBA / PR</a
                      >
                    </li>
                    <li>
                      <a href="tel:4840529233" title="Telefone (48) 4052-9233"
                        >(48) <span> 4052-9233</span> - FLORIANÓPOLIS / SC</a
                      >
                    </li>
                    <li>
                      <a href="tel:5140639662" title="Telefone (51) 4063-9662"
                        >(51) <span> 4063-9662</span> - PORTO ALEGRE / RS</a
                      >
                    </li>
                    <li>
                      <a href="tel:6140639725" title="Telefone (61) 4063-9725"
                        >(61)<span> 4063-9725</span> - BRASÍLIA / DF</a
                      >
                    </li>
                    <li>
                      <a href="tel:6240539253" title="Telefone (62) 4053-9253"
                        >(62) <span> 4053-9253</span> - GOIÂNIA / GO</a
                      >
                    </li>
                    <li>
                      <a href="tel:7140629662" title="Telefone (71) 4062-9662"
                        >(71) <span> 4062-9662</span> - SALVADOR / BA</a
                      >
                    </li>
                    <li>
                      <a href="tel:8140629590" title="Telefone (81) 4062-9590"
                        >(81) <span> 4062-9590</span> - RECIFE / PE</a
                      >
                    </li>
                    <li>
                      <a href="tel:8540629775" title="Telefone (85) 4062-9775"
                        >(85) <span> 4062-9775</span> - FORTALEZA / CE</a
                      >
                    </li>
                    <li>
                      <a href="tel:18991652103" title="Telefone (18) 99165-2103"
                        >(18) <span> 99165-2103</span> (claro)</a
                      >
                    </li>
                    <li>
                      <a href="tel:18988227436" title="Telefone (18) 98822-7436"
                        >(18) <span> 98822-7436</span> (oi)</a
                      >
                    </li>
                    <li>
                      <a href="tel:18981282008" title="Telefone (18) 98128-2008"
                        >(18) <span> 98128-2008</span> (tim)</a
                      >
                    </li>
                    <li>
                      <a
                        target="_blank"
                        href="https://web.whatsapp.com/send?phone=5518988227436&amp;text=Ol%C3%A1,%20preciso%20de%20suporte.%20Meu%20site%20%C3%A9:%20www.imobbrasil.com.br"
                        title="WhatsApp"
                        rel="noreferrer"
                        >(18) <span>98822-7436</span>
                        <i class="fa fa-whatsapp"></i
                      ></a>
                    </li>
                  </ul>
                </div>
                <div class="modal-contatos-info">
                  <p>
                    <svg
                      width="21"
                      height="16"
                      viewBox="0 0 21 16"
                      fill="none"
                      xmlns="http://www.w3.org/2000/svg"
                    >
                      <path
                        d="M20.6021 5.28333C20.7621 5.15417 21 5.275 21 5.47917V14C21 15.1042 20.1182 16 19.0312 16H1.96875C0.881836 16 0 15.1042 0 14V5.48333C0 5.275 0.233789 5.15833 0.397852 5.2875C1.3166 6.0125 2.53477 6.93333 6.71836 10.0208C7.58379 10.6625 9.04395 12.0125 10.5 12.0042C11.9643 12.0167 13.4531 10.6375 14.2857 10.0208C18.4693 6.93333 19.6834 6.00833 20.6021 5.28333ZM10.5 10.6667C11.4516 10.6833 12.8215 9.45 13.5105 8.94167C18.9533 4.92917 19.3676 4.57917 20.6227 3.57917C20.8605 3.39167 21 3.1 21 2.79167V2C21 0.895833 20.1182 0 19.0312 0H1.96875C0.881836 0 0 0.895833 0 2V2.79167C0 3.1 0.139453 3.3875 0.377344 3.57917C1.63242 4.575 2.04668 4.92917 7.48945 8.94167C8.17852 9.45 9.54844 10.6833 10.5 10.6667Z"
                        fill="#767D82"
                      ></path>
                    </svg>
                    <a href="mailto:contato@imobibrasil.com.br"
                      >contato@imobibrasil.com.br</a
                    >
                  </p>
                </div>
                <div class="modal-contatos-atendimento">
                  <p>
                    Atendimento de segunda-feira à sexta-feira das 08:00 às
                    18:00 horas
                  </p>
                </div>
              </div>
            </div>
          </section>
        </div>

        <script>
          $(function () {
            $("#abrir_modal_suporte").on("click", function (e) {
              e.preventDefault();
              Swal.fire({
                title: "Fale Conosco",
                html: $("#conteudo_modal_contatos_suporte").html(),
                showCloseButton: true,
                focusConfirm: false,
                showCancelButton: false,
                showConfirmButton: false,
                customClass: {
                  closeButton: "topo-modal-contatos-container-fechar",
                  header: "topo-modal-contatos-container-header",
                  popup: "topo-modal-contatos-container-popup",
                },
              });
            });

            $("body").on("click", "#open-list", function () {
              $(this).toggleClass("active");
              $(".modal-contatos-lista-container").toggleClass("active");
            });
          });
        </script>

        <header>
          <title>Novidades</title>
          <link href="./styles/novidades.css" rel="stylesheet" />
        </header>

        <div class="modal-container" id="container-novidades">
          <div id="caixa-novidades" class="sidenav">
            <div class="top-side-nav">
              <div>
                <span>Novidades ImobiBrasil</span>
              </div>

              <div class="container-icons">
                <a
                  href="javascript:void(0)"
                  id="openFiltros"
                  class="icon-search-novidade"
                >
                  <i class="fa fa-search" aria-hidden="true"></i
                ></a>
                <a
                  href="javascript:void(0)"
                  id="closeNovidades"
                  class="icon-close-novidade"
                  >×</a
                >
              </div>
            </div>
          </div>

          <div id="fitros-novidades" class="sidenav2">
            <div class="top-side-nav">
              <div>
                <span>O que deseja filtrar?</span>
              </div>
              <div class="container-icons">
                <a
                  href="javascript:void(0)"
                  id="closeFiltros"
                  class="icon-close-novidade"
                  >×</a
                >
              </div>
            </div>

            <div class="container-filtro">
              <a
                href="javascript:void(0)"
                class="filtro-novidades"
                data-filtro="TODOS"
                >Todos</a
              ><a
                href="javascript:void(0)"
                class="filtro-novidades"
                data-filtro="NOVIDADES"
                >Novidades</a
              ><a
                href="javascript:void(0)"
                class="filtro-novidades"
                data-filtro="MELHORIAS"
                >Melhorias</a
              ><a
                href="javascript:void(0)"
                class="filtro-novidades"
                data-filtro="PODCAST"
                >Podcast</a
              >
            </div>
          </div>
        </div>

        <script>
          function openNovidades() {
            document.body.style.overflow = "hidden";
            var iframe = document.getElementById("iframeNovidades");
            // iframe.src = "https://admin01.imobibrasil.net/imobiliarias/novidades_conteudo.php";
            iframe.src = "novidades_conteudo.php";
            document.getElementById("container-novidades").style.display =
              "block";

            var larguraTela =
              window.innerWidth ||
              document.documentElement.clientWidth ||
              document.body.clientWidth;
            var larguraElemento =
              larguraTela < 450 ? larguraTela + "px" : "450px";

            // Atribui a largura ao elemento com o ID "caixa-novidades"
            document.getElementById("caixa-novidades").style.width =
              larguraElemento;
            document.getElementById("caixa-novidades").style.display = "block";
          }

          function closeNovidades() {
            var iframe = document.getElementById("iframeNovidades");
            iframe.src = "";
            document.body.style.overflow = "auto";
            document.getElementById("fitros-novidades").style.display = "none";
            document.getElementById("fitros-novidades").style.width = "0";
            document.getElementById("caixa-novidades").style.width = "0";
            document.getElementById("caixa-novidades").style.display = "none";
            document.getElementById("container-novidades").style.display =
              "none";
          }

          $("#closeNovidades").click(function () {
            closeNovidades();
          });

          $("#container-novidades").click(function (e) {
            if (e.target == this) {
              closeNovidades();
            }
          });

          $(".btnAbrirNovidades").click(function () {
            openNovidades();
          });

          $("#closeFiltros").click(function (e) {
            document.getElementById("fitros-novidades").style.display = "none";
            document.getElementById("caixa-novidades").style.width = "450px";
            document.getElementById("caixa-novidades").style.display = "block";
            document.getElementById("fitros-novidades").style.width = "0";
          });

          $("#openFiltros").click(function (e) {
            document.getElementById("caixa-novidades").style.display = "none";
            document.getElementById("caixa-novidades").style.width = "0";

            document.getElementById("fitros-novidades").style.width = "450px";
            document.getElementById("fitros-novidades").style.display = "block";
          });

         
         
        </script>
      </div>
    </section>
    <div style="height: 100%">
      <style>
        /*PERSONALIZAÇÕES MÓDULO FINANCIAMENTO*/

        .conteudo {
          background-color: #fff;
          min-height: 80%;
          display: flex;
          flex-direction: column;
          height: auto;
        }

        .configuracao {
          margin-bottom: 25px;
          padding: 0px;
          border-radius: 5px;
          box-shadow: 2px 2px 4px 1px #ccc;
        }

        .descricao {
          height: 40%;
          display: flex;
          justify-content: space-evenly;
          align-items: start;
          padding: 20px 0px;
        }

        .explicacao_modulo {
          width: 70%;
          display: flex;
          flex-direction: column;
          border-left: 1px solid #e9e9e9;
          padding-left: 20px;
        }

        .titulo_site {
          text-transform: uppercase;
          border-bottom: 2px solid #efefef;
          padding-bottom: 10px;
          margin-bottom: 15px;
          font-size: 24px;
          font-weight: normal;
          color: #444444;
        }
        #artigo_modulo {
          cursor: pointer;
          text-decoration: underline;
          color: #007fe2;
        }

        #artigo_modulo:hover {
          color: #9de0f6;
        }

        .fa-clone {
          margin: 0px 15px;
          color: #007fe2;
        }
        .fa-clone:before {
          content: "\f24d";
        }

        .titulo_configuracoes {
          padding: 15px;
        }
        .titulo_modulo {
          display: inline-block;
          color: rgba(0, 0, 0, 0.67);
          cursor: default;
          margin: 0px;
        }
        .subtitulo_modulo {
          font-size: 12px;
          margin-left: 25px;
          border-left: 1px solid #ccc;
          padding-left: 10px;
          font-weight: normal;
        }

        .formulario_financiamento {
          display: flex;
          flex-direction: column;
        }
        textarea {
          resize: none;
          color: #000000;
          font-family: Verdana;
          font-size: 11px;
          border: solid 1px #cccccc;
          width: 80%;
          background-color: #ffffff;
          border-radius: 3px;
          padding-left: 5px;
          margin-right: 20px;
        }

        .input_organization {
          display: flex;
          flex-direction: column;
          width: 100%;
        }

        .campo_titulo {
          display: flex;
          justify-content: space-between;
        }

        .input_group1,
        .input_group2,
        #description,
        #title {
          display: flex;
          margin-bottom: 20px;
        }

        #description,
        #title {
          width: 95%;
          padding: 10px;
        }
        input,
        select {
          color: #000000;
          font-family: Verdana;
          font-size: 11px;
          border: solid 1px #cccccc;
          height: 30px;
          width: 85%;
          background-color: #ffffff;
          border-radius: 3px;
          padding-left: 10px;
        }

        body {
          font-family: "Ubuntu";
          color: #444;
          height: 100vh;
          width: 100vw;
          max-width: 100%;
          max-height: 100;
        }

        label {
          margin-bottom: 5px;
        }

        #switch_exibicao {
          margin-top: 10px;
          display: flex;
          justify-content: flex-start;
        }

        #select_exibicao {
          display: flex;
          justify-content: flex-start;
        }

        .switch {
          margin: 0px 10px;
        }

        /* Paulo */

        .tabela_dados{
          width: 80%;
          margin: 0 auto;
          margin-top: 10vh;
          text-align: center;
          border-collapse: collapse;
          margin-bottom: 80px;
        }

        .tabela_dados td,
        .tabela_dados th{
          border-bottom: 1px solid #D3D3D3;
          border-top: 1px solid #D3D3D3;
          padding: 10px;
        }

        .tabela_dados button{
          background-color: transparent;
          border: 1px solid transparent;
          cursor: pointer;
        }

        .tabela_dados i{
          width: 100%;
        }

        .salvar_opcao_modulo {
          color: #fff;
          background-color: #039341;
          border-color: #4cae4c;
          display: inline-block;
          margin-bottom: 0;
          font-weight: normal;
          text-align: center;
          vertical-align: middle;
          cursor: pointer;
          border: 1px solid transparent;
          white-space: nowrap;
          padding: 6px 12px;
          font-size: medium;
          border-radius: 4px;
          width: auto;
        }

        .desativa_modulo {
          margin: 10px 0px 0px 0px;
          width: auto;
          font-size: medium;
          color: #fff;
          background-color: #039341;
          border-color: #4cae4c;
          display: inline-block;
          text-align: center;
          vertical-align: middle;
          cursor: pointer;
          border: 1px solid transparent;
          white-space: nowrap;
          padding: 6px 12px;
          border-radius: 4px;
        }

        .ativa_modulo {
          margin: 10px 0px 0px 0px;
          width: auto;
          font-size: medium;
          color: #fff;
          background-color: #d6a100;
          border-color: #4cae4c;
          display: inline-block;
          text-align: center;
          vertical-align: middle;
          cursor: pointer;
          border: 1px solid transparent;
          white-space: nowrap;
          padding: 6px 12px;
          border-radius: 4px;
        }

        .redefinir_opcoes {
          color: #fff;
          background-color: #d6a100;
          display: inline-block;
          font-weight: normal;
          text-align: center;
          vertical-align: middle;
          cursor: pointer;
          border: 1px solid transparent;
          white-space: nowrap;
          padding: 6px 12px;
          font-size: medium;
          border-radius: 4px;
          width: auto;
          margin-left: 10px;
        }

        .salvar_opcao_modulo:hover,
        .ativa_desativa_modulo:hover {
          background-color: #5cb85c;
        }

        .redefinir_opcoes:hover {
          background-color: #f9bc04;
        }
        p,
        h1 {
          margin: 5px 0px;
        }

        .buttons_group {
          display: flex;
        }

        .corpo_principal_formulario {
          display: flex;
          flex-direction: row;
        }
        .explicacoes_calculos {
          display: flex;
          flex-direction: column;
          width: 40%;
          line-height: 1.5;
          padding: 0px 30px;
          margin-bottom: 30px;
          border-left: 1px solid #e9e9e9;
        }

        .formulario_financiamento_box {
          width: 60%;
          padding: 20px 0px 20px 30px;
        }

        .imagem_modulo {
          padding: 0px 20px;
        }
        .responsividade_600 {
          display: none;
        }
        ul li::marker {
          color: #039341;
        }
        .ocultar_botao{
          display: none;
        }
        /* RESPONSIVIDADE DO MÓDULO */
        @media (max-width: 1200px) {
          .explicacoes_calculos {
            padding: 0px 10px;
          }
        }

        @media (max-width: 1100px) {
          .descricao {
            flex-direction: column;
            align-items: center;
          }
          .explicacao_modulo {
            border: none;
            margin-top: 20px;
            padding: 0px;
            width: 90%;
          }
          .imagem_modulo {
            padding: 0px;
          }
          .desliga_modulo,
          .liga_modulo {
            text-align: center;
          }
          .corpo_principal_formulario {
            flex-direction: column;
            align-items: center;
          }
          .titulo_configuracoes,
          .titulo_site {
            text-align: center;
          }
          .explicacoes_calculos {
            border: none;
            width: 90%;
            padding: 0px;
            align-items: center;
          }

          .buttons_group {
            justify-content: center;
          }
          .formulario_financiamento_box {
            width: 90%;
            padding: 20px 0px;
          }
          .responsividade_forms {
            margin-bottom: 10px;
          }
          #description {
            margin: 0px;
            margin-bottom: 20px;
          }
        }
        @media (max-width: 610px) {
          .responsividade_forms {
            display: flex;
            justify-content: space-around;
          }
          .input_group1,
          .input_group2 {
            flex-direction: column;
            flex-grow: 1;
          }
          input,
          select,
          #description,
          #title {
            width: 100%;
            margin-bottom: 10px;
          }
          #taxa_juros,
          #valor_max,
          #periodo_padrao {
            width: 95%;
          }

          .subtitulo_modulo {
            display: none;
          }
          .responsividade_600 {
            display: block;
            text-align: center;
            font-weight: normal;
          }

          .explicacoes_calculos h1 {
            align-self: self-start;
          }
        }

        @media (max-width: 480px) {
          .responsividade_forms {
            display: flex;
            flex-direction: column;
            justify-content: space-around;
          }
          .input_group1,
          .input_group2 {
            flex-grow: 1;
            margin: 0px;
          }
          #taxa_juros,
          #valor_max,
          #periodo_padrao {
            width: 100%;
          }
        }

 
button {
  padding: 0.6rem 1.2rem;
  background-color: #888;
  color: #fff;
  border: none;
  border-radius: 0.25rem;
  cursor: pointer;
  opacity: 0.9;
  font-size: 1rem;
}



</style>


<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script>



$(document).ready(function(){
    $('#formdocumentos').submit(function(e){

     let file_contrato1 = $('#fileInput1').val()
     let file_contrato2 = $('#fileInput2').val()
     let file_contrato3 = $('#fileInput3').val()
     
      if(file_contrato1 == '' &&  file_contrato2 == '' && file_contrato3 == '' ){
        e.preventDefault()
        Swal.fire("Adicione pelo menos um documento !");
      }

    })


    $('#btn_docs').click(function(){

      $(this).css('display', 'none')
      $('#btn_docs_abrir').css('display', 'block')

      $('#lista_docs').addClass('remover')
    })



    $('#btn_docs_abrir').click(function(){
      $(this).css('display', 'none')
      $('#btn_docs').css('display', 'block')

      $('#lista_docs').removeClass('remover')
    })


    $('.del').click(function(){
      let codd = $(this).closest('.linha_docs').find('.linha_cod_doc').text()
      let path_doc = $(this).closest('.linha_docs').find('.path_doc').val()

      let obj_excluir_doc = {
        codigo_doc_del : codd, 
        cod_contrato: <?php echo $_GET['contrato'] ?>,
        pathh: path_doc
      }


      $.ajax({
        method: 'POST',
        url: 'index.php',
        data: obj_excluir_doc,

        success: function(res){
          Swal.fire({
              position: "center",
              icon: "success",
              title: "Documento excluído com sucesso",
              showConfirmButton: false,
              timer: 1500
            });

            setTimeout(()=>{

              window.location.reload()

            },1700)


        },
        error: function(err){
          console.log('deu ruim')
        }
      })



    })

})

</script>


     

      <section class="corpo">
        <div class="container">

        <!-- Paulo -->
        
          <div class="conteudo">

  <div class='pessoas'>

  <div class="revisa">
                    <div class="menu_ficha">
                        <div>
                            <a href="./ficha.php?contrato=<?php echo $cod_contrato?> ">Ficha</a>
                        </div>

                        <div>
                            <a href="./ficha_check.php?contrato=<?php echo $cod_contrato?>">Checklist</a>
                        </div>

                        <div>
                            <a style="color:#2CA62F; border-bottom:1px solid #2CA62F" href="./documentos.php?contrato=<?php echo $cod_contrato?> ">Documentos</a>
                        </div>


                    </div>
    </div>



<div class='dados_contratoo'>
  <p>Código do contrato: <strong><?php echo $dados_contrato['referencia'] ?></strong></p>
  <p>Título: <strong><?php echo $dados_contrato['titulo'] ?></strong></p>
  <p>Tipo de contrato: <strong><?php echo $dados_contrato['tipo'] ?></strong></p>
</div>

<div>
  <p>Proprietário: <strong><?php echo $dados_contrato['nome_proprietario'] ?></strong></p>
  <p>CPF: <strong><?php echo $dados_contrato['cpf_proprietario'] ?></strong></p>
</div>

<div>
  <p>Corretor: <strong><?php echo $dados_contrato['nome_corretor'] ?></strong></p>
  <p>CPF: <strong><?php echo $dados_contrato['cpf_corretor'] ?></strong></p>
  <p>CRECI: <strong><?php echo $dados_contrato['creci'] ?></strong></p>
</div>

</div>


<div class="revisa">




<div class="ilista" >
    <i id="btn_docs" style="display: none;" class="icon-chevron fa corpo-interno__categoria__cabecalho__titulo__icone fa-chevron-up"></i>
    <i id="btn_docs_abrir"  class="icon-chevron fa corpo-interno__categoria__cabecalho__titulo__icone fa-chevron-down"></i>
</div>

<h2 style='text-align: center;'><i class="fa fa-clone corpo-interno__categoria__cabecalho__titulo__icone"></i>  Lista de documentos</h2>


<div id="lista_docs" class="remover">



      
      <div class="<?php echo $dd->num_rows > 0 ? '':'remover'  ?>" >

      <table class="tabela_contratos">
          
          <tr>
            <th>Código</th>
            <th>Nome</th>
            <th>Tipo</th>
            <th>Data de inserção</th>
          </tr>
  
         </tr>
  
          <?php 
              while($documento = $dd->fetch_assoc()){

                echo '<tr class="linha_docs">';
                echo      '<td class="linha_cod_doc" >'. $documento['codigo_documento'] .'</td>';
                echo      '<td>'. $documento['nome'] .'</td>';
                echo      '<td>'. $documento['tipo_doc'] .'</td>';
                echo      '<td>'. $documento['dt_formatada'] .'</td>';
                echo      '<input class="path_doc" type="hidden" value="' . $documento['path'] . '">';
                echo      '<td>'. '<i class="fa-solid fa-trash del">' .'</i>' .'</td>';
                echo '</tr>';
              }
          ?>
  
        </table>

      </div>

      <div class="<?php echo $dd->num_rows > 0 ? 'remover':'ativ'?>" >
        <h4 style='text-align: center;'>Sem documentos</h4>
        <img src="./img/sem_contr.png" alt="">
      </div>


    </div>
</div>


          <h1 style="text-align: center;margin-top:50px">Adicionar documentos</h1>

          <div class="revisa">
             <br>

  <form method="post" enctype="multipart/form-data" id="formdocumentos">

<div class="inputfile">
    <div class="pular" style="background-color:#0b5dd9;">
        <p >Adicionar o contrato de <?php echo $dados_contrato['tipo'] ?> </p>
    </div>
        
    <div class="img_documento">
        <img src="./img/contratoo.png" alt="documentos clientes">
    </div>

    <div class="pai_inputfile">
        <input type="file" class="custom-file-input" accept=".pdf, .doc, .docx" id="fileInput1"  name="contratoPrincipal[]">
        <p class="custom-button" style="background-color:#0b5dd9;" onclick="document.getElementById('fileInput1').click()">Selecionar Arquivos</p>
    </div>
</div>

<div class="inputfile">
    <div class="pular" style="background-color:#b9c9be;">
        <p>Adicionar contrato de intermediação </p>
    </div>
        
    <div class="img_documento">
        <img src="./img/intermedio.png" alt="documentos clientes">
    </div>

    <div class="pai_inputfile">
        <input type="file" class="custom-file-input" accept=".pdf, .doc, .docx" id="fileInput2" name="documentos_pessoas2[]" >
        <p class="custom-button" style="background-color:#68a378;" onclick="document.getElementById('fileInput2').click()">Selecionar Arquivos</p>
    </div>
</div>

<div class="inputfile">
    <div class="pular" style="background-color: #b9c9be;">
        <p>Adicionar documentos das partes interessadas </p>
    </div>
        
    <div class="img_documento">
        <img src="./img/imgdocumentos.png" alt="documentos clientes">
    </div>

    <div class="pai_inputfile">
        <input type="file" class="custom-file-input" accept=".jpg,.jpeg,.png,.pdf" id="fileInput3" name="documentos_pessoas3[]" multiple>
        <p class="custom-button" style="background-color: #68a378;" onclick="document.getElementById('fileInput3').click()">Selecionar Arquivos</p>
    </div>
</div>

<div style="width: 15%;margin:auto">
    <button class="btnss" type="submit">Salvar documentos</button>
</div>

</form>

            

          </div>


        </div>
         
          
                      
            
        

        <!-- Paulo -->































        </div>
      </section>
    </div>

    <style>
      .chatbox-message-wrapper-nps {
        transform: scale(0);
      }
    </style>

    <div class="chatbox-wrapper-nps">
      <div class="chatbox-toggle-nps">
        <div
          style="
            transform: rotate(270deg);
            width: 110px;
            display: flex;
            place-content: center;
            align-items: center;
            justify-content: space-evenly;
            font-family: hind;
            font-size: 18px;
          "
        >
          <i
            style="transform: rotate(90deg)"
            class="fa-regular fa-face-smile"
          ></i>
          Opinião
        </div>
      </div>

      <div class="chatbox-message-wrapper-nps">
        <div class="chatbox-message-content-nps">
          <div class="" style="position: relative">
            <div
              class="background-widget-nps"
              style="
                width: 100%;
                height: 230px;
                border-top-left-radius: 4px;
                border-top-right-radius: 4px;
              "
            ></div>

            <div class="content-widget-nps">
              <div
                class="header-content-widget-nps flex flex-col z-10 ml-4 text-white"
              >
                <div
                  class="text-3xl mb-2"
                  style="
                    margin-top: 15px;
                    user-select: none;
                    font-size: 19px;
                    line-height: 25px;
                  "
                >
                  Você indicaria a Imobibrasil?
                </div>
              </div>

              <div
                class="border-0 border-t-4 border-green-500 rounded z-10 shadow-md"
                id="abrirchamado"
                style="margin-top: 15px"
              >
                <div
                  class="bg-white border border-t-0 rounded-t-none rounded-b flex flex-col"
                >
                  <div class="px-6 py-4 flex flex-col items-start gap-3">
                    <label
                      class="text-bold"
                      style="
                        user-select: none;
                        font-size: 13px;
                        display: inline-block;
                        max-width: 100%;
                        margin-bottom: 5px;
                        font-weight: bold;
                        font-family: arial;
                      "
                      >Qual probabilidade de você indicar para seus amigos e
                      familiares?</label
                    >
                  </div>

                  <div class="px-6 pab-4 flex flex-col items-start gap-3">
                    <span
                      style="color: red; display: none"
                      id="error-cm-envio"
                    ></span>

                    <span
                      style="color: #039341; display: none"
                      id="success-cm-envio"
                    ></span>

                    <div
                      class="flex flex-row gap-1"
                      style="
                        width: 100%;
                        justify-content: center;
                        align-items: center;
                        user-select: none;
                      "
                    >
                      <span
                        style="
                          font-size: 9px;

                          margin-right: 4px;
                        "
                        >Baixa</span
                      >

                      <div id="widgetNota" class="widget-nps">
                        <button
                          class="detractor"
                          style="
                            font-family: ubuntu;
                            font-size: 16px;
                            white-space: nowrap;
                            vertical-align: middle;
                            display: inline-block;
                            background: none;
                            border: none;
                            box-shadow: none;
                            cursor: pointer;
                            text-align: center;
                            font-weight: 500;
                            border-radius: 100%;
                            margin: 0;
                            outline: none;
                            margin-left: -1px;
                            width: 30px;
                            height: 30px;
                            border: 3px solid #eee;
                            transform: scale(1);
                            transition: background 0.2s ease-in,
                              color 0.2s ease-in, border-color 0.2s ease-in,
                              transform 0.2s cubic-bezier(0.5, 2, 0.5, 0.75);
                            padding: 0px;
                          "
                        >
                          1
                        </button>

                        <button
                          class="detractor"
                          style="
                            font-family: ubuntu;
                            font-size: 16px;
                            white-space: nowrap;
                            vertical-align: middle;
                            display: inline-block;
                            background: none;
                            border: none;
                            box-shadow: none;
                            cursor: pointer;
                            text-align: center;
                            font-weight: 500;
                            border-radius: 100%;
                            margin: 0;
                            outline: none;
                            margin-left: -1px;
                            width: 30px;
                            height: 30px;
                            border: 3px solid #eee;
                            transform: scale(1);
                            transition: background 0.2s ease-in,
                              color 0.2s ease-in, border-color 0.2s ease-in,
                              transform 0.2s cubic-bezier(0.5, 2, 0.5, 0.75);
                            padding: 0px;
                          "
                        >
                          2
                        </button>

                        <button
                          class="detractor"
                          style="
                            font-family: ubuntu;
                            font-size: 16px;
                            white-space: nowrap;
                            vertical-align: middle;
                            display: inline-block;
                            background: none;
                            border: none;
                            box-shadow: none;
                            cursor: pointer;
                            text-align: center;
                            font-weight: 500;
                            border-radius: 100%;
                            margin: 0;
                            outline: none;
                            margin-left: -1px;
                            width: 30px;
                            height: 30px;
                            border: 3px solid #eee;
                            transform: scale(1);
                            transition: background 0.2s ease-in,
                              color 0.2s ease-in, border-color 0.2s ease-in,
                              transform 0.2s cubic-bezier(0.5, 2, 0.5, 0.75);
                            padding: 0px;
                          "
                        >
                          3
                        </button>

                        <button
                          class="detractor"
                          style="
                            font-family: ubuntu;
                            font-size: 16px;
                            white-space: nowrap;
                            vertical-align: middle;
                            display: inline-block;
                            background: none;
                            border: none;
                            box-shadow: none;
                            cursor: pointer;
                            text-align: center;
                            font-weight: 500;
                            border-radius: 100%;
                            margin: 0;
                            outline: none;
                            margin-left: -1px;
                            width: 30px;
                            height: 30px;
                            border: 3px solid #eee;
                            transform: scale(1);
                            transition: background 0.2s ease-in,
                              color 0.2s ease-in, border-color 0.2s ease-in,
                              transform 0.2s cubic-bezier(0.5, 2, 0.5, 0.75);
                            padding: 0px;
                          "
                        >
                          4
                        </button>

                        <button
                          class="detractor"
                          style="
                            font-family: ubuntu;
                            font-size: 16px;
                            white-space: nowrap;
                            vertical-align: middle;
                            display: inline-block;
                            background: none;
                            border: none;
                            box-shadow: none;
                            cursor: pointer;
                            text-align: center;
                            font-weight: 500;
                            border-radius: 100%;
                            margin: 0;
                            outline: none;
                            margin-left: -1px;
                            width: 30px;
                            height: 30px;
                            border: 3px solid #eee;
                            transform: scale(1);
                            transition: background 0.2s ease-in,
                              color 0.2s ease-in, border-color 0.2s ease-in,
                              transform 0.2s cubic-bezier(0.5, 2, 0.5, 0.75);
                            padding: 0px;
                          "
                        >
                          5
                        </button>

                        <button
                          class="passive"
                          style="
                            font-family: ubuntu;
                            font-size: 16px;
                            white-space: nowrap;
                            vertical-align: middle;
                            display: inline-block;
                            background: none;
                            border: none;
                            box-shadow: none;
                            cursor: pointer;
                            text-align: center;
                            font-weight: 500;
                            border-radius: 100%;
                            margin: 0;
                            outline: none;
                            margin-left: -1px;
                            width: 30px;
                            height: 30px;
                            border: 3px solid #eee;
                            transform: scale(1);
                            transition: background 0.2s ease-in,
                              color 0.2s ease-in, border-color 0.2s ease-in,
                              transform 0.2s cubic-bezier(0.5, 2, 0.5, 0.75);
                            padding: 0px;
                          "
                        >
                          6
                        </button>

                        <button
                          class="passive"
                          style="
                            font-family: ubuntu;
                            font-size: 16px;
                            white-space: nowrap;
                            vertical-align: middle;
                            display: inline-block;
                            background: none;
                            border: none;
                            box-shadow: none;
                            cursor: pointer;
                            text-align: center;
                            font-weight: 500;
                            border-radius: 100%;
                            margin: 0;
                            outline: none;
                            margin-left: -1px;
                            width: 30px;
                            height: 30px;
                            border: 3px solid #eee;
                            transform: scale(1);
                            transition: background 0.2s ease-in,
                              color 0.2s ease-in, border-color 0.2s ease-in,
                              transform 0.2s cubic-bezier(0.5, 2, 0.5, 0.75);
                            padding: 0px;
                          "
                        >
                          7
                        </button>

                        <button
                          class="passive"
                          style="
                            font-family: ubuntu;
                            font-size: 16px;
                            white-space: nowrap;
                            vertical-align: middle;
                            display: inline-block;
                            background: none;
                            border: none;
                            box-shadow: none;
                            cursor: pointer;
                            text-align: center;
                            font-weight: 500;
                            border-radius: 100%;
                            margin: 0;
                            outline: none;
                            margin-left: -1px;
                            width: 30px;
                            height: 30px;
                            border: 3px solid #eee;
                            transform: scale(1);
                            transition: background 0.2s ease-in,
                              color 0.2s ease-in, border-color 0.2s ease-in,
                              transform 0.2s cubic-bezier(0.5, 2, 0.5, 0.75);
                            padding: 0px;
                          "
                        >
                          8
                        </button>

                        <button
                          class="promoter"
                          style="
                            font-family: ubuntu;
                            font-size: 16px;
                            white-space: nowrap;
                            vertical-align: middle;
                            display: inline-block;
                            background: none;
                            border: none;
                            box-shadow: none;
                            cursor: pointer;
                            text-align: center;
                            font-weight: 500;
                            border-radius: 100%;
                            margin: 0;
                            outline: none;
                            margin-left: -1px;
                            width: 30px;
                            height: 30px;
                            border: 3px solid #eee;
                            transform: scale(1);
                            transition: background 0.2s ease-in,
                              color 0.2s ease-in, border-color 0.2s ease-in,
                              transform 0.2s cubic-bezier(0.5, 2, 0.5, 0.75);
                            padding: 0px;
                          "
                        >
                          9
                        </button>

                        <button
                          class="promoter"
                          style="
                            font-family: ubuntu;
                            font-size: 16px;
                            white-space: nowrap;
                            vertical-align: middle;
                            display: inline-block;
                            background: none;
                            border: none;
                            box-shadow: none;
                            cursor: pointer;
                            text-align: center;
                            font-weight: 500;
                            border-radius: 100%;
                            margin: 0;
                            outline: none;
                            margin-left: -1px;
                            width: 30px;
                            height: 30px;
                            border: 3px solid #eee;
                            transform: scale(1);
                            transition: background 0.2s ease-in,
                              color 0.2s ease-in, border-color 0.2s ease-in,
                              transform 0.2s cubic-bezier(0.5, 2, 0.5, 0.75);
                            padding: 0px;
                          "
                        >
                          10
                        </button>
                      </div>

                      <span
                        style="
                          font-size: 9px;

                          margin-left: 4px;
                        "
                        >Alta</span
                      >
                    </div>

                    <div
                      class="flex flex-col items-start gap-3"
                      style="margin-top: 30px"
                    >
                      <label
                        id="toggleIcon"
                        class=""
                        style="
                          cursor: pointer;
                          user-select: none;
                          font-weight: 400;
                          color: grey;
                          display: inline-block;
                          max-width: 100%;
                          margin-bottom: 5px;
                          font-family: arial;
                        "
                        >Adicionar observação</label
                      >
                    </div>

                    <div
                      class="flex flex-row gap-1"
                      id="localTextarea"
                      style="width: 100%; height: 0px"
                    >
                      <textarea
                        id="inputMensagemNps"
                        style="
                          margin-bottom: 0;
                          border: none;
                          line-height: 1.2em;
                          display: none;
                          font-family: Ubuntu;
                        "
                        type="text"
                        rows="1"
                        class="border flex-1 p-2 shadow-inner outline-none bg-gray-50 focus:bg-white"
                      ></textarea>
                    </div>
                    <small
                      id="small_observacao"
                      style="color: red; font-size: 11px; display: none"
                      >Por favor adicione uma observação</small
                    >
                    <small
                      id="small_nota"
                      style="color: red; font-size: 11px; display: none"
                      >Por favor selecione uma nota</small
                    >

                    <button
                      id="enviarFeedback"
                      class="btenviar2"
                      style="
                        margin-top: 20px;
                        user-select: none;
                        background: #1c813c;
                        color: white;
                        cursor: pointer;
                      "
                    >
                      <i class="fa fa-paper-plane" aria-hidden="true"></i>
                      Enviar
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <link href="./styles/boxicons.min.css" rel="stylesheet" />
    <link href="./styles/nps_widget.css" rel="stylesheet" />

   

    <a href="javascript:" id="return-to-top" style="display: none"
      ><i class="fa fa-arrow-up" aria-hidden="true"></i
    ></a>
    <footer>
      <div class="container" id="rodape">
        <div>
          <a
            href="https://admin01.imobibrasil.net/imobiliarias/inicial.php"
            title="Inicial"
            ><img src="./img/logo.png" alt="logo-site"
          /></a>
        </div>

        <div class="identificacao">
          <p style="margin: 0">
            Identificação na página, IP: 186.193.124.116, 02/03/2024 -
            15:10:09<br />
            ImobiBrasil Sites para Imobiliárias e Corretores - Todos os Direitos
            Reservados
          </p>
        </div>

        <div class="social">
          Siga-nos
          <a
            href="https://www.facebook.com/imobibrasil"
            target="_blank"
            title="Facebook"
            ><i class="fa-brands fa-facebook" aria-hidden="true"></i
          ></a>
          <a
            href="https://www.youtube.com/imobibrasilbr"
            target="_blank"
            title="YouTube"
            ><i class="fa-brands fa-youtube" aria-hidden="true"></i
          ></a>
          <a
            href="http://www.imobibrasil.com.br/blog/"
            target="_blank"
            title="Blog"
            ><i class="fa fa-pencil-square" aria-hidden="true"></i
          ></a>
        </div>
      </div>
    </footer>
    <script type="text/javascript" src="./scripts/script.js"></script>
    <script>
      $(window).scroll(function () {
        if ($(this).scrollTop() >= 50) {
          // If page is scrolled more than 50px
          $("#return-to-top").fadeIn(200); // Fade in the arrow
        } else {
          $("#return-to-top").fadeOut(200); // Else fade out the arrow
        }
      });
      $("#return-to-top").click(function () {
        // When arrow is clicked
        $("body,html").animate(
          {
            scrollTop: 0, // Scroll to top of body
          },
          500
        );
      });
    </script>
  </body>
</html>
