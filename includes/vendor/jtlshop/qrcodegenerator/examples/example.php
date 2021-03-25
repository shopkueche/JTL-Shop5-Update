<?php
/**
 * @filesource   example.php
 * @created      10.12.2015
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2015 Smiley
 * @license      MIT
 */

//require_once '../vendor/autoload.php';
//require_once '../../autoload.php';
//
// ============ example autoloader ======================================
spl_autoload_register(
	function($class) {
		$szClassName = str_replace('\\', '/', $class);
		$szClassName = substr($szClassName, strrpos($szClassName, '/')+1);

		// {{{ --DEBUG--
		//echo("<b>[$szClassName]</b><br>");
		//ob_flush();
		//flush();
		// }}}

		$szFileName = $szClassName.'.php';
		switch(true) {
			case file_exists('../src/' . $szFileName):
				require_once '../src/' . $szFileName;
				break;
			case file_exists('../src/Data/' . $szFileName):
				require_once '../src/Data/' . $szFileName;
				break;
			case file_exists('../src/Output/' . $szFileName):
				require_once '../src/Output/' . $szFileName;
				break;
			default:
				die("class $szFileName (in ".__DIR__.") not found for loading!");
		}
	}
);
// ======================================================================


use qrcodegenerator\QRCode\QRCode;
use qrcodegenerator\QRCode\Output\QRImage;
use qrcodegenerator\QRCode\Output\QRString;
use qrcodegenerator\QRCode\Output\QRStringOptions;

$data = 'otpauth://totp/test?secret=B3JX4VCVJDVNXNZ5&issuer=chillerlan.net';
#$data = 'https://www.youtube.com/watch?v=DLzxrzFCyOs&t=43s';
#$data = 'skype://echo123';

?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8"/>
	<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
	<title>QRCode test</title>
	<style>
		body{
			margin: 0;
			padding: 0;
		}

		div.qrcode{
			margin: 0 5px;
		}

		/* row element */
		div.qrcode > p {
			margin: 0;
			padding: 0;
			height: 5px;
		}

		/* column element(s) */
		div.qrcode > p > b,
		div.qrcode > p > i {
			display: inline-block;
			width: 5px;
			height: 5px;
		}

		div.qrcode > p > b {
			background-color: #000;
		}

		div.qrcode > p > i {
			background-color: #fff;
		}
	</style>
</head>
<body>
<?php

echo '<img class="qrcode" alt="qrcode" src="'.(new QRCode($data, new QRImage))->output().'" />';
echo '<div class="qrcode">'.(new QRCode($data, new QRString))->output().'</div>';

echo('<pre>'
	."\n\n"
	."PHP ".phpversion()."\n"
	.'</pre>'
);

/*
$qrStringOptions = new QRStringOptions;
$qrStringOptions->type = QRCode::OUTPUT_STRING_TEXT;

echo '<pre class="qrcode">'.(new QRCode($data, new QRString($qrStringOptions)))->output().'</pre>';
*/

?>
</body>
</html>

