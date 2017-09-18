<?php
	
/*
	Info
	
	This script can check any website for changes.
	Run it as a cronjob in an interval of your choice to watch a site regularly.
	
	Note the following arguments the script is expecting.
	
	example: php website_alert.php to@example.com from@example.com Example https://www.example.com push positives '//div[contains(@class, "example")]'
	
	Initially written by Sascha Wiswedel 
		berb@sascha-wiswedel.de
		https://github.com/wiswedel/
		https://github.com/wiswedel/simpleWebsiteAlert/	
*/
	
$mail_to = $argv[1]; 	// target e-mail address
$mail_from = $argv[2];	// mail sender
$site = $argv[3]; 		// project identifier, must be 1 word, no spaces
$url = $argv[4];		// URL to watch, including http(s)
$push = $argv[5];		// set to "push" to receive push notifications >> requires a token from https://pushme.jagcesar.se/ >> token must be stored inside pushToken.txt
$negatives = $argv[6]; 	// [positives|negatives] especially for testing purposes, set to "negative" to get an alert every time
$xpath = $argv[7];		// [noxpath|(xpath-expression in single quotes)] apply an xpath onto the crawled website before diffing


// crawl a website and save it as current.txt
if (isset($xpath) && $xpath !== 'noxpath' && $xpath !== NULL) {
	
		exec("wget -q -O - $url -q --user-agent='Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/51.0.2704.103 Safari/537.36'| xmllint --nowarning --html --xpath '$xpath' - >current_$site.txt 2>/dev/null");
	
	} else {
		
		exec("wget -O current_$site.txt $url -q --user-agent='Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/51.0.2704.103 Safari/537.36'");		
	}

// compare the last crawl with this one and write the result into a variable
$diff = exec("diff current_$site.txt before_$site.txt");

// set subject and message body for the alert e-mail
if (isset($diff) && $diff !== '' && $diff !== NULL) {

	$subject = $site . ": UPDATE!!!";
	$message = "There has been an update for " . $url;

	} else {
	
		if ($negatives == "negatives") {
			
			$subject = $site . ": no update";
			$message = "Sorry, no update for " . $url;
		}
		
	}

// send the e-mail
if (isset($subject)) {

	mail($mail_to, $subject, $message, "From: " . $mail_from . "\r\n" . "Content-Type: text/html\r\n");
	
	// send push notification if requested
	if ($push == 'push') {
	
		$pushToken = trim(file_get_contents('pushToken.txt'));
		$pushCommand = "https://pushmeapi.jagcesar.se --data 'title=" . $url . "&token=" . $pushToken . "'";
		exec("curl -s " . $pushCommand);
	
	}

}

// prepare this crawl to be the diffed with in the next rotation
exec("mv current_$site.txt before_$site.txt");

?>