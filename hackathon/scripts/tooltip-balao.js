$( function() {
    
        var boxCriada = false;
        $('body').on('mousemove','.balao', function(evento){
            let $nota = $(this);

            let texto = $nota.attr('data-balao') || '';
            let largura = $nota.attr('data-largura') ? $nota.attr('data-largura') : '160';
            let posicao = $nota.attr('data-posicao');
            let cor = $nota.attr('data-cor');
            
            let mouseX = evento.pageX;
            let mouseY = evento.pageY;
            let top = '';
            let left = '';
            
            switch(posicao){
                case 'acima':
                    top = 50;
                    left = 10;
                break;   
                case 'abaixo':
                    top = -20;
                    left = 10;
                break;   
                case 'esquerda':
                    top = 20;
                    left = -20 - largura;
                break;       
                case 'fixo':
                    top = 30;
                    mouseX = $nota.position().left;
                    mouseY = $nota.position().top;
                    left = 20 - largura/2;
                break;    
                default:
                    top = 20;
                    left = 20;    
            }
            
            if(!boxCriada && texto ){
                boxCriada = true;
                $('body').append(`<span id="balaoCard" class="tooltip-balao" style="position:absolute;left:${mouseX+left};top:${mouseY+top};display:none;width:${largura}px;" >${texto}</span>`);
                
                let altura = $('#baladoCard').css('height')
                
                if(altura > 40)                
                    console.log($('#balaoCard').css('height'))
                $('#balaoCard').fadeIn(400);
            }
                $('#balaoCard').css({left : mouseX+left, top : mouseY-top});
        })

        //esconde tag ao retirar mouse
        $('body').on('mouseout','.balao', function(evento){
            $('#balaoCard').remove();
            boxCriada = false;        
        });
})