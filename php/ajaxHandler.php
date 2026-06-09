<?php 

ini_set('display_errors', '0');

require __DIR__ . '/config.php';
require __DIR__ . '/nPuzzle.php';

if(isset($_POST['newBoard']))
{
	$puzzle = new SlidePuzzle($_POST['newBoard']);
	$node   = json_encode($puzzle->board);
	$datab  = getPuzzleDatabase();

	$stmt = count($puzzle->board) < 4 ? $datab->prepare("INSERT INTO  `8puzzle` (node, counter) VALUES (?, 1) ON DUPLICATE KEY UPDATE counter=counter+1")
									  :	$datab->prepare("INSERT INTO `15puzzle` (node, counter) VALUES (?, 1) ON DUPLICATE KEY UPDATE counter=counter+1");
	$stmt->bind_param('s', $node);
	$stmt->execute();
	$stmt->close();
	
	echo json_encode($puzzle->board);
	
	unset($_POST['newBoard']);
	unset($puzzle);
    exit;
}

else if(isset($_POST['check']))
{
	$permutation = decodeBoard($_POST['check']);
	
	if(SlidePuzzle::solvable($permutation,sqrt(count($permutation)) % 2 != 0)) echo json_encode(array(true ));
	else                                                                       echo json_encode(array(false));
	
	unset($_POST['check']);
    exit;
}

else if(isset($_POST['solve']))
{
	$puzzle = new SlidePuzzle(null,decodeBoard($_POST['solve']));

	echo json_encode($puzzle->solve( ));
	
	unset($_POST['solve']);
	unset($puzzle);
    exit;
}

else if(isset($_POST['query']))
{
	$puzzle = new SlidePuzzle(null,decodeBoard($_POST['query']));
	
	$node = json_encode($puzzle->board);
	$table = count($puzzle->board) == 3 ? '8puzzle' : '15puzzle';

	$datab = getPuzzleDatabase();

	$stmt = $table == '8puzzle' ? $datab->prepare("INSERT IGNORE INTO  `8puzzle` (node) VALUES (?)")
								: $datab->prepare("INSERT IGNORE INTO `15puzzle` (node) VALUES (?)");
	$stmt->bind_param('s',$node);
	$stmt->execute();
	$stmt->close();

	$stmt = $table == '8puzzle' ? $datab->prepare("SELECT counter, solution, moves_to_goal FROM  `8puzzle` WHERE node = ?")
								: $datab->prepare("SELECT counter, solution, moves_to_goal FROM `15puzzle` WHERE node = ?");
	$stmt->bind_param('s',$node);
	$stmt->execute();
	$result  = $stmt->get_result();
	$query   = $result->fetch_array(MYSQLI_ASSOC);
	$counter = intval($query['counter']);
	$stmt->close();

	$moves = intval($query['moves_to_goal']);

	echo json_encode(array($counter, intval($moves)));
	
	unset($_POST['query']);
	unset($puzzle);
    exit;
}

else if(isset($_POST['heuristic']))
{
	$puzzle = new SlidePuzzle(null,decodeBoard($_POST['heuristic']));
	
	echo json_encode(array($puzzle->heuristic($puzzle->board)));
	
	unset($_POST['heuristic']);
	unset($puzzle);
    exit;
}

else if(isset($_POST['randomImg']))
{
	$imgPoolPath = UPLOAD_DIR . 'imgPool';
	if (!is_dir($imgPoolPath)) {
		@mkdir($imgPoolPath, 0755, true);
	}
	
	$images = scandir($imgPoolPath);
	$items  = sizeof($images);
	$pick   = '_';
	
	while(strlen($pick) < 32 || ($pick == $_POST['current'] && $items > 3)) 
	{
		$pick = $images[rand(2,$items-1)];
	}
    
    echo 'pictures/imgPool/'.$pick.'/';
    
    unset($images);
    unset($pick);
    unset($_POST['randomImg']);
    exit;
}

function decodeBoard($boardStr) : array
{
	$boardStr = explode(',',$boardStr);
	$perm     = array  (             );
	
	foreach($boardStr as $tile)  
	{
		$perm[] = intval($tile);
	}
	
	return $perm;
}

exit;

?>