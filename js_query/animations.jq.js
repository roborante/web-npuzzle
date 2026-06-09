function checkPermutation( )
{
    jQuery('<div/>',{'class':'cover','id':'coverBoard'                           }).appendTo('#board'     );
    jQuery('<img/>',{'class':'gear' ,'id':'gearCheck','src':'layout/img/gear.png'}).appendTo('#coverBoard');
	
    wait( );
}

function slide(tile,towards)
{
    switch(towards)
    {
        case 'up'   : $('#'+tile).css('animation','slideUp    423ms 1 ease-in-out'); break;
        case 'down' : $('#'+tile).css('animation','slideDown  423ms 1 ease-in-out'); break;
        case 'left' : $('#'+tile).css('animation','slideLeft  423ms 1 ease-in-out'); break;
        case 'right': $('#'+tile).css('animation','slideRight 423ms 1 ease-in-out'); break;
    }
}

function search_starts( )
{
    jQuery('<div/>',{'class':'cover','id':'coverBoard'                       }).appendTo('#board'     );
    jQuery('<img/>',{'class':'gear' ,'id':'gear1','src':'layout/img/gear.png'}).appendTo('#coverBoard');
    jQuery('<img/>',{'class':'gear' ,'id':'gear2','src':'layout/img/gear.png'}).appendTo('#coverBoard');
	
    setTimeout(function( ){$('#gear1').css('animation',       'clockwise 1886ms infinite linear');
    			           $('#gear2').css('animation','counterclockwise 1886ms infinite linear');},618);
    wait( );
}

function search_is_over( )
{
    $('#gear1').css({'animation':'disappear 423ms 1 ease-out','animationFillMode':'forwards'});
    $('#gear2').css({'animation':'disappear 423ms 1 ease-out','animationFillMode':'forwards'});
	
    setTimeout(function( ){ $('.gear').remove( ); },423);
}

function congratulations( )
{
    let cover = $('#coverBoard');
    if(!cover.length)
    {
	    jQuery('<div/>',{'class':'cover','id':'coverBoard'}).appendTo('#board');
    }
    jQuery('<img/>',{'class':'gearFinal','id':'gearBk','src':'layout/img/happy.png'   }).appendTo('#coverBoard');
    jQuery('<img/>',{'class':'gearFinal','id':'gearHy','src':'layout/img/happy.png'   }).appendTo('#coverBoard');
    jQuery('<img/>',{'class':'thumb'    ,'id':'thumbR','src':'layout/img/thumbupR.png'}).appendTo('#coverBoard');
    jQuery('<img/>',{'class':'thumb'    ,'id':'thumbL','src':'layout/img/thumbupL.png'}).appendTo('#coverBoard');
    
    if(!$('#coverAll').length) wait( );
	
    setTimeout(function( )
    { 
        $('#gearBk').css('animation',     'clockwise 618ms infinite linear'     );
        $('#gearHy').css('animation',    'oscillator 886ms infinite ease-in-out');
        $('#thumbL').css('animation', 'thumbLeftMove 886ms infinite ease-in-out'); 
        $('#thumbR').css('animation','thumbRightMove 886ms infinite ease-in-out'); 
                                
        setTimeout(function( )
        {
            $('#gearBk').css('animation','disappear 618ms 1 ease-in');
            $('#gearHy').css('animation','disappear 618ms 1 ease-in');
            $('#thumbL').css('animation','disappear 618ms 1 ease-in'); 
            $('#thumbR').css('animation','disappear 618ms 1 ease-in');
                
            setTimeout(function( )
            {
                $('.gearFinal').remove( );
                $('.thumb'    ).remove( );
                $('.cover'    ).remove( );
            }
            ,612);
        }
        ,2658)
    }
    ,618);
}