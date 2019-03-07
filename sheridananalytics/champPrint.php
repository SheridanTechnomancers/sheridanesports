<?php
    sessions_start();

if(empty($_SESSION['uname'])){
	header("index.php/");
}

elseif(!empty($_SESSION['uname'])){
    $champ1 = $_SESSION['firstChamp'];
    print_r($champ1);
    echo"
    <p>
        Champ 1: 


    
";}

?>
