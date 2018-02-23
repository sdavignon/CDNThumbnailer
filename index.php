<?php
/**
 * This file is part of CDNThumbnailer.
 * For the full copyright and license information, please view the LICENCE
 * file that was distributed with this source code.
 *
 * @license See the LICENCE file distributed with the source code
 * @author Stephane HULARD <s.hulard@chstudio.fr>
 * @package Default
 */

if( !isset($_GET['path']) || !isset($_GET['format']) ) {
	header($_SERVER['SERVER_PROTOCOL'].' 400 Bad request', true, 400);
	exit();
}

//Require configuration
require_once dirname(__FILE__).'/config/config.inc.php';

//File path to be resized
$sPath = $_GET['path'];
//Image url scheme if image is an external one
$sScheme = isset($_GET['scheme'])?$_GET['scheme']:null;
//echo $sScheme;
//If there are GET parameters in the picture URL, just add it to the path
$query = array_diff_key($_GET, array_flip(array('path', 'format', 'scheme')));

if( count($query) > 0 ) {
	$sPath .= '?'.http_build_query($query);
}

//echo $sPath;
	//If the scheme is defined we try to download image
	if( !is_null($sScheme) ) {
		//Initialize curl handler and make the request
		$oRequest = curl_init($sScheme.'://'.str_replace(' ', '%20', $sPath));
		//Pretend to be a desktop browser
		curl_setopt($oRequest, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2272.118 Safari/537.36');
		//Try and cope with some HTTPS servers
		curl_setopt($oRequest, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($oRequest, CURLOPT_SSL_VERIFYHOST, 2);
		curl_setopt($oRequest, CURLOPT_SSL_CIPHER_LIST, 'TLSv1');
		//Follow redirects
		curl_setopt($oRequest, CURLOPT_FOLLOWLOCATION, true);
		ob_start();
		curl_exec($oRequest);
		$sContent = ob_get_clean();

		//Retrieve last request details
		$aCurlInfo = curl_getinfo($oRequest);
		//If last request is a "200 OK", continue
		if( isset($aCurlInfo['http_code']) && $aCurlInfo['http_code'] == 200 ) {
			
		//Else, the file can't be retrieved so, send a 404 header
		} else {
			header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found', true, 404);
			exit();
		}
		//Close curl handle
		curl_close($oRequest);
	//The scheme is not defined and original file is not here, file does not exists
	} else {
		header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found', true, 404);
		exit();
	}



	//Build valid HTTP Headers for cache and content type/length for a correct navigator management
	$expires = 60*60*24*EXPIRE_DAYS;
	header($_SERVER['SERVER_PROTOCOL'].' 200 OK', true, 200);
	header("Pragma: public");
	header("Cache-Control: maxage=".$expires);
	header('Expires: ' . gmdate('D, d M Y H:i:s', time()+$expires. ' GMT');
	header('Last-Modified: '.gmdate('D, d M Y H:i:s', time()-$expires.' GMT');
	header('Content-Type: image/svg+xml');
	header('Content-Length: '.strlen($sContent));
	echo $sContent;
	//Unset ImageFactory object to make sure resources are released
	//unset($sOriginalFile);




