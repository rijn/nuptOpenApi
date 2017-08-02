<?php

error_reporting(0);

include "VC2.php";
include 'database.php';

/* Initialize object */
$object = new VerifyCode();

$object->Import_Database($data);

/* request resource */
function curl_request($url, $post = '', $cookie_file = '', $fetch_cookie = 0, $referer = '', $timeout = 10)
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
	curl_setopt($curl, CURLOPT_HEADER, 0);
	curl_setopt($curl, CURLOPT_TIMEOUT, $timeout);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
	$data = curl_exec($curl);
	if (curl_errno($curl))
	{
		return false;
	}

	return $data;
}

function login($id, $password, $cookie_file)
{
	global $object;
	for ($k = 1; $k <= 100000; $k++)
	{
		$name = time().uniqid();

		$url = "http://202.119.225.34/default2.aspx";
		$code_url = "http://202.119.225.34/CheckCode.aspx";
		$refreer = "http://202.119.225.34/default2.aspx";

		$viewresult = curl_request($url, '', $cookie_file, true, $referer, 10);
		$image = curl_request($code_url, '', $cookie_file, false, $referer, 10);

		$fp = fopen("./temp/$name.gif", "w");
		fwrite($fp, $image);
		fclose($fp);

		$result = $object->Recognize_Image("./temp/$name.gif");

		$viewresult = iconv('gb2312', 'utf-8//IGNORE', $viewresult);
		$pattern    = '/<input type="hidden" name="__VIEWSTATE" value="(.*?)" \/>/is';
		preg_match_all($pattern, $viewresult, $matches);

		$post = array(
			__VIEWSTATE => @$matches[1][0],
			txtUserName => $id,
			TextBox2 => $password,
			txtSecretCode => $result,
			RadioButtonList1 => "",
			Button1 => "",
			lbLanguage => "",
			hidPdrs => "",
			hidsc => "",
		);

		$checkresult = curl_request($url, $post, $cookie_file, false, $referer, 10);
		$checkresult = iconv('gb2312', 'utf-8//IGNORE', $checkresult);

		if (strstr($checkresult, "验证码不正确") || false == $result || 'iiii' == $result)
		{

		}
		else
		{
			if (strstr($checkresult, "密码错误"))
			{
				exit("{\"err\":9000301}");
			}
			else
			{
				$pattern = '/xm=(.*?)&gnmkdm=N/is';
				preg_match_all($pattern, $checkresult, $matches);

				return @$matches[1][0];
			}

		}

		unlink("./temp/$name.gif");

	}
}

function getView($result)
{
	if ($result)
	{
		$result  = iconv('gb2312', 'utf-8//IGNORE', $result);
		$pattern = '/<input type="hidden" name="__VIEWSTATE" value="(.*?)" \/>/is';
		preg_match_all($pattern, $result, $matches);

		if (isset($matches[1][0]))
		{
			return $matches[1][0];
		}
		else
		{
			exit("{\"err\":9000302}");
		}
	}
	else
	{
		exit("{\"err\":9000302}");
	}
}

$method = strtolower(@$_REQUEST['method']);

switch ($method)
{
	case "login":
		$id = @$_REQUEST['student_id'];
		$password = @$_REQUEST['student_password'];

		if (null == $id || null == $password)
		{
			exit("{\"err\":200}");
		}

		$cookie_file = tempnam('./temp', 'cookie');

		$name = login($id, $password, $cookie_file);

		unlink($cookie_file);

		if (null == $name)
		{
			exit("{\"err\":9000302}");
		}
		else
		{
			exit("{\"student_name\":\"$name\"}");
		}

		break;

	case "info":

		$id = @$_REQUEST['student_id'];
		$password = @$_REQUEST['student_password'];

		if (null == $id || null == $password)
		{
			exit("{\"err\":200}");
		}

		$cookie_file = tempnam('./temp', 'cookie');

		$name = login($id, $password, $cookie_file);

		if (null == $name)
		{
			echo ("{\"err\":9000302}");
		}
		else
		{
			$output  = array();
			$matches = NULL;
			$url     = 'http://202.119.225.34/xsgrxx.aspx?xh='.$id.'&xm='.iconv('utf-8', 'gb2312//IGNORE', $name).'&gnmkdm=N121501';
			for ($i = 0; $i < 5 &&  ! isset($matches[1][0]); $i++)
			{
				$result  = curl_request($url, '', $cookie_file, FALSE, 'http://202.119.225.34/xs_main.aspx?xh=B14010312');
				$result  = iconv('gb2312', 'utf-8//IGNORE', $result);
				$pattern = '/<span id="lbl_sfzh">(.*?)<\/span>/is';
				preg_match_all($pattern, $result, $matches);
			}
			$output['student_idcard'] = $matches[1][0];

			$output['student_name'] = $name;

			$pattern = '/<input name="TELNUMBER" type="text" value="(.*?)" id="TELNUMBER" \/>/is';
			preg_match_all($pattern, $result, $matches);
			$output['student_mobile'] = $matches[1][0];

			$pattern = '/<input name="lxdh" type="text" value="(.*?)" id="lxdh" \/>/is';
			preg_match_all($pattern, $result, $matches);
			$output['student_tel'] = $matches[1][0];

			$pattern = '/<input name="dzyxdz" type="text" value="(.*?)" id="dzyxdz" \/>/is';
			preg_match_all($pattern, $result, $matches);
			$output['student_email'] = $matches[1][0];

			$pattern = '/<span id="lbl_xb">(.*?)<\/span>/is';
			preg_match_all($pattern, $result, $matches);
			$output['student_sex'] = $matches[1][0];

			$pattern = '/<span id="lbl_xy">(.*?)<\/span>/is';
			preg_match_all($pattern, $result, $matches);
			$output['student_depatment'] = $matches[1][0];

			$pattern = '/<span id="lbl_zymc">(.*?)<\/span>/is';
			preg_match_all($pattern, $result, $matches);
			$output['student_major'] = $matches[1][0];

			echo (json_encode($output));
		}

		unlink($cookie_file);

		exit();

		break;

	case "score":

		$id = @$_REQUEST['student_id'];
		$password = @$_REQUEST['student_password'];

		if (null == $id || null == $password)
		{
			exit("{\"err\":200}");
		}

		$cookie_file = tempnam('./temp', 'cookie');

		$name = login($id, $password, $cookie_file);

		if (null == $name)
		{
			echo ("{\"err\":9000302}");
		}
		else
		{
			//get new VIEWSTATE
			$url = 'http://202.119.225.34/xscj_gc.aspx?xh='.$id.'&xm='.iconv('utf-8', 'gb2312//IGNORE', $name).'&gnmkdm=N121605';

			$result    = curl_request($url, '', $cookie_file, FALSE, 'http://202.119.225.34/');
			$VIEWSTATE = getView($result);

			//request score
			$post_2['__VIEWSTATE'] = $VIEWSTATE;
			$post_2['ddlXN'] = iconv('utf-8', 'gb2312//IGNORE', '');
			$post_2['ddlXQ'] = iconv('utf-8', 'gb2312//IGNORE', '');
			$post_2['Button2'] = iconv('utf-8', 'gb2312//IGNORE', '在校学习成绩查询');

			$result = curl_request($url, $post_2, $cookie_file, FALSE, $url);

			//match result
			//$result = iconv('gb2312', 'utf-8//IGNORE', $result);

			$doc = new DomDocument;
			$doc->validateOnParse = true;
			$doc->LoadHTML($result);
			$node = $doc->getElementById('Datagrid1')->getElementsByTagName('tr');

			$key    = array();
			$value  = array();
			$detail = array();

			for ($a = 0; $a < $node->length; $a++)
			{
				$ta    = $node->item($a);
				$list  = $ta->getElementsByTagName('td');
				$value = array();
				if ($ta->getAttribute('class') == 'datelisthead')
				{
					for ($b = 0; $b < $list->length; $b++)
					{
						array_push($key, str_replace("<td>", "", str_replace("</td>", "", $doc->saveXML($list->item($b)))));
					}
				}
				else
				{
					for ($b = 0; $b < $list->length; $b++)
					{
						$value[$key[$b]] = str_replace("<td>", "", str_replace("</td>", "", $doc->saveXML($list->item($b))));
					}
					array_push($detail, $value);
				}
			}

			/*
		$pattern = '/<table class="datelist"(.*?)<\/table>/is';
		preg_match_all($pattern, $result, $matches_0);
		$result_0 = $matches_0[0][0];
		print_r($result_0);
		*/
			$result  = iconv('gb2312', 'utf-8//IGNORE', $result);
			$pattern = '/<span id="pjxfjd"><b>平均学分绩点：(.*?)<\/b><\/span>/is';
			preg_match_all($pattern, $result, $matches_1);
			$result_1 = $matches_1[1][0];

			echo (
				json_encode(
					array(
						"gpa"    => $result_1,
						"detail" => $detail,
					)
				)
			);
		}

		unlink($cookie_file);

		exit();

		break;

	default:
		exit("{\"err\":200}");
}
