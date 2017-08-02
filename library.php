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

function getRemain($url)
{
	$result = "";

	for ($i = 0; $i < 5 && strpos($result, "总馆") === false; $i++)
	{
		$result = curl_request($url, '', '', FALSE, '');
	}

	if (strpos($result, "总馆") === false)
	{
		exit('{"error":"1","msg":"Network error"}');
	}

	$doc = new DomDocument;
	$doc->validateOnParse = true;
	$doc->LoadHTML($result);

	$node = $doc->getElementsByTagName('table')->item(0)->getElementsByTagName('tr');

	$result = array();

	$result["available"] = 0;

	for ($c = 1; $c < $node->length; $c++)
	{
		$data = $node->item($c)->getElementsByTagName('td');
		$t_a  = array();
		for ($d = 0; $d < $data->length; $d++)
		{
			$temp = $data->item($d);

			while ($temp->hasChildNodes())
			{
				$temp = $temp->childNodes->item(0);
			}

			array_push($t_a, $doc->saveXML($temp));

			//echo ($doc->saveXML($temp));
		}
		if (strpos($t_a[4], "仙林"))
		{
			$result["location"] = strstr($t_a[4], "（", true);
			if ("可借" == $t_a[5])
			{
				$result["available"]++;
			}

		}

	}

	return $result;
}

$method = @$_REQUEST['method'];

switch ($method)
{
	case 'search':

		$keyword = @$_REQUEST['keyword'];

		$start = 1;

		if (isset($_REQUEST['start']))
		{
			$start = $_REQUEST['start'];
		}

		if ($start < 1)
		{
			$start = 1;
		}

		$url = 'http://202.119.228.6:8080/opac/openlink.php?match_flag=forward&displaypg=5&showmode=table&orderby=DESC&sort=CATA_DATE&onlylendable=no';

		$value   = str_replace('-', '', $keyword);
		$pattern = '/[0-9]{12}[0-9xX]/is';
		preg_match_all($pattern, $value, $matches);
		if (isset($matches[0][0]))
		{
			$url .= '&isbn='.$value;
		}
		else
		{
			$url .= '&title='.$value;
		}

		$result = "";

		for ($i = 0; $i < 5 && strpos($result, "版权") === false; $i++)
		{
			$result = curl_request($url."&page=1", '', '', FALSE, '');
		}

		if (strpos($result, "版权") === false)
		{
			exit('{"error":"1","msg":"Network error"}');
		}

		$pattern = '/<strong class="red">(.*?)<\/strong>/is';
		preg_match_all($pattern, $result, $matches);

		if (isset($matches[1][0]))
		{
			$scan_count = $matches[1][0];
		}
		else
		{
			exit('{"error":"1","msg":"System error"}');
		}

		if ($start > $scan_count)
		{
			exit("{}");
		}

		$url = $url."&page=".((int) ($start / 20) + 1);

		$doc = new DomDocument;
		$doc->validateOnParse = true;
		$doc->LoadHTML($result);

		$node = $doc->getElementById('result_content')->getElementsByTagName('tr');

		$result = array();

		for ($c = ((int) ($start % 20)); $c < $node->length && $c < ($start % 20) + 5 && $c <= $scan_count - ((int) ($start / 20)) * 20; $c++)
		{
			$data = $node->item($c)->getElementsByTagName('td');
			$t_a  = array();
			for ($d = 0; $d < $data->length; $d++)
			{
				$temp = $data->item($d);

				$a = $temp->getElementsByTagName('a');

				for ($i = 0; $i < $a->length; $i++)
				{
					$href = $a->item($i)->getAttribute('href');
				}

				while ($temp->hasChildNodes())
				{
					$temp = $temp->childNodes->item(0);
				}

				array_push($t_a, $doc->saveXML($temp));

				//array_push($t_a, $href);
				//echo ($doc->saveXML($temp));
			}
			array_push($t_a, substr($href, strpos($href, "marc_no=") + 8));
			$sub_url = "http://202.119.228.6:8080/opac/$href";
			array_push(
				$result,
				array_merge(
					array(
						"id"       => $t_a[0],
						"name"     => $t_a[1],
						"author"   => $t_a[2],
						"press"    => $t_a[3],
						"category" => $t_a[4],
						"type"     => $t_a[5],
						"marc_no"  => $t_a[6],
					),
					getRemain($sub_url)
				)
			);
		}

		exit(json_encode($result));

		break;

	case 'detail':

		$marc_no = @$_REQUEST['marc_no'];

		$url = 'http://202.119.228.6:8080/opac/item.php?marc_no='.$marc_no;

		$request = "";

		for ($i = 0; $i < 5 && strpos($request, "版权") === false; $i++)
		{
			$request = curl_request($url."&page=1", '', '', FALSE, '');
		}

		if (strpos($request, "版权") === false)
		{
			exit('{"error":"1","msg":"Network error"}');
		}

		if (strpos($request, "题名") === false)
		{
			exit('{"error":"1","msg":"Wrong marc_no"}');
		}

		$doc = new DomDocument;
		$doc->validateOnParse = true;
		$doc->LoadHTML($request);

		$output = array();

		$node   = $doc->getElementsByTagName('dl');
		$result = array();

		for ($c = 0; $c < $node->length; $c++)
		{
			$dt = $node->item($c)->childNodes->item(0);
			$dd = $node->item($c)->getElementsByTagName('dd');
			if ($dd->length > 0 && strpos($doc->saveXML($dt), '相关电子资源') === false)
			{
				$dd = $dd->item(0)->childNodes;

				$str = "";
				for ($d = 0; $d < $dd->length; $d++)
				{
					$temp = $dd->item($d);
					while ($temp->hasChildNodes())
					{
						$temp = $temp->childNodes->item(0);
					}
					$str .= $doc->saveXML($temp);
				}
				$key = str_replace(array(":", "：", "<dt>", "</dt>"), "", $doc->saveXML($dt));
				if ( ! isset($result[$key]))
				{
					$result[$key] = $str;
				}

			}
		}

		$output = array(
			"name"     => strstr($result["题名/责任者"], '/', true),
			"author"   => substr(strstr($result["题名/责任者"], '/'), 1),
			"press"    => $result["出版发行项"],
			"isbn"     => strstr($result["ISBN及定价"], '/', true),
			"price"    => substr(strstr($result["ISBN及定价"], '/'), 1),
			"carrier"  => $result["载体形态项"],
			"subject"  => $result["学科主题"],
			"category" => $result["中图法分类号"],
			"summary"  => $result["提要文摘附注"],
		);

		$pattern = '/<dt class="grey">文献类型：(.*?)<\/dt>/is';
		preg_match_all($pattern, $request, $matches);

		if (isset($matches[1][0]))
		{
			$output["type"] = $matches[1][0];
		}

		$pattern = '/<dt class="grey">浏览次数：(.*?)<\/dt>/is';
		preg_match_all($pattern, $request, $matches);

		if (isset($matches[1][0]))
		{
			$output["view"] = $matches[1][0];
		}

		$node   = $doc->getElementsByTagName('table')->item(0)->getElementsByTagName('tr');
		$result = array();

		for ($c = 1; $c < $node->length; $c++)
		{
			$data = $node->item($c)->getElementsByTagName('td');
			$t_a  = array();
			for ($d = 0; $d < $data->length; $d++)
			{
				$temp = $data->item($d);

				while ($temp->hasChildNodes())
				{
					$temp = $temp->childNodes->item(0);
				}
				array_push($t_a, $doc->saveXML($temp));
			}
			array_push($result, array(
				"demanding" => $t_a[0],
				"bar" => $t_a[1],
				"location" => str_replace(array("\r\n", "\r", "\n", " ", "\t", "\0"), "", $t_a[4]) || "",
				"state" => $t_a[5],
			));
		}

		$output["list"] = $result;

		exit(str_replace(array("&#13;"), "", json_encode($output)));

		break;

	case ("checkout"):

		$cookie_file = tempnam('./temp', 'cookie');
		$id   = @$_REQUEST['student_id'];
		$password = @$_REQUEST['password'];
		$name = @$_REQUEST['student_name'];

		$url = 'http://202.119.228.6:8080/reader/redr_verify.php';
		$post['number'] = $id;
		$post['passwd'] = $password;
		$post['select'] = 'cert_no';
		$post['returnUrl'] = '';

		$result = 0;

		for ($i = 0; $i < 5 &&  ! strstr($result, "注销"); $i++)
		{
			$result = curl_request($url, $post, $cookie_file, TRUE, 'http://202.119.228.6:8080/reader/login.php');

			if (strstr($result, "您尚未完成身份认证"))
			{

				$post_con['name'] = $name;
				for ($j = 0; $j < 5 &&  ! strstr($result, "修改密码"); $j++)
				{
					$result = curl_request('http://202.119.228.6:8080/reader/redr_con_result.php', $post_con, $cookie_file, FALSE, 'http://202.119.228.6:8080/reader/redr_con.php');
				}
				if ( ! strstr($result, "修改密码"))
				{
					unlink($cookie_file);
					exit(0);
				}

			}
			if (strstr($result, "密码错误"))
			{
				exit('{"error":"1","msg":"Wrong data"}');
				unlink($cookie_file);
				exit(0);
			}
		}

		if ( ! strstr($result, "注销"))
		{
			exit('{"error":"1","msg":"Wrong data"}');
			exit(0);
		}

		$output = array();

		$url = 'http://202.119.228.6:8080/reader/redr_info.php';

		for ($i = 0; $i < 5 &&  ! strstr($result, "欠款状态"); $i++)
		{
			$result = curl_request($url, $post, $cookie_file, FALSE, 'http://202.119.228.6:8080/');
		}

		if ( ! strstr($result, "欠款状态"))
		{
			exit('{"error":"1","msg":"System error"}');
			exit(0);
		}

		$doc = new DomDocument;
		$doc->validateOnParse = true;
		$doc->LoadHTML($result);

		$key = array("读者条码号：", "累计借书：", "欠款状态：");

		$temp = array();

		$node = $doc->getElementsByTagName('td');
		for ($c = 0; $c < $node->length; $c++)
		{
			$child = $node->item($c)->getElementsByTagName('span');
			if ($child->length)
			{
				$item = urldecode($doc->saveXML($child->item(0)->childNodes->item(0)));
				if (in_array($item, $key))
				{
					array_push($temp, $doc->saveXML($node->item($c)->childNodes->item(1)));
				}
			}
		}

		$output["card"] = $temp[0];
		$output['times'] = str_replace("册次", "", $temp[1]);
		$output['state'] = $temp[2];

		$url = 'http://202.119.228.6:8080/reader/book_lst.php';

		for ($i = 0; $i < 5 &&  ! strstr($result, "当前借阅"); $i++)
		{
			$result = curl_request($url, $post, $cookie_file, FALSE, 'http://202.119.228.6:8080/');
		}

		if ( ! strstr($result, "当前借阅"))
		{
			unlink($cookie_file);
			exit(0);
		}

		$doc->LoadHTML($result);

		$key = array(0, 1, 4);

		$result = array();

		$node = $doc->getElementsByTagName('tr');
		for ($c = 1; $c < $node->length; $c++)
		{
			$text  = array();
			$child = $node->item($c)->getElementsByTagName('td');
			foreach ($key as $k)
			{

				$pattern = '/marc_no=(.*?)">/is';
				preg_match_all($pattern, $doc->saveXML($child->item($k)), $matches);

				if (isset($matches[1][0]))
				{
					$text["marc_no"] = $matches[1][0];
				}

				if ($child->item($k)->hasChildNodes())
				{
					if ($child->item($k)->childNodes->item(0)->hasChildNodes())
					{
						$text[$k] = $doc->saveXML($child->item($k)->childNodes->item(0)->childNodes->item(0));
					}
					else
					{
						$text[$k] = $doc->saveXML($child->item($k)->childNodes->item(0));
					}
				}
				else
				{
					$text[$k] = $doc->saveXML($child->item($k));
				}
			}
			array_push(
				$result,
				array(
					"bar"     => $text[0],
					"marc_no" => $text[marc_no],
					"name"    => $text[1],
					"deadline" => str_replace(array(" ", "\n", "\r", "\r\n", "\t", " "), "", $text[4]),
				)
			);
		}

		$output['list'] = $result;

		unlink($cookie_file);

		exit(json_encode($output));

		break;

	case ("history"):

		$cookie_file = tempnam('./temp', 'cookie');
		$id   = @$_REQUEST['student_id'];
		$password = @$_REQUEST['password'];
		$name = @$_REQUEST['student_name'];

		$url = 'http://202.119.228.6:8080/reader/redr_verify.php';
		$post['number'] = $id;
		$post['passwd'] = $password;
		$post['select'] = 'cert_no';
		$post['returnUrl'] = '';

		$result = 0;

		for ($i = 0; $i < 5 &&  ! strstr($result, "注销"); $i++)
		{
			$result = curl_request($url, $post, $cookie_file, TRUE, 'http://202.119.228.6:8080/reader/login.php');

			if (strstr($result, "您尚未完成身份认证"))
			{

				$post_con['name'] = $name;
				for ($j = 0; $j < 5 &&  ! strstr($result, "修改密码"); $j++)
				{
					$result = curl_request('http://202.119.228.6:8080/reader/redr_con_result.php', $post_con, $cookie_file, FALSE, 'http://202.119.228.6:8080/reader/redr_con.php');
				}
				if ( ! strstr($result, "修改密码"))
				{
					unlink($cookie_file);
					exit(0);
				}

			}
			if (strstr($result, "密码错误"))
			{
				exit('{"error":"1","msg":"Wrong data"}');
				unlink($cookie_file);
				exit(0);
			}
		}

		if ( ! strstr($result, "注销"))
		{
			exit('{"error":"1","msg":"Wrong data"}');
			exit(0);
		}

		$output = array();

		$url = 'http://202.119.228.6:8080/reader/book_hist.php';

		$post = array(
			"para_string" => "all",
			"topage"      => "1",
		);

		for ($i = 0; $i < 5 &&  ! strstr($result, "显示全部"); $i++)
		{
			$result = curl_request($url, $post, $cookie_file, FALSE, 'http://202.119.228.6:8080/');
		}

		if ( ! strstr($result, "显示全部"))
		{
			exit('{"error":"1","msg":"System error"}');
			exit(0);
		}

		$doc = new DomDocument;
		$doc->validateOnParse = true;

		$doc->LoadHTML($result);

		$key = array(1, 2, 4, 5);

		$result = array();

		$node = $doc->getElementsByTagName('tr');
		for ($c = 1; $c < $node->length; $c++)
		{
			$text  = array();
			$child = $node->item($c)->getElementsByTagName('td');
			foreach ($key as $k)
			{

				$pattern = '/marc_no=(.*?)">/is';
				preg_match_all($pattern, $doc->saveXML($child->item($k)), $matches);

				if (isset($matches[1][0]))
				{
					$text["marc_no"] = $matches[1][0];
				}

				if ($child->item($k)->hasChildNodes())
				{
					if ($child->item($k)->childNodes->item(0)->hasChildNodes())
					{
						$text[$k] = $doc->saveXML($child->item($k)->childNodes->item(0)->childNodes->item(0));
					}
					else
					{
						$text[$k] = $doc->saveXML($child->item($k)->childNodes->item(0));
					}
				}
				else
				{
					$text[$k] = $doc->saveXML($child->item($k));
				}
			}
			array_push(
				$result,
				array(
					"bar"        => $text[1],
					"marc_no"    => $text[marc_no],
					"name"       => $text[2],
					"borrowdate" => $text[4],
					"returndate" => $text[5],
				)
			);
		}

		$output['list'] = $result;

		unlink($cookie_file);

		exit(json_encode($output));

		break;

	case "renew":

		$cookie_file = tempnam('./temp', 'cookie');
		$id   = @$_REQUEST['student_id'];
		$password = @$_REQUEST['password'];
		$name = @$_REQUEST['student_name'];
		$bar  = @$_REQUEST['bar'];

		$url = 'http://202.119.228.6:8080/reader/redr_verify.php';
		$post['number'] = $id;
		$post['passwd'] = $password;
		$post['select'] = 'cert_no';
		$post['returnUrl'] = '';

		$result = 0;

		for ($i = 0; $i < 5 &&  ! strstr($result, "注销"); $i++)
		{
			$result = curl_request($url, $post, $cookie_file, TRUE, 'http://202.119.228.6:8080/reader/login.php');
			if (strstr($result, "您尚未完成身份认证"))
			{

				$post_con['name'] = $name;
				for ($j = 0; $j < 5 &&  ! strstr($result, "修改密码"); $j++)
				{
					$result = curl_request('http://202.119.228.6:8080/reader/redr_con_result.php', $post_con, $cookie_file, FALSE, 'http://202.119.228.6:8080/reader/redr_con.php');
				}
				if ( ! strstr($result, "修改密码"))
				{
					unlink($cookie_file);
					exit(0);
				}

			}
			if (strstr($result, "密码错误"))
			{
				exit('{"error":"1","msg":"Wrong data"}');
				unlink($cookie_file);
				exit(0);
			}
		}

		if ( ! strstr($result, "注销"))
		{
			exit('{"error":"1","msg":"Wrong data"}');
			exit(0);
		}

		$result = "";

		$post = array();

		$post['bar_code'] = $bar;
		$post['time'] = microtime_int();

		$url = "http://202.119.228.6:8080/reader/ajax_renew.php?bar_code=$bar&time=".$post['time'];

		for ($i = 0; $i < 5 &&  ! strstr($result, "mylib"); $i++)
		{
			$result = curl_request($url, $post, $cookie_file, FALSE, 'http://202.119.228.6:8080/reader/book_lst.php');
		}

		unlink($cookie_file);

		if ( ! strstr($result, "mylib"))
		{
			exit('{"error":"1","msg":"Network error"}');
		}

		if ( ! strstr($result, "red"))
		{
			exit('{"error":"1","msg":"Bar_code error"}');
		}

		if ( ! strstr($result, "成功"))
		{
			exit('{"query":"success","result":"failed"}');
		}
		else
		{
			exit('{"query":"success","result":"succeed"}');
		}

		break;
	default:
		exit('{"error":"1","msg":"Wrong parameter"}');
		break;
}
