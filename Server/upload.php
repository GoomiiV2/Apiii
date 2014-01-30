<?php
	header('Content-type: application/json');
	include("notepad++AutoComplete.php");
	
	// Consts
	$SecretKey = ""; //Needs to match in the addon and Addii
	$ADDII_URL = "http://109.169.89.73:8081"; // The url for the Addii IRC bot

	// Get the data
	$entityBody = json_decode(file_get_contents('php://input'), true);

    if ($SecretKey == $key)
    {

        // Check if this is the last one
        if (isset($entityBody[0]) && $entityBody[0] == "END")
        {
            $file1 = "inprogress.json";
            $temp = json_decode(file_get_contents($file1), true);
            unlink($file1);
            $file2 = "done.json";
            file_put_contents($file2, json_encode($temp, JSON_PRETTY_PRINT));

            ProcessApiData($temp);
        }
        else // Add this chunk
        {
            $file = "inprogress.json";
            if (file_exists($file))
            {
                $temp = json_decode(file_get_contents($file), true);
                $temp = array_merge($temp, $entityBody);
                file_put_contents($file, json_encode($temp, JSON_PRETTY_PRINT));
            }
            else
            {
                file_put_contents($file, json_encode($entityBody, JSON_PRETTY_PRINT));
            }
        }
    }
	
	function ProcessApiData($data)
	{
		global $SecretKey;
		
		$key = $data["info"]["key"];
		$gameVer = $data["info"]["ver"];
		$gameEnv = $data["info"]["env"];
		$gameLevel = $data["info"]["level"];
			
		$ver = $gameVer.":".$gameLevel;
		$fileVer = $gameVer."_".$gameLevel;

		unset($data["info"]["key"]);
			
        // Check if it is a newer version
        if (strcmp(GetVersion($gameEnv), $ver))
        {
            SaveApi($gameEnv, $data, $fileVer);
            SaveVersion($ver, $gameEnv);

            unset($data["info"]);
            // Generate any docs that need be
            GenrateNppAutoComplete($gameEnv, $data);

            // Addii
            TellAddii($gameEnv, $ver, $SecretKey);
        }
	}
	function SaveVersion($ver, $env)
	{
		$file = "version-".$env.".txt";
		file_put_contents($file, $ver);
	}
	
	function GetVersion($env)
	{
		return file_get_contents("version-".$env.".txt");
	}
	
	function SaveApi($env, $api, $vera)
	{
		// Current
		$file = "assests/".$env."/api-".$env.".json";
		file_put_contents($file, json_encode($api, JSON_PRETTY_PRINT));
		
		// For history
		$file2 = "assests/".$env."/api-".$env."-". $vera .".json";
		file_put_contents($file2, json_encode($api, JSON_PRETTY_PRINT));
	}
	
	function TellAddii($env, $ver, $Secret)
	{
        global $ADDII_URL:

		$url = $ADDII_URL.'/pts_api_update';
		$envNice = "pts";
		if ($env == "production")
		{
			$url = $ADDII_URL.'/live_api_update';
			$envNice = "live";
		}
			
		$myvars = 'env=' . $envNice . '&ver=' . $ver."&secert=". urlencode($Secret);

		$ch = curl_init( $url );
		curl_setopt( $ch, CURLOPT_POST, 1);
		curl_setopt( $ch, CURLOPT_POSTFIELDS, $myvars);
		curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt( $ch, CURLOPT_HEADER, 1);
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1);

		$response = curl_exec( $ch );
}
?>