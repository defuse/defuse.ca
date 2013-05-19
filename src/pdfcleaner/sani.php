<?php
    die('This page has been removed temporarily for a security audit.');
try{
    require_once('/etc/creds.php');
    $creds = Creds::getCredentials("pdfcleaner");
$dbhost = $creds[C_HOST];
$dbuser = $creds[C_USER];
$dbpass = $creds[C_PASS];
/*
$conn = mysql_connect($dbhost, $dbuser, $dbpass) or die ('Error connecting to mysql');
$dbname = $creds[C_DATB];
mysql_select_db($dbname);

$q = mysql_query("UPDATE ctr SET count=count + 1");
*/

unset($creds);

if ($_FILES["file"]["error"] > 0)
{
	echo "Error: File upload failed.";
}
else
{
	$filename = $_FILES["pdffile"]["name"];
	$filepath = $_FILES["pdffile"]["tmp_name"];

	if(strpos($filename, ".pdf") === false) //obviously this isn't real security..
	{
		echo "Not a PDF file!"; die();
	}

	if(filesize($filepath) > 100 * 1024 * 1024)
	{
		echo "Sorry, that file is too big :(";
		die();
	}

	$filename = substr($filename, 0, strrpos($filename, ".")) . "[CLEANED]";

	$want = $_POST['want'];
	if($want == "ps" || $want == "pdf")
	{
		$psname = "/tmp/" . mt_rand() . ".ps";
		exec("pdf2ps $filepath $psname");
		if($want == "ps")
		{
			DownloadFile($psname, $filename . ".ps", "application/postscript");
		}
		else
		{
			$newpdf = "/tmp/" . mt_rand() . ".pdf";
			exec("ps2pdf $psname $newpdf");
			DownloadFile($newpdf, $filename . ".pdf", "application/pdf");
		}
		
	}
	elseif ($want == "text")
	{
		$txtname = "/tmp/" . mt_rand() . ".txt";
		exec("pdftotext $filepath $txtname");
		DownloadFile($txtname, $filename . ".txt", "text/plain");
	}


}
}catch (Exception $e){
	echo "Invalid PDF file.";
}

function DownloadFile($path, $name, $mime_type)
{
	if(file_exists($path))
	{
		@ob_end_clean();
		if(ini_get('zlib.output_compression'))
		ini_set('zlib.output_compression', 'Off');
		header('Content-Type: ' . $mime_type);
		header('Content-Disposition: attachment; filename="' . $name .'"');
		header("Content-Transfer-Encoding: binary");
		header('Accept-Ranges: bytes');
		header("Cache-control: private");
		header('Pragma: private');
		header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
		header("Content-Length: " . filesize($path) ); //15KB

		$fd = fopen($path, 'r');
		while(!feof($fd)) {
			$buffer = fread($fd, 2048);
			echo $buffer;
		}
		fclose($fd);
	}
}

?>
