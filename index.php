<!DOCTYPE HTML>  
<html>
	<head>
		<style>
		body {text-align: center;}
		.error {color: red;}
		.success {color: green;}
		</style>
	</head>
	<body>
		<?php
		$urlErr = $urlSuccess = "";
		$url = "";

		if ($_SERVER["REQUEST_METHOD"] == "POST") {
			if (empty($_POST["url"])) {
				$urlErr = "URL is required";
			} else {
				$url = $_POST["url"];
				// remove illegal characters from a url
				$url = filter_var($url, FILTER_SANITIZE_URL);
				// add http if not exist
				if (!preg_match("~^(?:f|ht)tps?://~i", $url)) {
					$url = "http://" . $url;
				}
				// validate url
				if (filter_var($url, FILTER_VALIDATE_URL)) {
					// call bitly using token
					$token = "50ca105047fb3f041405886224b9073a739f9c27";
					$ch = curl_init('https://api-ssl.bitly.com/v4/shorten');
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
					curl_setopt($ch, CURLOPT_HTTPHEADER, array(
					   'Content-Type: application/json',
					   'Authorization: Bearer ' . $token
					));
					
					// data to send
					$postData = array(
						'group_guid' => '', // default to free
						'domain' => 'bit.ly',
						'long_url' => $url,
					);
					curl_setopt($ch, CURLOPT_POSTFIELDS , json_encode($postData));

					$data = json_decode(curl_exec($ch));
					curl_close($ch);
					
					if (is_null($data->{'link'})) {
						$message = $data->{'message'};
						$urlErr	 = "Server error: $message";
					} else {
						$link = $data->{'link'};
						$urlSuccess = "<a target='_blank' href='$link'>$link</a>";
					}
				} else {
					$urlErr	 = "$url is not a valid URL";
				}
			}
		}
		?>

		<h2>Simple URL Shortening Service</h2>
		<form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">  
			URL<span class="error">*</span>: <input type="text" name="url" value="<?php echo $url;?>" placeholder="http://www.example.com"><input type="submit" name="submit" value="Submit">
			<span class="error"><br/><?php echo $urlErr;?></span>
			<span class="success"><br/><?php echo $urlSuccess;?></span>
		</form>
	</body>
</html>
