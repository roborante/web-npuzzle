<?php

class SlidePuzzle 
{
	public  $board;
	private $goal , $goalXY, $size;
	
	function __construct($size = null, $perm = null)
	{
		$goOn = false;
		
		if(isset($perm))
		{
			$temp = $perm; sort($temp);
			
			switch(count($perm))
			{
				case  9: $this->size = 3; $goOn = $temp == range(0, 8) && $this->solvable($perm,true ); break;
				case 16: $this->size = 4; $goOn = $temp == range(0,15) && $this->solvable($perm,false); break;
			}
			
			$this->board = $goOn ? $this->toMatrix($perm) : null;
		}
		
		else if($size == 3 
			 || $size == 4)
		{
			$this->size  = $size;
			$this->board = $this->generate(true);
			
			$goOn = true;
		}
			
		if($goOn)
		{
			$this->goal   = $this->generate (false);
		    $this->goalXY = $this->matrix_XY($this->goal);
		}
	}

	function __destruct( )
	{
		unset($this->board );
		unset($this->size  );
		unset($this->goal  );
		unset($this->goalXY);
		exit;
	}

	private function toMatrix($perm) : array
	{
		$toReturn = array( );
	    $l        = 0; 
	    
	    for($j=0; $j < $this->size; ++$j) 
	    for($k=0; $k < $this->size; ++$k)
	    {
	        $toReturn[$j][$k] = $perm[$l];
	        $l++;
	    }
	    
	    return $toReturn;
	}
	
	private function generate($toSolve) : array
    {
		$max  = pow  ($this->size,2)-1;
        $temp = range(       0,$max)  ; 
    
        if($toSolve)
        { 
            $odd = $this->size % 2 != 0;
        	do
			{
				shuffle($temp);
			} 
			while(!$this->solvable($temp,$odd));
        }
    
        $toReturn = array(array( ));
        $l        = 1; 
    
        for($j=0; $j < $this->size; ++$j) 
            for($k=0; $k < $this->size; ++$k)
            {
                switch($toSolve)
                {
                    case true:               $toReturn[$j][$k] = $temp[$l-1]; break;
                    default  : if($l > $max) $toReturn[$j][$k] = BLANK      ;
                               else          $toReturn[$j][$k] = $l         ;
                }
                $l++;
            }
    
        return $toReturn;
    }
    
    public static function solvable($state, $odd) : bool
    {
        $tiles = count($state);
        $inversions = 0;
	
	    for($j = 0; $j < $tiles-1; ++$j)
        {
    	    if(1 < $state[$j])
    	    {
    		    for($k = $j+1; $k < $tiles; ++$k)
    		    
    			    if($state[$k] != BLANK && $state[$j] > $state[$k]) $inversions++;
    	    }
        }
        
        switch($odd)
        { 
            case true:                                                                 return($inversions % 2 == 0);
            default  : switch(SlidePuzzle::blankEven($state,sqrt($tiles))){ case true: return($inversions % 2 != 0);
                                                                            default  : return($inversions % 2 == 0); }
        }
    }
    
    private static function blankEven($state, $size) : bool
    {
        $index = 0;
    
        for($j=0; $j < $size; ++$j)
        for($k=0; $k < $size; ++$k)
        {
           	if($state[$index] == BLANK) return !($j % 2);
                
            $index++;
        }
		return true;
    }
	
	private function get_xy($what, $where, &$x, &$y) : void
	{
		for($j=0;$j<$this->size;++$j)
	    for($k=0;$k<$this->size;++$k)
	            
	        if($where[$j][$k] == $what) 
	        { 
	            $x = $j;
	            $y = $k;
	            
	            break;
	        }
	}
	
	private function matrix_XY($matrix) : array
	{
	    $xy = array( );
	    
	    for($x=0; $x < $this->size; ++$x)
	        for($y=0; $y < $this->size; ++$y)
	        {
	            $xy[$matrix[$x][$y]] = array('x' => $x,'y' => $y);
	        }
	    
	    return $xy;
	}
	
	private function expand($parent) : array
	{
		$children = array( );
		
		$x = $parent['x'];
		$y = $parent['y'];
		
		if($x > 0 && $parent['state'][$x-1][$y] != $parent['move']) // up
        {
            $child         = $parent;
            $child['move'] = $parent['state'][$x-1][$y];
        	
        	$child['state'][$x  ][$y] = $child['move'];
            $child['state'][$x-1][$y] = BLANK;
            
            $child['x'] = $x-1;
            $children[] = $child;
        }
         
        if($y > 0 && $parent['state'][$x][$y-1] != $parent['move']) // left
        {
            $child         = $parent;
            $child['move'] = $parent['state'][$x][$y-1];
        	
        	$child['state'][$x][$y  ] = $child['move'];
            $child['state'][$x][$y-1] = BLANK;
            
            $child['y'] = $y-1;
            $children[] = $child;
        }
        
        if($x < $this->size-1 && $parent['state'][$x+1][$y] != $parent['move']) // down
        {
            $child         = $parent;
            $child['move'] = $parent['state'][$x+1][$y];
        	
        	$child['state'][$x  ][$y] = $child['move'];
            $child['state'][$x+1][$y] = BLANK;
            
            $child['x'] = $x+1;
            $children[] = $child;
        }
        
        if($y < $this->size-1 && $parent['state'][$x][$y+1] != $parent['move']) // right
        {
            $child         = $parent;
            $child['move'] = $parent['state'][$x][$y+1];
        	
        	$child['state'][$x][$y  ] = $child['move'];
            $child['state'][$x][$y+1] = BLANK;
            
            $child['y'] = $y+1;
            $children[] = $child;
        }
        
        return $children;
	}
	
	public function heuristic($state) : int
	{
	    $score     = 0;
	    $target    = 1;
	    $reset     = $this->size*$this->size;
	    $inter_row = array( );
	    $inter_col = array( );
	    
	    for($x=0;$x<$this->size;++$x)
	    for($y=0;$y<$this->size;++$y)
	    {
	        $tile = $state[$x][$y];
	        
	        if    ($tile != BLANK  )
			switch($tile == $target)
			{
			    case true: $inter_row[$x][$y] = $tile;
			               $inter_col[$y][$x] = $tile; break;
	
			    default  : $score += abs($x - $this->goalXY[$tile]['x'])  // manhattan
						          +  abs($y - $this->goalXY[$tile]['y']); // distance
	
			               $inter_row[$x][$y] = intval(($tile-1) / $this->size) == $x ? $tile : 0;
			               $inter_col[$y][$x] = intval(($tile-1) % $this->size) == $y ? $tile : 0;
			}
	        else { $inter_row[$x][$y] = 0;
	               $inter_col[$y][$x] = 0; }
	
			switch($target+1)
			{
				case $reset: $target = BLANK; break;
				default    : $target++;
			}
	    }
	    
	    for($x=0   ;$x<$this->size  ;++$x)
		for($y=0   ;$y<$this->size-1;++$y)
		for($z=$y+1;$z<$this->size  ;++$z)
		{
			if($inter_row[$x][$z] && $inter_row[$x][$y] > $inter_row[$x][$z]) $score += 2; // linear
		    if($inter_col[$x][$z] && $inter_col[$x][$y] > $inter_col[$x][$z]) $score += 2; // conflict
		}
	    
	    return $score;
	}
	
	public function solve( ) : array
	{
		$datab = getPuzzleDatabase(      );
		$hash  = json_encode($this->board);
		$table = $this->size == 3 ? '8puzzle' : '15puzzle';

		$stmt = $table == '8puzzle' ? $datab->prepare("SELECT solution FROM  8puzzle WHERE node = ?")
									: $datab->prepare("SELECT solution FROM 15puzzle WHERE node = ?");
		$stmt->bind_param('s', $hash);
		$stmt->execute();

		$result = $stmt->get_result();
		$query  = $result->fetch_array(MYSQLI_ASSOC);
		$stmt->close();

		if(isset($query['solution']) && $query['solution'])
		{
			$cached = json_decode($query['solution'], true);
			return is_array($cached)  ?  $cached : array( );
		}

		$path = $this->runIdaStarSolver();
		$this->saveSolution($table, $hash, $path);
		
		return $path;
	}

	private function stateKey($state) : string
	{
		$key = '';

		for($x=0; $x<$this->size; ++$x)
		for($y=0; $y<$this->size; ++$y)
		{
			$key .= $state[$x][$y] . ',';
		}

		return $key;
	}
	
	private function runIdaStarSolver( ) : array
	{
		$x=-1;
		$y=-1; $this->get_xy(BLANK,$this->board,$x,$y);
		
		$nodes = array(array('state' => $this->board,
	                         'move'  => -1,
	                         'x'     => $x,
	                         'y'     => $y));
		
		$bound =       $this->heuristic($this->board);
		$hashT = array($this->stateKey ($this->board) => true);
		$moves = array( );

		while(true)
		{
			$deeper = PHP_INT_MAX;
			if($this->findPath($nodes,$moves,1,$bound,$hashT,$deeper)) return $moves;
			if($deeper == PHP_INT_MAX) return array( );
			$bound  = $deeper;
		}
	}

	private function saveSolution($table, $hash, $path) : void
	{
		$datab    = getPuzzleDatabase();
		$solution = json_encode($path);
		$count    = count($path);

		$stmt = $table == '8puzzle' ? $datab->prepare("INSERT INTO  8puzzle (node, solution, moves_to_goal) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE solution=?, moves_to_goal=?")
								    : $datab->prepare("INSERT INTO 15puzzle (node, solution, moves_to_goal) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE solution=?, moves_to_goal=?");
		
		$stmt->bind_param('ssiss', $hash, $solution, $count, $solution, $count);
		$stmt->execute();
		$stmt->close();
	}
	
	private function findPath(&$nodes, &$moves, $depth, $bound, &$hashT, &$deeper) : bool
	{
		$last = count($nodes)-1;

		foreach($this->expand($nodes[$last]) as $child)
		{
			if($child['state'] == $this->goal) 
			{
				$moves[] = $child['move'];
				return true;
			}
			
			$hash = $this->stateKey($child['state']); 
			
			if(!isset($hashT[$hash]))
			{
				$heur = $this->heuristic($child['state']) + $depth;
				
				if($heur <= $bound)
				{
					$nodes[] = $child;
					$moves[] = $child['move'];
					$hashT[$hash] = true;

					if($this->findPath($nodes,$moves,$depth+1,$bound,$hashT,$deeper)) return true;

					array_pop($nodes);
					array_pop($moves);
					unset($hashT[$hash]);
				}
				
				else if($heur < $deeper) $deeper = $heur;
			}
		}
		
		return false;
	}
	
}

?>