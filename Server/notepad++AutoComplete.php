<?php
	function GenrateNppAutoComplete($env, $data)
	{
		$file = "assests/". $env ."/npp/lua.xml";
		
		$str = '<?xml version="1.0" encoding="Windows-1252" ?>'.PHP_EOL
		.'<NotepadPlus>'.PHP_EOL
		.'	<AutoComplete language="LUA">'.PHP_EOL
		.'		<Environment ignoreCase="no" startFunc="(" stopFunc=")" paramSeparator="," terminal=";" additionalWordChar = "."/>'.PHP_EOL;

        // Well this got messy :<
		foreach ($data as $key => $value)
		{
			foreach ($value as $k => $val)
			{
                PrintFunctionDef($k, $val);
			}
		}
		
		// Now add the base lua funcs and stuff
		$str = $str . file_get_contents("stuff/nppLuaBase.txt");
		
		$str = $str.'	</AutoComplete>'.PHP_EOL
		.'</NotepadPlus>'.PHP_EOL;
		
		file_put_contents($file, $str);
	}

    function PrintFunctionDef($key, $k, $val)
    {
        if (is_array($val))
        {
            if (array_key_exists("usageString", $val))
                $desc = $val["usageString"];
            if (array_key_exists("returnVal", $val))
                $retVal = $val["returnVal"];

            $str = $str. '		<KeyWord name="'.$key.".".$k.'" func="yes">'.PHP_EOL;



            PrintArgs($val);

            if ($desc || $retVal)
                $str = $str. '			</Overload>'.PHP_EOL;

            $str = $str. '		</KeyWord>'.PHP_EOL;

            $desc = null;
            $retVal = null;
        }
        else
        {
            $str = $str. '		<KeyWord name="'.$key.".".$val.'"/>'.PHP_EOL;
        }
    }

function PrintOverLoad()
{
    if ($desc && $retVal)
        $str = $str. '			<Overload retVal="'.$retVal.'" descr="'.$desc.'">'.PHP_EOL;
    elseif ($desc)
        $str = $str. '			<Overload retVal="" descr="'.$desc.'">'.PHP_EOL;
    elseif ($retVal)
        $str = $str. '			<Overload retVal="'.$retVal.'" descr="">'.PHP_EOL;
}

function PrintArgs($val)
{
    if (array_key_exists("args", $val))
        foreach ($val["args"] as $arg)
        {
            $str = $str. '				<Param name="'.$arg.'"/>'.PHP_EOL;
        }
}
?>