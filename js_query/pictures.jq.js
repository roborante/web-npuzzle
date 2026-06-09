function upload(  )
{
    $('#uploadImg').click( );
}

function loadImg(submit)
{
    if(submit.files[0].size > 3000000) {alert('maximum size allowed 3 MB'); return;}
    
    let file = new FormData( );
    
    file.append('imgFile',submit.files[0]);
	
    $.ajax({url : 'php/uploadImg.php',
            type: 'POST',
            data:  file ,
                
            contentType: false,
            processData: false,
        
            success: function(response)
            {
                let answer = JSON.parse(response);
                
                if(answer[0]){if  ($('#solve'  ).attr('name') == 'ok' ) reStick(answer[1],true);
                              else{$('#newGame').attr('name',answer[1]);
                                   $('#nums'   ).prop('checked', true );shuffle(              );}}
                            
                else alert(answer[1]); 
            }
    });
}

function reStick(imgRef, showIndex)
{
    let newGame = $('#newGame');
	
    if(imgRef == newGame.attr('name')) return;
	
    newGame.attr('name',imgRef);
	
    let board = currentState(Math.sqrt($('.pos').length));
    if(!board)
    {
        $('#nums').prop('checked',showIndex); 
        shuffle( ); 
        return;
    }
    wait( );
	
    let tiles = $('.tile');
	
    tiles.each(function( )
    {
        let tile =  $(this);
        if (tile.attr('id') != 0) tile.css({'animation':'disappear 423ms 1 ease-out','animationFillMode':'forwards'});
    });
	
    setTimeout(function( )
    {
        let folder = getFolder(      );
        let suffix = fileType (folder);
            
        tiles.each(function( )
        {
            let tile = $(this);
            let id   = tile.attr('id');
            if (id!=0) tile.attr('src',folder+id+suffix).css('animation','appear 423ms 1 ease-in');
            else       tile.attr('src','layout/tiles/0.gif');
        });
        
        setTimeout(function( )
        {
            $('.cover').remove(  );
        }
        ,846);
    }
    ,423);
	
    checkLabels(showIndex);
}

function getFolder( )
{
    let imgRef = $('#newGame').attr('name');
	
    if     (imgRef ==     'layout/tiles/' ){$('#noImg' ).prop('checked',true); return imgRef;}
    else if(imgRef.search('tempTiles') > 0) $('#custom').prop('checked',true);
    else                                    $('#random').prop('checked',true);
    
    return $('#to8').is(':checked') ? imgRef + "tiles8/": imgRef + "tiles15/";
}

function fileType(folder)
{
    return folder == "layout/tiles/" ? ".gif" : ".jpeg";
}