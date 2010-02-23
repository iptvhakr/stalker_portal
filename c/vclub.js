/**
 * Video Club modile.
 */

//(function(){
    
    var genres = ['ИНФОРМАЦИОННЫЙ',
            'РАЗВЛЕЧЕНИЯ',
            'ДЕТСКИЙ',
            'КИНО',
            'НАУКА',
            'СПОРТ',
            'МУЗЫКА',
            'БИЗНЕС',
            'КУЛЬТУРА',
            'НАШЕ',
            'ЗАРУБЕЖНОЕ']
    
    var vclub_constructor = function(){
        
    };
    
    vclub_constructor.prototype = new Layer();
    
    var vclub = new vclub_constructor();  
    
    vclub.init();
    vclub.init_sidebar();
    vclub.sidebar.init_items("genre", genres);
    
    
//})()