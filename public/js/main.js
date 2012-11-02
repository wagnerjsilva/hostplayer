/*
 * The player handler object
 */
handlePlayer = {
    addSong:  function (song) {        
        var id = song.closest('tr').attr('id');
        var title = $('#'+id+' .title').html();
        var artist = $('#'+id+' .artist').html();     
     
        playlist.add({
            title:title,
            artist:artist,
            mp3:'/download/index/song/'+id+'.mp3',
            poster: ""
        });  
        
    },
    search : function () {
        var term = $('#searchTerm');        
        $('#searchDialog').dialog('open');
        var table = $('#songList tbody');
        table.empty();
        table.append('<tr class="loading"><td class="centerText" colspan="3"><img src="/media/themes/main/images/ajax.gif" class="ajax_loader"/></td></tr>');
        $.getJSON("/music/search/", {
            term: term.val()
        }, function(music) {
            //$('.loading').remove();
            var list = [];
            var stripe = "";
            for(var i=0;i<music.results.length;i++){ 
                /*
                 * Since we are not including the table header in the calculation
                 * we are missing the first item, so yes, the calculation below should point to odd not even
                 * Tough!
                 */
                stripe = (i % 2 > 0) ? 'even' : '';
                list [i] = '<tr class="'+music.results[i].album+' '+stripe+'" id="'+music.results[i].id+'"><td class="title">'+music.results[i].title+'</td><td class="artist">'+music.results[i].artist+'</td><td class="album">'+music.results[i].album_name+'</td></tr>';
                //$(table).trigger("update");
            }           
             table.empty();
             table.html(list.join(''));

        });
        //$("#songList tr:even").addClass("even");
        //stops form from reloading the page
        return false;
    },
    initPlayer : function () {
        playlist = new jPlayerPlaylist({
            jPlayer: "#jquery_jplayer_1",
            cssSelectorAncestor: "#jp_container_1"
        }, [
        ], {
            swfPath: "/js/jplayer/Jplayer.swf",
            supplied: "mp3",
            wmode: "window"
        });
    },
    deleteHtmlTableRow : function (row) {         
        //removes row
        $( row ).effect( 'highlight', {
            color: '#000', 
            mode: 'hide'
        }, 250, function () {
            //removes row   
            $(row).remove();
            //refresh zebra
            $("#songList tr:even").addClass("even");             
        });      
    },
    refreshLibrary : function () {
        $.ajax({url: '/refresh'});
        alert('Your library will now be refreshed');
    }
};

$(document).ready(function(){
    //hides address bar on android
    if(navigator.userAgent.match(/Android/i)){
        window.scrollTo(0,1);
    }    
    handlePlayer.initPlayer();    
    //clicked in one song so add it   
    $('.title').live('click', function() { 
        //add song uses id
        handlePlayer.addSong($(this));  
        /*
         * We need to know the class of the item
         * However the zebra styling adds a new class to it, so we need to remove it from 
         * all the even items
         */
        $("#songList tr").removeClass("even"); 
        handlePlayer.deleteHtmlTableRow($(this).closest('tr'));        
    });    
    
    //click on the album so add all of its songs
    $('.album').live('click', function() {
        /*
         * We need to know the class of the item
         * However the zebra styling adds a new class to it, so we need to remove it from 
         * all the even items
         */
        $("#songList tr").removeClass("even"); 
        var album = $(this).closest('tr').attr('class');        
        $('#songList').find("."+album+"").each(function(){ 
            handlePlayer.addSong($(this)); 
        }); 
        handlePlayer.deleteHtmlTableRow('.'+album);
    });
    
    $('#searchDialog').dialog({ 
        autoOpen: false,
        modal: false,
        position: 'top',
        minWidth: 320,
        title: 'Music library'
    });    
    
    $('#refreshLibrary').click(function () {
        handlePlayer.refreshLibrary();
    });
        
    //$( "#go" ).button();     
    
    $('#backToTop').click(function() {
        $('html, body').animate({
            scrollTop: 0
        }, 100);
    });   
    
    
});