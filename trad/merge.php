<?php

	$inputs = array('words-de.txt', 'words-ro.txt');
	$output = '../words.txt';
	
	function readWordFile($sFile)
	{
		$aRes = array();
		$aLanguages = array();
		
		$sContent = file_get_contents($sFile);
		$aLines = explode("\n", $sContent);
		
		$first = true;
		
		foreach($aLines as $sLine)
		{
			$sLine = trim($sLine);
			$aElements = explode(',', $sLine);
		
			if($first)
			{
				$first = false;
				for($i = 1 ; $i < count($aElements) ; $i++)
				{
					$aLanguages[] = trim($aElements[$i]);
				}
				continue;
			}

			$key = $aElements[0];
			$r = array();
			for($i = 0 ; $i < count($aLanguages) ; $i++)
			{
				$r[$aLanguages[$i]] = $aElements[$i+1];
			}

			$aRes[$key] = $r;
		}
		return $aRes;
	}
	
	function writeWordFile($file, $set)
	{
		$temp = array_values($set);
		$langs = array_keys(array_shift($temp));
		
		$data = array();
		
		$data[] = 'image,' . implode(',', $langs);
		foreach($set as $image => $trads)
		{
			$orderedTrads = array($image);
			foreach($langs as $lang)
			{
				$orderedTrads[] = $trads[$lang];
			}
			$data[] = implode(',', $orderedTrads);
		}
		
		file_put_contents($file, implode("\n", $data));
	}
	
	$resultSet = array();
	$first = true;
	$keys = array();
	foreach($inputs as $file)
	{
		if($first)
		{
			$first = false;
			$resultSet = readWordFile($file);
			$keys = array_keys($resultSet);
			continue;
		}
		
		$temp = readWordFile($file);
		
		foreach($keys as $key)
		{
			$resultSet[$key] += $temp[$key];
		}
	}
	
	writeWordFile($output, $resultSet);