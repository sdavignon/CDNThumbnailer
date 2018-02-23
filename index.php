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

if( !isset($_GET['svg']) ) {
	header($_SERVER['SERVER_PROTOCOL'].' 400 Bad request', true, 400);
	exit();
}

//Require configuration
require_once dirname(__FILE__).'/config/config.inc.php';

//File path to be resized
$sPath = $_GET['svg'];
//Image url scheme if image is an external one
	

//echo $sPath;
	//If the scheme is defined we try to download image
	if( !is_null($sPath) ) {
		//Initialize curl handler and make the request
		$oRequest = curl_init('https://storage.googleapis.com/ck-kitty-image/'.$sPath);
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
	 header("Access-Control-Allow-Origin: *");
	header("Cache-Control: maxage=".$expires);
	header('Content-Type: image/svg+xml');
	header('Content-Length: '.strlen($sContent));
	echo $sContent;
