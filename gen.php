<?php

	$sSourceImage = './images/';
	$sWordFile = './words.txt';
	$sDest = './build/';
	$sDestImage = './build/';
	$sDestXml = './build/xml/';
	$aSupportedLanguages = array('fr', 'en', 'de', 'ro');

	function readWordFile()
	{
		global $sWordFile, $aSupportedLanguages;

		$res = array();

		$sContent = file_get_contents($sWordFile);
		$aLines = explode("\n", $sContent);
		array_shift($aLines);
		foreach($aLines as $sLine)
		{
			$sLine = trim($sLine);
			$aElements = explode(',', $sLine);

			$key = $aElements[0];
			$r = array();
			for($i = 0 ; $i < count($aSupportedLanguages) ; $i++)
			{
				$r[$aSupportedLanguages[$i]] = $aElements[$i+1];
			}

			$res[$key] = $r;
		}

		return $res;
	}

	$aWordIndex = readWordFile();


	function rmrf($path) {

		$path = rtrim($path, '/');

		if(file_exists($path))
		{
			if(is_dir($path))
			{
				$rDir = opendir($path);
				while(($file = readdir($rDir)) !== false)
				{
					if($file != '.' && $file != '..')
					{
						$subPath = $path . '/' . $file;
						rmrf($subPath);
					}
				}
				closedir($rDir);
				rmdir($path);
			}
			else
			{
				unlink($path);
			}
		}
	}

	function dirlist($directory) {

		$path = rtrim($directory, '/');
		$res = array();

		$rDir = opendir($path);
		while(($file = readdir($rDir)) !== false)
		{
			if($file != '.' && $file != '..')
			{
				$subPath = $path . '/' . $file;
				if(!is_dir($subPath))
				{
					$res[pathinfo($file, PATHINFO_FILENAME)] = $subPath;
				}
			}
		}
		closedir($rDir);

		return $res;
	}

	function genXml($basePath, $category) {

		global $sDestImage, $sDestXml, $aSupportedLanguages, $aWordIndex;

		$category = empty($category) ? '' : (rtrim($category, '/') . '/');
		$aLevel = dirlist($basePath . $category);
		$aRes = array();
		foreach($aLevel as $sKey => $sPath)
		{
			$categoryPath = $basePath . $category . $sKey;

			$sImagePath = uniqid() . pathinfo($sPath, PATHINFO_BASENAME);
			copy($sPath, $sDestImage . $sImagePath);

			if(file_exists($categoryPath) && is_dir($categoryPath))
			{
				foreach($aSupportedLanguages as $sLang)
				{
					$sMyPath = $sDestXml . $sLang . '/' . $category . $sKey;
					if(!file_exists($sMyPath))
					{
						mkdir($sMyPath);
					}
				}
			
				$aChildren = genXml($basePath, $category . $sKey);
				// category

				$fileContent = array(
					'isCategory' => true,
					'picture' => $sImagePath,
					'sound' => '',
				);
				$children = array();
				
				$sXmlHeader = '<category><picture>' . $sImagePath . '</picture><sound></sound><text>';
				$sXmlFooter = '</text><textcolor>#CCCCCC</textcolor><indiagrams>';
				foreach($aChildren as $sFile) {
					$children[] = $sFile;
					$sXmlFooter .= '<indiagram>' . $sFile . '</indiagram>';
				}
				$fileContent['children'] = $children;
				$sXmlFooter .= '</indiagrams></category>';

				$sText = $aWordIndex[pathinfo($sPath, PATHINFO_BASENAME)]['fr'];
				$sFilename = uniqid() . preg_replace('#[^a-z]#isU', '', $sText) . '.xml';
				$aRes[] = $category . $sFilename;	
				foreach($aSupportedLanguages as $sLang)
				{
					$sDestDir = $sDestXml . $sLang . '/' . $category . $sFilename;
					$sText = $aWordIndex[pathinfo($sPath, PATHINFO_BASENAME)][$sLang];
					
					$fileContent['text'] = $sText;
					
					file_put_contents($sDestDir, $sXmlHeader . $sText . $sXmlFooter);
					//file_put_contents($sDestDir, json_encode($fileContent));
				}
			}
			else
			{
				//indiagram
				$fileContent = array(
					'isCategory' => false,
					'picture' => $sImagePath,
					'sound' => '',
				);
				
				$sXmlHeader = '<indiagram><picture>' . $sImagePath . '</picture><sound></sound><text>';
				$sXmlFooter = '</text></indiagram>';

				$sText = $aWordIndex[pathinfo($sPath, PATHINFO_BASENAME)]['fr'];
				$sFilename = uniqid() . preg_replace('#[^a-z]#isU', '', $sText) . '.xml';
				$aRes[] = $category . $sFilename;

				foreach($aSupportedLanguages as $sLang)
				{
					$sDestDir = $sDestXml . $sLang . '/' . $category . $sFilename;
					$sText = $aWordIndex[pathinfo($sPath, PATHINFO_BASENAME)][$sLang];
					$fileContent['text'] = $sText;
					
					file_put_contents($sDestDir, $sXmlHeader . $sText . $sXmlFooter);
					//file_put_contents($sDestDir, json_encode($fileContent));
				}
			}
		}
		return $aRes;
	}

	rmrf($sDest);
	@mkdir($sDest);
	@mkdir($sDestImage);
	@mkdir($sDestXml);
	foreach($aSupportedLanguages as $sLang) {
		@mkdir($sDestXml . '/' . $sLang);
	}

	//list top level
	genXml($sSourceImage, '');

