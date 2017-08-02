<?php

error_reporting(0);

function microtime_int()
{
	list($usec, $sec) = explode(" ", microtime());
	return (int) (((float) $usec + (float) $sec) * 1000);
}

function curl_request($url, $post = '', $cookie_file = '', $fetch_cookie = 0, $referer)
{

	$curl = curl_init();
	curl_setopt($curl, CURLOPT_URL, $url);
	curl_setopt($curl, CURLOPT_HTTPHEADER, array("Expect:"));
	curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.1; Trident/6.0)');
	curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
	curl_setopt($curl, CURLOPT_AUTOREFERER, 1);
	curl_setopt($curl, CURLOPT_REFERER, $referer);
	if ($post)
	{
		curl_setopt($curl, CURLOPT_POST, 1);
		curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($post));
	}
	if ($fetch_cookie)
	{
		curl_setopt($curl, CURLOPT_COOKIEJAR, $cookie_file);
	}
	else
	{
		curl_setopt($curl, CURLOPT_COOKIEFILE, $cookie_file);
	}
	curl_setopt($curl, CURLOPT_HEADER, 1);
	curl_setopt($curl, CURLOPT_TIMEOUT, 5);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
	$data = curl_exec($curl);
	if (curl_errno($curl))
	{
		return false;
	}

	return $data;
}

$method = @$_REQUEST['method'];

switch ($method)
{

	case "fetch":
		$id   = @$_REQUEST['student_id'];
		$type = strtoupper(@$_REQUEST['type']);
		$name = @$_REQUEST['student_name'];

		if (null == $id || null == $type || null == $name)
		{
			exit('{"error":"1","msg":"Wrong parameter"}');
		}

		switch ($type)
		{
		case 'TIMES':

				$url = 'http://zccx.tyb.njupt.edu.cn/';

				for ($i = 0; $i < 5 &&  ! isset($matches[1][0]); $i++)
			{
					$result = curl_request($url, '', '', FALSE, $url);

					$pattern = '/<input type="hidden" name="authenticityToken" value="(.*?)">/is';
					preg_match_all($pattern, $result, $matches);
				}

				if ( ! isset($matches[1][0]))
			{
					exit('{"error":"1","msg":"Wrong authenticityToken"}');
				}

				$authenticityToken = $matches[1][0];

				$post['authenticityToken'] = $authenticityToken;
				$post['number'] = $id;
				$post['name'] = $name;

				$url = "http://zccx.tyb.njupt.edu.cn/student";

				for ($i = 0; $i < 5 &&  ! strstr($result, "刷卡计数"); $i++)
			{
					$result = curl_request($url, $post, $cookie_file, TRUE, $url);
				}

				if ( ! strstr($result, "刷卡计数"))
			{
					exit("error");
				}

				$pattern = '/<span class="badge">(.*?)<\/span>/is';
				preg_match_all($pattern, $result, $matches);

				if ( ! isset($matches[1][0]))
			{
					exit('{"error":"1","msg":"Network error"}');
				}

				echo ('{"query":"success","times":"'.$matches[1][0].'"}');

				break;

		case "DETAIL":

				$url = 'http://zccx.tyb.njupt.edu.cn/';

				for ($i = 0; $i < 5 &&  ! isset($matches[1][0]); $i++)
			{
					$result = curl_request($url, '', '', FALSE, $url);

					$pattern = '/<input type="hidden" name="authenticityToken" value="(.*?)">/is';
					preg_match_all($pattern, $result, $matches);
				}

				if ( ! isset($matches[1][0]))
			{
					exit('{"error":"1","msg":"Wrong authenticityToken"}');
				}

				$authenticityToken = $matches[1][0];

				$post['authenticityToken'] = $authenticityToken;
				$post['number'] = $id;
				$post['name'] = $name;

				$url = "http://zccx.tyb.njupt.edu.cn/student";

				for ($i = 0; $i < 5 &&  ! strstr($result, "刷卡计数"); $i++)
			{
					$result = curl_request($url, $post, $cookie_file, TRUE, $url);
				}

				if ( ! strstr($result, "刷卡计数"))
			{
					exit("error");
				}

				$pattern = '/<span class="badge">(.*?)<\/span>/is';
				preg_match_all($pattern, $result, $matches);

				if ( ! isset($matches[1][0]))
			{
					exit('{"error":"1","msg":"Network error"}');
				}

				$doc = new DomDocument;
				$doc->validateOnParse = true;
				$doc->LoadHTML($result);

				$result = array();

				$node = $doc->getElementsByTagName('tbody')->item(0)->getElementsByTagName('tr');
				for ($c = 0; $c < $node->length; $c++)
			{
					$child = $node->item($c)->getElementsByTagName('td');
					$str   = "";
					for ($d = 0; $d < $child->length; $d++)
				{
						$temp = $child->item($d);
						while ($temp->hasChildNodes())
					{
							$temp = $temp->childNodes->item(0);
						}
						$str .= $doc->saveXML($temp);
					}

					$str = str_replace(array("\n", "\r", "\r\n", " ", "\t", "&#13;"), "", $str);
					$str = urlencode($str);
					$str = str_replace(array("%C3%A5%C2%B9%C2%B4", "%C3%A6%C2%9C%C2%88", "%C3%A6%C2%97%C2%A5", "%C3%A6%C2%97%C2%B6", "%C3%A5%C2%88%C2%86"), "|", $str);

					$pieces = explode("|", $str);
					array_push($result, $pieces[0]."-".$pieces[1]."-".$pieces[2]." ".$pieces[3].":".$pieces[4]);
				}

				$output = array(
					"query" => "success",
					"times" => $matches[1][0],
					"list"  => $result,
				);

				echo (str_replace("&#13;", "", json_encode($output)));

				break;

				break;

		default:
				exit('{"error":"1","msg":"Wrong parameter"}');
				break;
		}

		break;
	default:
		exit('{"error":"1","msg":"Wrong parameter"}');
		break;
}
