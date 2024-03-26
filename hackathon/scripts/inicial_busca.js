
 $(document).ready(function () {

  let timeoutId;

  $('#nova_busca_aprimorada_busca_ajax').keyup(function () {

   let query = $('#nova_busca_aprimorada_busca_ajax').val();

   $('#paginas-exibicao__linha__input__resultado_topo').empty();
   if (query.length > 2) {
    $('#paginas-exibicao__linha__input__resultado_topo').append('<div class="select2-results__option select2-results__option--group" aria-label="buscando" style="padding-top: 17px;"><div style="height: 40px;"><i class="fa fa-spinner spinner" aria-hidden="true" style="margin: 13px;"></i> Buscando...</div></div>')
   }

   // Limpa o timeout anterior, se houver
   clearTimeout(timeoutId);

   // Define um novo timeout de 1,2 segundos
   timeoutId = setTimeout(function () {
    if (query.length > 2 && !document.getElementById("nova_busca_aprimorada_busca_ajax").readOnly) {

     $.ajax({
      url: 'inicial_busca_api.php?formato=json',
      dataType: 'json',
      data: {
       textoPesquisado: query,
      },
      success: function (data) {
       var groups = {};
       var items = Object.values(data);

       if (items.length === 0 || Object.keys(items[0]).length === 0) {
        $('#paginas-exibicao__linha__input__resultado_topo').empty();
        $('#paginas-exibicao__linha__input__resultado_topo').append('<div class="select2-results__option select2-results__option--group" aria-label="Nadaencontrado"><div style="margin: 13px;">Nada encontrado, especifique por gentileza</div></div>')

        return;
       } else {
        items.forEach(function (item) {
         var groupKey = item.campo_encontrado || 'Outros';
         if (!groups[groupKey]) {
          groups[groupKey] = {
           text: groupKey,
           children: []
          };
         }

         let text = '';
         
            if (item.campo_encontrado === 'Bairro' || item.campo_encontrado === 'Referência' || item.campo_encontrado === 'Condomínio' || item.campo_encontrado === 'Cidade' || item.campo_encontrado === 'Caracteristica' || item.campo_encontrado === 'Região' || item.campo_encontrado === 'Logradouro' || item.campo_encontrado === 'Tipo de imovel') {
                

                if(item.campo_encontrado === 'Tipo de imovel'){
                    var regex = new RegExp("(" + query + ")", "i");
                    item.tipo_imovel = item.tipo_imovel.replace(regex, "<strong>$1</strong>");
                }
                if(item.campo_encontrado === 'Bairro'){
                    var regex = new RegExp("(" + query + ")", "i");
                    item.nome_bairro = item.nome_bairro.replace(regex, "<strong>$1</strong>");
                }
                if(item.campo_encontrado === 'Referência'){
                    var regex = new RegExp("(" + query + ")", "i");
                    item.referencia_imovel = item.referencia_imovel.replace(regex, "<strong>$1</strong>");
                }
                if(item.campo_encontrado === 'Condomínio'){
                    var regex = new RegExp("(" + query + ")", "i");
                    item.edificio_imovel = item.edificio_imovel.replace(regex, "<strong>$1</strong>");
                }
                if(item.campo_encontrado === 'Cidade'){
                    var regex = new RegExp("(" + query + ")", "i");
                    item.end_cidade_imovel = item.end_cidade_imovel.replace(regex, "<strong>$1</strong>");
                }
                if(item.campo_encontrado === 'Região'){
                    var regex = new RegExp("(" + query + ")", "i");
                    item.end_regiao_imovel = item.end_regiao_imovel.replace(regex, "<strong>$1</strong>");
                }
                if(item.campo_encontrado === 'Logradouro'){
                    var regex = new RegExp("(" + query + ")", "i");
                    item.end_logradouro_imovel = item.end_logradouro_imovel.replace(regex, "<strong>$1</strong>");
                }
                var regex = new RegExp("(" + query + ")", "i");
                text = (item.campo_encontrado === 'Caracteristica'
                    ? 'Caracteristica: ' + item.nome_caracteristica.replace(regex, "<strong>$1</strong>") + ' - '
                    : '') +
                    (item.referencia_imovel != '' ? '(' + item.referencia_imovel + ') - ' : '') +
                    (item.campo_encontrado === 'Tipo de imovel'
                    ? 'Tipo: ' + item.tipo_imovel + ' - '
                    : '') +
                    (item.end_logradouro_imovel != '' ? item.end_logradouro_imovel + ', ' : '') +
                    (item.nome_bairro != '' ? item.nome_bairro + ', ' : '') +
                    (item.campo_encontrado === 'Condomínio' ? item.edificio_imovel + ', ' : '') +
                    (item.end_regiao_imovel != '' ? item.end_regiao_imovel + ', ' : '') +
                    (item.end_cidade_imovel != '' ? item.end_cidade_imovel + ', ' : '') 
                if (text.slice(-2) === ', ') {
                    // Remover a vírgula e o espaço
                    text = text.slice(0, -2);
                }
            } else if (
            item.campo_encontrado === 'Cliente' ||
            item.campo_encontrado === 'Corretor' ||
            item.campo_encontrado === 'Proprietário' ||
            item.campo_encontrado === 'Locatário' ||
            item.campo_encontrado === 'Interessado' ||
            item.campo_encontrado === 'Cliente_outros'
            ) {
            var regex = new RegExp("(" + query + ")", "i");
            text = item.nome_proprietario.replace(regex, "<strong>$1</strong>");
            // text = "<strong>" + item.nome_proprietario + "</strong>";
            } else if (item.campo_encontrado === 'Negócio') {
            var regex = new RegExp("(" + query + ")", "i");
            text = item.titulo_negocio.replace(regex, "<strong>$1</strong>");
            } else {
            // Valor padrão se nenhuma condição for atendida
            text = '';
            }

         groups[groupKey].children.push({
          id: item.codigo_imovel,
          text: text,
          codigo_imovel: item.codigo_imovel,
          exibir_imovel: item.exibir_imovel,
          codigo_proprietario: item.codigo_proprietario,
          nome_proprietario: item.nome_proprietario,
          codigo_negocio: item.codigo_negocio,
          titulo_negocio: item.titulo_negocio,
          cep: item.end_cep_imovel,
          cidade: item.end_cidade_imovel,
          logradouro: item.end_logradouro_imovel,
          regiao: item.end_regiao_imovel,
          condominio: item.edificio_imovel,
          caracteristica: item.nome_caracteristica,
          bairro: item.nome_bairro,
          referencia: item.referencia_imovel,
          pesquisa_por: item.campo_encontrado,
          link: (
           (item.campo_encontrado === 'Bairro' ||
            item.campo_encontrado === 'Tipo de imovel' ||
            item.campo_encontrado === 'Referência' ||
            item.campo_encontrado === 'Condomínio' ||
            item.campo_encontrado === 'Cidade' ||
            item.campo_encontrado === 'Caracteristica' ||
            item.campo_encontrado === 'Logradouro' ||
            item.campo_encontrado === 'Região') ?
            'imovel_vida_frame.php?cod=' + item.codigo_imovel + '&pagina=imovel_ficha' :
            (item.campo_encontrado === 'Cliente' ||
             item.campo_encontrado === 'Corretor' ||
             item.campo_encontrado === 'Proprietário' ||
             item.campo_encontrado === 'Locatário' ||
             item.campo_encontrado === 'Interessado' ||
             item.campo_encontrado === 'Cliente_outros') ?
             'clientes_vida_frame.php?cliente=' + item.codigo_proprietario + '&pagina=clientes_detalhes' :
             (item.campo_encontrado === 'Negócio') ?
              'negocios_listar.php?acao=buscar&pesquisapor=titulo&pesquisa=' + item.titulo_negocio :
              // Valor padrão se nenhuma condição for atendida
              ''
          ),
         });
        });
        var results = Object.values(groups);


        var resultsArray = [];

        // Crie a estrutura HTML
        $('#paginas-exibicao__linha__input__resultado_topo').empty();
        results.forEach(function (group) {

         var groupHtml = $('<div class="select2-results__option select2-results__option--group" aria-label="' + group.text + '"><span class="select2-results__group">' + group.text + '</span></div>');

         group.children.forEach(function (child) {
          var childHtml = $('<a href="' + child.link + '" class="select2-results__option select2-results__option--selectable" ' + (child.exibir_imovel == 'N' ? 'style="color: #4d4d4d94;"' : '') + ' data-campo_encontrado="' + child.pesquisa_por + '" data-codigo_imovel="' + child.codigo_imovel + '" data-codigo_proprietario="' + child.codigo_proprietario + '" data-link="' + child.link + '" role="option">' + child.text + '</a>');
          groupHtml.append(childHtml);

          resultsArray.push({
           campo_encontrado: child.campo_encontrado,
           codigo_imovel: child.codigo_imovel,
           codigo_proprietario: child.codigo_proprietario,
           link: child.link,
           text: child.text
          });
         });
         
         $('#paginas-exibicao__linha__input__resultado_topo').append(groupHtml);


        });



        $('#campos_busca_gerados').val(resultsArray[0]['link']);
       }
      },
      error: function (error) {
       console.error('Erro na chamada AJAX:', error);
      }
     });
    }

   }, 1200);


  });

    function nomeGrupo(nome) {

        return nome;
    }

  var searchBtn = document.querySelector('.search-btn');

  if (searchBtn) {
   searchBtn.addEventListener('click', function () {
    var valueInput = document.getElementById('nova_busca_aprimorada_busca_ajax').value
    if (valueInput.length > 2) {
     if ($('#campos_busca_gerados').val() != '') {
         window.location.href = $('#campos_busca_gerados').val()
     } else {
      alert('Seja mais específico na busca')
     }
    }
   });
  }

  var inputResultado = document.getElementById('paginas-exibicao__linha__input__resultado_topo');
  var campos_busca_gerados = document.getElementById('campos_busca_gerados');

  if (inputResultado) {
   document.addEventListener('click', function (event) {
    if (!inputResultado.contains(event.target)) {
     if (campos_busca_gerados) {
      campos_busca_gerados.value = ''
     }
     $('#paginas-exibicao__linha__input__resultado_topo').empty();
    }
   });
  }

//   $(document).on('click', '#paginas-exibicao__linha__input__resultado_topo > div > .select2-results__option--selectable', function () {

//    let dadoSelecionado = $(this).text();
//    let linkSelecionado = $(this).attr('data-link');

//    window.location.href = linkSelecionado
//   });

  document.getElementById('nova_busca_aprimorada_busca_ajax').addEventListener('keydown', function (event) {
   if (event.key === 'Enter') {
    event.preventDefault(); // Evita que a tecla Enter realize a ação padrão (como submeter um formulário)
    var inputValue = this.value;
    if (inputValue.length > 1) {
        if ($('#campos_busca_gerados').val() != '') {
            window.location.href = $('#campos_busca_gerados').val()
        }
    }
   }
  });

  if (window.innerWidth < 780) {
   var elementoParaRemover = document.querySelector('.busca_aprimorada_li_botao');
   if (elementoParaRemover) {
    elementoParaRemover.remove();
   }
  }

  document.addEventListener('keydown', function (event) {
   // Verifica se a tecla pressionada é Ctrl + Espaço
   if (event.ctrlKey && event.code === 'Space') {
    // Verifica se nenhum input ou textarea está atualmente focado
    var activeElement = document.activeElement;
    if (activeElement.tagName !== 'INPUT' && activeElement.tagName !== 'TEXTAREA') {
     // Dá foco ao input desejado
     var inputElement = document.getElementById('nova_busca_aprimorada_busca_ajax');
     if (inputElement) {
      inputElement.focus();
     }
    }
   }
  });
 })