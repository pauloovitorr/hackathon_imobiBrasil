$(document).ready(function(){
        // nav mobile
        $(".nav__togglemobile").on("click", function() {
            if ($(".nav__mobile").hasClass("navmobileshow")) {
                $(".nav__mobile").removeClass("navmobileshow");
                $(".nav__mobile").addClass("navmobilehidden");
            } else {
                $(".nav__mobile").addClass("navmobileshow");
                $(".nav__mobile").removeClass("navmobilehidden");
            }
        });
    
        $(".nav__mobile__close").on("click", function() {
            $(".nav__mobile").removeClass("navmobileshow");
            $(".nav__mobile").addClass("navmobilehidden");
        });
    
        $(".nav__mobile__link__dropdown").on("click", function() {
            
            let content = $(this).find(".nav__mobile__link__dropdown__content");
            let icone = $(this).find(".fa-chevron-right");
    
            if ($(content).hasClass("dropvisible")) {
                $(content).removeClass("dropvisible")
                $(icone).css("transform","rotate(0deg)")
            }else {
                $(content).addClass("dropvisible")
                $(icone).css("transform","rotate(90deg)")
            }
        });
        // end nav mobile
});

function toggle_visibility(id) {
    var e = document.getElementById(id);

    if (e.style.display == 'block'){
        e.style.display = 'none';
    }else{
        e.style.display = 'block';
    }
}

function googleTranslateElementInit() {
    new google.translate.TranslateElement({pageLanguage: 'pt', includedLanguages: 'en,es,pt,fr,it',layout: google.translate.TranslateElement.InlineLayout.SIMPLE}, 'google_translate_element');
}