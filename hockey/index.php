<?php

    function getWinProb(){
        # Simulate 21 players with 15 to 100 as frequency to score a goal
        # This makes the probability of scoring to range from 0.15 to 1
        # Return sthe the proability to win for the team.
        $playerScoreProbs = range(15, 100);
        shuffle($playerScoreProbs);
        $playerScoreProbs = array_slice($playerScoreProbs ,0,20);
        $teamWinProb = array_sum($playerScoreProbs) / count($playerScoreProbs);
        return($teamWinProb);
    }

    # function to run the game for a single group of two teams
    function getgameRes($h,$a){
        # take teams as arguement, h for home and a for away
        # initialize scores for home and away team
        $hscore = 0;
        $ascore =0;
        while ($hscore < 4 & $ascore <4) {
            $wh = getWinProb();
            $wa = getWinProb();
            ($wh >= $wa) ? $hscore++ :$ascore++;
        }
        # build and return the winners info
        if ($hscore >= $ascore) {
            $gameRes = array('winner' => $h, 'wins' => $hscore, 'loss' => $ascore);
        }else {
            $gameRes = array('winner' => $a, 'wins' => $ascore, 'loss' => $hscore);
        }
        return $gameRes;
    }

 # print_r(getgameRes("A","B"));

    # function to run all the game for a single round
    function getDivRes($div){
        # length of team must be greater than 2 and even
        $teams= range("A","H");
        shuffle($teams);
        $divRes = array();
        $round=1;
        # repeat round until one winner
        while (count($teams) >= 2) {
            # create group out of list of teams
            $groups = array_chunk($teams, 2);
            $teams = array();
            # play the game within in each group to get the winner
            foreach($groups as $group){
                $gameRes = getgameRes($group[0],$group[1]);
                $group = array("div"=>$div,"round"=>$round,"home" => $group[0],"away"=>$group[1]);
                $teams[]= $gameRes['winner'];
                $divRes[]= array_merge($group,$gameRes);
            }
            $round++;
        }
        return $divRes;
    }

    # Run the round for each division Final game
    $est=getDivRes("East");
    $west=getDivRes("West");
    # Run final game between the winners
    $finalRes=getgameRes($est[6]['winner'],$west[6]['winner']);
    # add division and round
    $group = array("div"=>"Both","round"=>4,"home" => $est[6]['winner'],"away"=>$west[6]['winner']);
    $finalRes= array_merge($group,$finalRes);
    # merge result for east, west and final
    $finalRes=array_merge($est,$west,array($finalRes));

    # Display result as html table
    # table header
    $htmldata='<tr><th>div</th><th>round</th><th> home</th><th> away</th><th>winner</th><th>wins</th><th>loss</th></tr>';
    # table content
    foreach($finalRes as $data){
        $htmldata.='<tr><td>'.$data['div'].'</td><td>'.$data['round'].'</td><td>'.$data['home'].'</td><td>'.$data['away'].'</td><td>'.$data['winner'].'</td><td>'.$data['wins'].'</td><td>'.$data['loss'].'</td></tr>';
    }
    # show the table
    echo '<table border=0 colspan=0>'.$htmldata.'</table>';


    #datastructure
    echo "<pre>";
    print_r($finalRes) ;
    echo "</pre>";

    /*
    $finalRes=
    $leagueRes=array_merge($est,$west,$finalRes);

    # run all games for all rounds within a division
    function getDivRes($name){
        #first round
        # intial 8 teams are labeled A to H
        $teams= range("A","H");
        $round1Res=getRoundRes($teams,0,$name);

        # Second round
        # team are winners from 1st round
        $teams= $round1Res['winners'];
        $round2Res=getRoundRes($teams,1,$name);

        # Third round
        # team are winners from 2nd round
        $teams= $round2Res['winners'];
        $round3Res=getRoundRes($teams,2,$name);

        # Combine all result from rounds
        $divRes = array($round1Res['Games'],$round2Res['Games'], $round3Res['Games']);
        $divRes = array_merge(...$divRes);
        return $divRes;
    }
    # Final game
    $est=getDivRes("East");
    $west=getDivRes("West");
    $teams=[$est[6]['winner'],$west[6]['winner']];
    $finalRes=getRoundRes($teams,"Final","Final")['Games'];
    $leagueRes=array_merge($est,$west,$finalRes);

    # Display result as html table
    $htmldata='<table border=0 colspan="0"><tr><th>div</th><th>round</th><th> home</th><th> away</th><th>winner</th><th>wins</th><th>loss</th></tr>';

    foreach($leagueRes as $data){
        $htmldata.='<tr><td>'.$data["div"].'</td><td>'.$data["round"].'</td><td>'.$data["home"].'</td><td>'.$data["away"].'</td><td>'.$data["winner"].'</td><td>'.$data["wins"].'</td><td>'.$data['loss'].'</td></tr>';
    }
    echo $htmldata.'</table>';




*/


 ?>