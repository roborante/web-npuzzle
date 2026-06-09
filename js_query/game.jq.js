function newBoard( ) 
{
    newSolvablePerm( ); 
    resize         ( );
}

function shuffle( ) 
{
	removeTiles(newSolvablePerm);
}

function solution_ready(state) 
{
    if(state) $('#solve').css('cursor','pointer'    ).attr({'name':'ok','onclick':'findSolution( )'});
    else      $('#solve').css('cursor','not-allowed').attr( 'name','ko').removeAttr('onclick');            
}

function newSolvablePerm( ) 
{
    let tiles = parseInt(Math.sqrt($('.pos').length));  
	
    $.post('php/ajaxHandler.php',
    		
			{newBoard: tiles},
			
			function(response) 
			{
				let board = JSON.parse(response);
					
				if(heuristic(board,true) < 10) {newSolvablePerm( ); return;}
					
				placeTiles    (board);
				solution_ready(true );
						
				$('#counter').html('moves #');
			});
}

function place_tile(tile) 
{
    let number = $('#moves_to_goal').html( ).substring(26);
	
    $(tile).attr({'src':'/layout/tiles/'+number+'.gif','id':number,'class':'tile locked'}).css('animation','appear 423ms 1 ease-in').removeAttr('onclick');
	
    tileLabs($('#nums').is(':checked'));
	
    let integer = parseInt(number);
    let max     = $('.pos').length;
	
    if (integer < max-1) $('#moves_to_goal').html('set the position for tile '+ (integer+1));
    else 
		permutationCheck(Math.sqrt(max));
}

function permutationCheck(size) 
{
    $('#0').attr('class','tile locked').removeAttr('onclick');
	
    setTimeout(function( )
    {
		$('#moves_to_goal').html('checking the permutation...'); 
			
		checkPermutation(    );
		setTimeout(function( )
		{
			let currentBoard = currentState(size); 
			if(!currentBoard)  isGoal      (size);
			else
			$.post('php/ajaxHandler.php',
						
				  {check: currentBoard.toString( )},
				
				  function(response)
				  {
					  if(JSON.parse(response)[0])
					  {
						  solution_ready(true);
					  }							
					  updateBoard(size,false);
				  });

			$('.gear' ).remove( );
			$('.cover').remove( );
		}
		,1382);
    }
    ,423);
}

function heuristic(currentBoard, getValue = false)
{
    $.post('php/ajaxHandler.php',
    		
    	  {heuristic: currentBoard.toString( )},
    	  
    	  function(response)
    	  {
    	      if(getValue) return JSON.parse(response);
    	  });
}

function findSolution( )
{
    if($('#solve').attr('name') == 'ok')
    {
		let currentBoard = currentState(Math.sqrt($('.pos').length)); 
		if (currentBoard)
		{
			search_starts( );

			let startTime = Date.now( );

			$.post('php/ajaxHandler.php',
							
			{solve: currentBoard.toString( )},
						
			function(response)
			{
				let elapsed   = Date.now( ) - startTime;
				let remaining = Math.max(0, 1618 - elapsed);

				setTimeout(function( )
				{
					search_is_over (                    );
					executeSolution(JSON.parse(response));
				}
				, remaining);
			})
			.fail(function( )
			{
				let elapsed   = Date.now( ) - startTime;
				let remaining = Math.max(0, 1618 - elapsed);

				setTimeout(search_is_over, remaining);
			});
		}
    }
}

function executeSolution(moves, k=0)
{
    let pause = setInterval(move,618); 
	
    function move( )
    {
		if(k < moves.length)
		{
			swap($('#'+moves[k]),true,moves.length-(k+1)); 
			++k;
		}	
		else clearInterval(pause);
    }
}

function swap_with_key(key)
{
    let  tile = $('[name="'+key+'"]'); 
    if  (tile[0]) 
    swap(tile[0]);	
}

function swap(tile, auto = false, remaining_moves = 0)
{
    if(!auto) wait( );
	
    let blank = $('#0'); let blankPos = blank.parent( );
    	tile  = $(tile); let tilePos  = tile .parent( );
	
    tilePos .append(blank);
    blankPos.append(tile );
	
    let from    = tilePos .attr('id').substring(3); let fromX    = from   .substring(0,1); let fromY    = from   .substring(1);
    let towards = blankPos.attr('id').substring(3); let towardsX = towards.substring(0,1); let towardsY = towards.substring(1);
	
    if     (fromX < towardsX) var movement = "up"   ;
    else if(fromY < towardsY) var movement = "right"; 
    else if(fromX > towardsX) var movement = "down" ; 
    else if(fromY > towardsY) var movement = "left" ; 
	
    let id = tile.attr('id');
	
    if($('#nums').is(':checked'))
    {
    	switch(movement)
    	{
    	    case "up"   : $("#index"+id).css({'height':'100%','width':''    }); break;
    	    case "left" : $("#index"+id).css({'height':''    ,'width':'100%'}); break;
    	    case "down" : $("#index"+id).css({'height':'100%','width':''    }); break;
    	    case "right": $("#index"+id).css({'height':''    ,'width':'100%'}); break;
    	}
    	
    	blankPos.append($('#index'+id)); 
    	
    	slide('index'+id,movement);
    }
	
    slide(id,movement);
    
    let counter = $('#counter');
    let moves   = counter.html( ).substring(6);
	
    counter.html(moves == '#' ? 'moves 1' : 'moves '+ (parseInt(moves) + 1));
	
    let goal = updateBoard(Math.sqrt($('.pos').length),auto,remaining_moves);
	
    if(!auto && !goal) setTimeout(function( ) {$('.cover').remove( )} , 423);
}

function updateBoard(size, auto = false, remaining_moves = 0)
{
    let board = currentState(size); 
	
    for(let x=0; x<size; ++x)
    for(let y=0; y<size; ++y)
    {
		let pos  = $('#pos'+ x.toString( )
						   + y.toString( ));
		let tile = pos.children (':first' );
		let move = movable      (board,x,y);
			
		tile.attr('name',move);
			
		if(board && move){tile.attr      ({'onclick':'swap(this)'    ,
									 	   'class'  :'tile movable'});} 
		else             {tile.attr      ( 'class'  ,'tile locked'  );
						  tile.removeAttr( 'onclick'                );}
			
		pos.css('z-index',tile.attr('id') == 0 ? -1 : 'auto');
    }
	
    if(!board) { isGoal(size); return true; }
	
    return false;
}

function isGoal(size)
{
    let folder = getFolder($('#newGame').attr('name'));
	
    $('.tile').attr('class','tile locked').removeAttr('onclick'); 
	
    setTimeout(function( )
    {
		$('#0').attr('src',folder+'star'+fileType(folder)).css('animation','appear 423ms 1 ease-in'); 
			
		removeIndex( );
			
		setTimeout(function( )
		{
			congratulations( );
		}
		,423);
    }
    ,382);
}

function currentState(size)
{
    let board = new Array( ); let k=1; let checkGoal = true;
	
    for(let x=0; x<size; ++x)
    {
		board[x] = new Array( );
	    
		for(let y=0; y<size; ++y)
		{
			board[x][y] = parseInt($('#pos'+ x.toString( ) + y.toString( )).children(':first').attr('id'));
				
			checkGoal = checkGoal && k == board[x][y];
				
			if(x   == size-1
			&& y+1 == size-1) k=0;
			else              ++k;
     	}
    }
	
    return(checkGoal ? false : board);
}

function movable(board, x, y)
{
    if(board){if(x < board.length-1 && board[x+1][y] == 0) return "down" ;
              if(y < board.length-1 && board[x][y+1] == 0) return "right";
              if(x > 0              && board[x-1][y] == 0) return "up"   ; 
              if(y > 0              && board[x][y-1] == 0) return "left" ;}
    
    return false;
}