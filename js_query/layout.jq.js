function firstCheck( )
{
    $('#noImg').prop('checked',true );
    $('#nums' ).prop('checked',false);
    $('#to8'  ).prop('checked',true );
	
    $(document).on('keydown',function(event) 
    {
        if(!$('.cover').length)
            
        switch(event.which)
        {
            case 37: swap_with_key('left' ); break; 
            case 38: swap_with_key('up'   ); break; 
            case 39: swap_with_key('right'); break;
            case 40: swap_with_key('down' ); break;
                
            case  9: if($('#to8').is(':checked')) {$('#to15').prop('checked',true ); switchTo(16);}
                     else                         {$('#to8' ).prop('checked',true ); switchTo( 9);} break;
                
            case 76: $('#nums').prop('checked',!$('#nums').is(':checked')); tileLabs(-1); break;
                
            case 32: shuffle     (                     ); break;
            case 67: upload      (                     ); break;
            case 78: reStick     ('layout/tiles/',false); break;
            case 83: findSolution(                     );
        }
    });
}

function resize( )
{
    let width  = $(window).width ( );
    let height = $(window).height( );
	
    switch($('.pos').length)
    {
        case  9: var ratio = 0.222 ; var size = 3; break;
        default: var ratio = 0.1665; var size = 4; 
    }
    
    if(width > height){var sizeT = height*ratio; var sizeB = parseInt(height*0.111);}
    else              {var sizeT = width *ratio; var sizeB = parseInt(width *0.111);}
	
    let boardWidth    = parseInt(sizeT*size);
    let left          = parseInt((width -boardWidth)*0.5);
    let top           = parseInt((height-boardWidth)*0.5);
    let marginTopSide = parseInt( height*0.5)-40;
    let sizeBfix      = parseInt( sizeB*0.89);
    
    $('#board').css({'top'   : top       ,
                     'left'  : left      ,
                     'width' : boardWidth, 
                     'height': boardWidth});
    
    $('#boardSettings').css('top',marginTopSide);
    $('#imageSettings').css('top',marginTopSide);
    
    for(let x=0; x<size; ++x) 
    for(let y=0; y<size; ++y) 
    {
    	$('#pos'+ x.toString( )
                + y.toString( )).css({'top'   : parseInt(sizeT*x),
    	                              'left'  : parseInt(sizeT*y),
    	                              'width' : sizeT            ,
    	                              'height': sizeT          });
    }
    
    $('.posB').each(function( )
    {
    	$(this).css({'width' : sizeB,
    	             'height': sizeB});   
    });
    
    $('.buttons').each(function( )
    {
    	$(this).css({'width'   : sizeBfix,
    	             'height'  : sizeBfix,
    	             'fontSize':'4.23vmin'});     
    });

    $('#topleft'    ).css({'left' : left - sizeB     , 'top' : top - sizeB     });
    $('#topright'   ).css({'left' : left + boardWidth, 'top' : top - sizeB     });
    $('#bottomleft' ).css({'left' : left - sizeB     , 'top' : top + boardWidth});
    $('#bottomright').css({'left' : left + boardWidth, 'top' : top + boardWidth});

    $('#top'        ).css({'left'    : left                 ,
                           'top'     : top - sizeB          ,
                           'width'   : boardWidth           ,
                           'height'  : parseInt(sizeB*0.333),
                           'fontSize':'1.886vmin'           });  

    $('#bottom'     ).css({'left'    : left                                  ,
                           'top'     : parseInt(top + boardWidth + sizeB*0.5),
    	                   'height'  : parseInt(                   sizeB*0.5),
    	                   'width'   : boardWidth                            ,
                           'fontSize': '3.246vmin'                         });

    $('#board, .posB, .settings, .stats').css('opacity', 1);
}

function switchTo(size) 
{
    wait( );
	
    let positions    = $('.pos');
    let currentTiles = positions.length;
	
    let to9  = size ==  9 && currentTiles == 16;
    let to16 = size == 16 && currentTiles ==  9;
    
    if(to9 || to16) 
    {
    	positions.css({'animation':'hide 423ms 1 ease-out','animationFillMode':'forwards'});
    	removeTiles( ); 
    	
    	setTimeout(function( ) 
        {
    	    let fourthTile = [               '03',
                                             '13',     
                                             '23',
                              '30','31','32','33'];
		    
    	    for(let k=0; k < 7; ++k) 
            {
                switch(currentTiles)
                {
                    case  9: jQuery('<div/>',{'class':'pos','id':'pos'+fourthTile[k]})
                               .css({'opacity':0,'animation':'show 423ms 1 ease-in','animationFillMode':'forwards'})
                               .appendTo('#board'); 
                             break;
                        
                    default: $('#pos'+fourthTile[k]).remove( );
                }
    	    }
		    
    	    positions.css({'animation':'show 423ms 1 ease-in','animationFillMode':'forwards'});
		    
    	    if($('#solve').attr('name') == 'ok') newBoard   ( ); 
    	    else                                 resize     ( ); 
    	}
    	,423);
    }
    
    setTimeout(function( ){$('.cover').remove( );},846);
}

function placeTiles(board)
{
    wait( );
	
    let folder = getFolder(      );
    let suffix = fileType (folder);
	
    let showNums = $('#nums').is(':checked');
	
    for(let x=0; x<board.length; ++x)
    for(let y=0; y<board.length; ++y)
    {
        let tile  = board[x][y];
    	let index = x.toString( )
                  + y.toString( );
        
        let move  = movable(board,x,y);
    	
        let $img = jQuery('<img/>',{id: tile}).attr('src',tile != 0 ? folder+tile+suffix : 'layout/tiles/0.gif');
        
        if(move) $img.attr({'onclick':'swap(this)','class':'tile movable','name':move}); 
        else     $img.attr({                       'class':'tile locked' ,'name':move});
        
        if(tile != 0) $img.css('animation','appear 423ms 1 ease-in');
        
        $img.appendTo('#pos'+index);
        
        if(tile != 0 && showNums) 
        {
            jQuery('<div/>',{'class':'index','id':'index'+tile}).html(tile).css('animation','appear 423ms 1 ease-in').appendTo('#pos'+index);
        }
    }
	
    setTimeout(function( ){$('.cover').remove( );},618);
}

function removeTiles(onComplete = null)
{
    $('.tile').each(function( )
    {
        let tile = $(this);
            
        if(tile.attr('id' ) != 0 
        || tile.attr('src').search('star') > 0) tile.css({'animation':'disappear 423ms 1 ease-out','animationFillMode':'forwards'});
    });
	
    if($('#nums').is(':checked')) removeIndex( );
	
    setTimeout(function( )
    {
        $('.pos').empty( );

        if(typeof onComplete == 'function') onComplete( );
    }
    ,423);
}

function checkLabels(show)
{
    $('#nums').prop('checked',show); 
	
    tileLabs(show);
}

function tileLabs(show)
{
    if(show < 0) show = $('#nums').is(':checked');
	
    if(show) $('.pos').each(function( )
    {
        let pos  = $(this);
        let tile = pos.children(':first').attr('id');
            
        if(tile != 0 && !$('#index'+tile).length)
        {
            jQuery('<div/>',{'class':'index','id':'index'+tile}).html(tile).css('animation','appear 423ms 1 ease-in').appendTo(pos);
        }
    });
	
    else removeIndex( );
}

function removeIndex( )
{
    var labels = $('.index');
		
    labels.css('animation','hide 618ms 1 ease-out forwards');
		
    setTimeout(function(  ){labels.remove( );},618);
}

function wait( )
{
    jQuery('<div/>',{'class':'cover','id':'coverAll'}).appendTo('#body');
}

function focusOff(element)
{
    if(element.blur) element.blur( );
}