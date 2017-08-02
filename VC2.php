<?php

/**
 * Verify Code class
 *
 * @author Rijn
 * @link [https://github.com/rijn/PHP-Verify-Code-Identification-Class]
 */

//error_reporting(0);
set_time_limit(0);

function cmp($a, $b)
{
	if ($a->p == $b->p)
	{
		return 0;
	}
	return ($a->p > $b->p) ? -1 : 1;
}

class VerifyCode {

	public $Resource = array();
	public $k = 0;

	public function Import_Database($object)
	{
		for ($k = 0; $k < count($object); $k++)
		{
			$temp = $object[$k]['image'];
			$object[$k]['array'] = $temp;
			$im    = imagecreate(strlen($temp[0]), count($temp));
			$black = imagecolorallocate($im, 0, 0, 0);
			$white = imagecolorallocate($im, 255, 255, 255);
			for ($i = 0; $i < count($temp); $i++)
			{
				for ($j = 0; $j < strlen($temp[$i]); $j++)
				{
					if (1 == $temp[$i][$j])
					{
						imagesetpixel($im, $j, $i, $black);
					}
					else
					{
						imagesetpixel($im, $j, $i, $white);
					}
				}
			}

			$object[$k]['image'] = $im;
		};

		$this->Resource = $object;

		return true;
	}

	public function Output_Database()
	{

		$output = $this->Resource;

		for ($k = 0; $k < count($output); $k++)
		{
			$temp  = $output[$k]['image'];
			$image = array();

			for ($i = 0; $i < imagesy($temp); $i++)
			{
				$image[$i] = "";
				for ($j = 0; $j < imagesx($temp); $j++)
				{
					$pixelrgb = imagecolorat($temp, $j, $i);
					$cols     = imagecolorsforindex($temp, $pixelrgb);
					$r        = $cols['red'];

					if ($r < 127)
					{
						$image[$i] .= "1";
					}
					else
					{
						$image[$i] .= "0";
					}
				}
			}

			$output[$k]['image'] = $image;
		};
		return $output;
	}

	/**
	 * This was image binaryzation function
	 *
	 * @author Rijn
	 * @param image verify code
	 * @param width height
	 * @return image
	 */
	public function Binaryzation($image, $width, $height)
	{
		$im = imagecreate($width, $height);
		$black = imagecolorallocate($im, 0, 0, 0);
		$white = imagecolorallocate($im, 255, 255, 255);
		for ($i = 0; $i < $width; $i++)
		{
			for ($j = 0; $j < $height; $j++)
			{
				$pixelrgb = imagecolorat($image, $i, $j);
				$cols     = imagecolorsforindex($image, $pixelrgb);
				$r        = $cols['red'];
				$g        = $cols['green'];
				$b        = $cols['blue'];
				//echo ("$i,$j=>$r,$g,$b<br/>");
				if ($b - $r > 70 && $b - $g > 70)
				{
					imagesetpixel($im, $i, $j, $black);
				}
				else
				{
					imagesetpixel($im, $i, $j, $white);
				}
			}
		}

		return $im;
	}

	/**
	 * This was image binaryzation function
	 *
	 * @author Rijn
	 * @param image verify code
	 * @param width height
	 * @return image
	 */
	public function Binaryzation2($image, $width, $height)
	{
		$im = imagecreate($width, $height);
		$black = imagecolorallocate($im, 0, 0, 0);
		$white = imagecolorallocate($im, 255, 255, 255);
		for ($i = 0; $i < $width; $i++)
		{
			for ($j = 0; $j < $height; $j++)
			{
				$pixelrgb = imagecolorat($image, $i, $j);
				$cols     = imagecolorsforindex($image, $pixelrgb);
				$r        = $cols['red'];
				$g        = $cols['green'];
				$b        = $cols['blue'];
				//echo ("$i,$j=>$r,$g,$b<br/>");
				if ($r < 127)
				{
					imagesetpixel($im, $i, $j, $black);
				}
				else
				{
					imagesetpixel($im, $i, $j, $white);
				}
			}
		}

		return $im;
	}

	/**
	 * Erosion
	 *
	 * @author Rijn
	 * @param image
	 * @return image
	 */
	public function Erosion($image, $width, $height)
	{
		$im = imagecreate($width, $height);
		$black = imagecolorallocate($im, 0, 0, 0);
		$white = imagecolorallocate($im, 255, 255, 255);
		for ($i = 0; $i < $width; $i++)
		{
			for ($j = 0; $j < $height; $j++)
			{
				$count = 0;
				for ($di = -1; $di <= 1; $di++)
				{
					for ($dj = -1; $dj <= 1; $dj++)
					{
						$pixelrgb = imagecolorat($image, $i + $di, $j + $dj);
						$cols     = imagecolorsforindex($image, $pixelrgb);
						$r        = $cols['red'];
						if ($r < 127)
						{
							$count++;
						}
					}
				}
				$pixelrgb = imagecolorat($image, $i, $j);
				$cols     = imagecolorsforindex($image, $pixelrgb);
				$r        = $cols['red'];
				if ($count > 1 && $r < 127 && $i > 0 && $i < $width && $j > 0 && $j < $height)
				{
					imagesetpixel($im, $i, $j, $black);
				}
				else
				{
					imagesetpixel($im, $i, $j, $white);
				}
			}
		}

		return $im;
	}

	/**
	 * Integrate image through x
	 *
	 * @author Rijn
	 * @param image
	 * @return array
	 */
	public function x_cWave($image, $width, $height)
	{
		$wave = array();
		$peak = array();
		for ($i = 0; $i < $width; $i++)
		{
			$count = 0;
			for ($j = 0; $j < $height; $j++)
			{
				$pixelrgb = imagecolorat($image, $i, $j);
				$cols     = imagecolorsforindex($image, $pixelrgb);
				$r        = $cols['red'];
				if ($r < 127)
				{
					$count++;
				}
			}
			array_push($wave, $count);
		}

		for ($i = 0; $i < $width + 1; $i++)
		{
			if (($wave[$i - 1] > $wave[$i] && $wave[$i] < $wave[$i + 1]) || ($wave[$i - 1] >= $wave[$i] && $wave[$i] < $wave[$i + 1]) || ($wave[$i - 1] > $wave[$i] && $wave[$i] <= $wave[$i + 1]))
			{
				array_push($peak, $wave[$i]);
			}
			else
			{
				array_push($peak, $height - 1);
			}
		}

		$split = array();
		for ($i = 0, $count = 0; $i < $height && $count < 5; $i++)
		{
			for ($j = 0; $j < count($peak) && $count < 5; $j++)
			{
				if ($peak[$j] == $i)
				{
					$flag = true;
					for ($k = 0; $k < count($split); $k++)
					{
						if (abs($split[$k] - $j) < 7)
						{
							$flag = false;
						}
					}
					if ($flag)
					{
						array_push($split, $j);
						$count++;
					}
				}
			}
		}

		//print_r($split);

		return $split;
	}

	public function x_aWave($image)
	{
		$th = 127;

		$flag = false;
		$x0   = 0;
		while ( ! $flag && $x0 < imagesx($image))
		{
			$flag = false;
			for ($j = 0; $j < imagesy($image); $j++)
			{
				$pixelrgb = imagecolorat($image, $x0, $j);
				$cols     = imagecolorsforindex($image, $pixelrgb);
				$r        = $cols['red'];
				if ($r < $th)
				{
					$flag = true;
				}
			}
			$x0++;
		}
		$x0--;

		$flag = false;
		$x1   = imagesx($image) - 1;
		while ( ! $flag && $x1 > 0)
		{
			$flag = false;
			for ($j = 0; $j < imagesy($image); $j++)
			{
				$pixelrgb = imagecolorat($image, $x1, $j);
				$cols     = imagecolorsforindex($image, $pixelrgb);
				$r        = $cols['red'];
				if ($r < $th)
				{
					$flag = true;
				}
			}
			$x1--;
		}
		$x1++;

		$k = array();
		for ($p = 1; $p <= 3; $p++)
		{
			$t = round(($x1 - $x0) / 4 * $p) + $x0;
			$wave = array();
			for ($i = $t - 4; $i <= $t + 4; $i++)
			{
				$count = 0;
				for ($j = 0; $j < imagesy($image); $j++)
				{
					$pixelrgb = imagecolorat($image, $i, $j);
					$cols     = imagecolorsforindex($image, $pixelrgb);
					$r        = $cols['red'];
					if ($r < 127)
					{
						$count++;
					}
				}
				$wave[$i] = $count;
			}

			asort($wave);

			foreach ($wave as $key => $val)
			{
				$id = $key;
				break;
			}

			array_push($k, $id);
		}

		return array(
			$x0,
			$k[0],
			$k[1],
			$k[2],
			$x1,
		);
	}

	/**
	 * Rotate image to find thinest position
	 *
	 * @author Rijn
	 * @param image
	 * @return image
	 */
	public function Thinest($image, $x0, $x1, $height)
	{
		$im = imagecreate($x1 - $x0 + 1, $height);
		$black = imagecolorallocate($im, 0, 0, 0);
		$white = imagecolorallocate($im, 255, 255, 255);
		for ($i = $x0; $i <= $x1; $i++)
		{
			for ($j = 0; $j < $height; $j++)
			{
				$pixelrgb = imagecolorat($image, $i, $j);
				$cols     = imagecolorsforindex($image, $pixelrgb);
				$r        = $cols['red'];
				if ($r < 127)
				{
					imagesetpixel($im, $i - $x0, $j, $black);
				}
				else
				{
					imagesetpixel($im, $i - $x0, $j, $white);
				}
			}
		}

		$rem = null;
		$d   = $x1 - $x0 + 100;
		for ($angle = -30; $angle <= 30; $angle++)
		{
			$newwidth = ($x1 - $x0 + 1); // / cos(deg2rad($angle));
			$newheight = $height; /// cos(deg2rad($angle));
			$resize = imagecreatetruecolor($newwidth, $newheight);
			$white = imagecolorallocate($resize, 255, 255, 255);
			imagefill($resize, 0, 0, $white);

			imagecopyresampled($resize, $im, 0, 0, 0, 0, $newwidth, $newheight, $x1 - $x0 + 1, $height);

			$temp = imagerotate($resize, $angle, $white);

			$flag = false;
			$min  = 0;
			while ( ! $flag && $min < imagesx($temp))
			{
				$flag = false;
				for ($j = 0; $j < imagesy($temp); $j++)
				{
					$pixelrgb = imagecolorat($temp, $min, $j);
					$cols     = imagecolorsforindex($temp, $pixelrgb);
					$r        = $cols['red'];
					if ($r < 127)
					{
						$flag = true;
					}
				}
				$min++;
			}
			$min--;
			$flag = false;
			$max  = imagesx($temp) - 1;
			while ( ! $flag && $max >= 0)
			{
				$flag = false;
				for ($j = 0; $j < imagesy($temp); $j++)
				{
					$pixelrgb = imagecolorat($temp, $min, $j);
					$cols     = imagecolorsforindex($temp, $pixelrgb);
					$r        = $cols['red'];
					if ($r < 127)
					{
						$flag = true;
					}
				}
				$max--;
			}
			$max++;
			if ($max - $min + 1 < $d)
			{
				$d = $max - $min + 1;
				//echo ($d);
				$rem = imagecreatetruecolor($max - $min + 1, imagesy($temp));
				$white = imagecolorallocate($rem, 255, 255, 255);
				imagefill($rem, 0, 0, $white);
				imagecopyresized($rem, $temp, 0, 0, $min - 1, 0, $max - $min + 1, imagesy($temp), $max - $min + 1, imagesy($temp));

			}
			//echo ($min.",$max");
			//return $temp;
		}
		//echo ($rem);
		return $rem;
	}

	/**
	 * Trim image
	 *
	 * @author Rijn
	 * @param image
	 * @return image
	 */
	public function Trim($image)
	{
		$th = 127;

		$flag = false;
		$x0   = 0;
		while ( ! $flag && $x0 < imagesx($image))
		{
			$flag = false;
			for ($j = 0; $j < imagesy($image); $j++)
			{
				$pixelrgb = imagecolorat($image, $x0, $j);
				$cols     = imagecolorsforindex($image, $pixelrgb);
				$r        = $cols['red'];
				if ($r < $th)
				{
					$flag = true;
				}
			}
			$x0++;
		}
		$x0--;

		$flag = false;
		$x1   = imagesx($image) - 1;
		while ( ! $flag && $x1 >= 0)
		{
			$flag = false;
			for ($j = 0; $j < imagesy($image); $j++)
			{
				$pixelrgb = imagecolorat($image, $x1, $j);
				$cols     = imagecolorsforindex($image, $pixelrgb);
				$r        = $cols['red'];
				if ($r < $th)
				{
					$flag = true;
				}
			}
			$x1--;
		}
		$x1++;

		$flag = false;
		$y0   = 0;
		while ( ! $flag && $y0 < imagesy($image))
		{
			$flag = false;
			for ($j = 0; $j < imagesx($image); $j++)
			{
				$pixelrgb = imagecolorat($image, $j, $y0);
				$cols     = imagecolorsforindex($image, $pixelrgb);
				$r        = $cols['red'];
				if ($r < $th)
				{
					$flag = true;
				}
			}
			$y0++;
		}
		$y0--;

		$flag = false;
		$y1   = imagesy($image) - 1;
		while ( ! $flag && $y1 >= 0)
		{
			$flag = false;
			for ($j = 0; $j < imagesx($image); $j++)
			{
				$pixelrgb = imagecolorat($image, $j, $y1);
				$cols     = imagecolorsforindex($image, $pixelrgb);
				$r        = $cols['red'];
				if ($r < $th)
				{
					$flag = true;
				}
			}
			$y1--;
		}
		$y1 += 2;

		$result = imagecreatetruecolor($x1 - $x0 + 1, $y1 - $y0 + 1);
		$white  = imagecolorallocate($result, 255, 255, 255);
		imagefill($result, 0, 0, $white);
		imagecopyresized($result, $image, 0, 0, $x0 - 1, $y0 - 1, $x1 - $x0 + 1, $y1 - $y0 + 1, $x1 - $x0 + 1, $y1 - $y0 + 1);

		return $result;
	}

	/**
	 * This was image learning function
	 *
	 * @author Rijn
	 * @param image verify code
	 * @param string code
	 * @return true
	 */
	public function Init_Image($image, $string)
	{
		list($width, $height, $type, $attr) = getimagesize($image);
		$img = imagecreatefromgif($image);

		if (false)
		{
			$result = imagecreatetruecolor($width, $height * 4);
			$white  = imagecolorallocate($result, 255, 255, 255);
			imagefill($result, 0, 0, $white);

			imagecopy($result, $img, 0, 0, 0, 0, imagesx($img), imagesy($img));
		}

		$img = $this->Binaryzation($img, $width, $height);

		if (false)
		{
			imagecopy($result, $img, 0, $height, 0, 0, imagesx($img), imagesy($img));
		}

		$img = $this->Erosion($img, $width, $height);

		if (false)
		{
			imagecopy($result, $img, 0, $height * 2, 0, 0, imagesx($img), imagesy($img));
		}

		$split = $this->x_aWave($img, $width, $height);
		sort($split);
		$chars = array();
		for ($i = 0; $i < 4; $i++)
		{
			//echo ($split[$i]."-".$split[$i + 1]."<br/>");
			$chars[$i] = $this->Thinest($img, $split[$i] - 1, $split[$i + 1] + 1, $height);
			$chars[$i] = $this->Binaryzation2($chars[$i], imagesx($chars[$i]), imagesy($chars[$i]));
			$chars[$i] = $this->Trim($chars[$i]);

			if (false)
			{
				imagecopy($result, $chars[$i], $split[$i], $height * 3, 0, 0, imagesx($chars[$i]), imagesy($chars[$i]));
			}

			if ('*' != $string[$i] && imagesx($chars[$i]) > 3 && imagesy($chars[$i]) > 4)
			{
				$this->Resource[count($this->Resource)] = array(
					'image' => $chars[$i],
					'key'   => $string[$i],
				);
			}
		}

		//print_r($this->Resource);

		if (false)
		{
			$this->view($result);
		}

		return true;
	}

	public function Image_2_String($image)
	{
		$output = array();
		for ($y = 0; $y < imagesy($image); $y++)
		{
			$output[$y] = "";
			for ($x = 0; $x < imagesx($image); $x++)
			{
				$pixelrgb = imagecolorat($image, $x, $y);
				$cols     = imagecolorsforindex($image, $pixelrgb);
				$a        = $cols['red'];
				if ($a < 127)
				{
					$output[$y] .= "1";
				}
				else
				{
					$output[$y] .= "0";
				}
			}
		}
		return $output;
	}

	/**
	 * This was image compare function
	 *
	 * @author Rijn
	 * @param image a
	 * @param image b
	 * @return float
	 */
	public function Calc_Percentage($imageA, $imageB)
	{
		$widthA  = imagesx($imageA);
		$widthB  = imagesx($imageB);
		$heightA = imagesy($imageA);
		$heightB = imagesy($imageB);
		if (abs($widthA - $widthB) / ($widthB + $widthA) + abs($heightA - $heightB) / ($widthB + $widthA) > 0.6)
		{
			return -2;
		}

		$countA = 0;
		$countB = 0;

		for ($x = 0; $x < $widthB; $x++)
		{
			for ($y = 0; $y < $heightB; $y++)
			{
				$pixelrgb = imagecolorat($imageA, $x, $y);
				$cols     = imagecolorsforindex($imageA, $pixelrgb);
				$a        = $cols['red'];
				$pixelrgb = imagecolorat($imageB, $x, $y);
				$cols     = imagecolorsforindex($imageB, $pixelrgb);
				$b        = $cols['red'];
				if ($a < 127 && $b < 127)
				{
					$countA++;
				}
				if ($b < 127)
				{
					$countB++;
				}

			}
		}

		return $countA / $countB - abs($widthA - $widthB) / ($widthB + $widthA) - abs($heightA - $heightB) / ($widthB + $widthA);
	}

	public function Calc_Array_Percentage($arrayA, $arrayB)
	{
		$widthA  = strlen($arrayA[0]);
		$widthB  = strlen($arrayB[0]);
		$heightA = count($arrayA);
		$heightB = count($arrayA);
		if (abs($widthA - $widthB) / ($widthB + $widthA) + 2 * abs($heightA - $heightB) / ($widthB + $widthA) > 0.5)
		{
			return -2;
		}

		$countA = 0;
		$countB = 0;

		for ($x = 0; $x < $widthB/*|| $x < $widthA*/; $x++)
		{
			for ($y = 0; $y < $heightB/*|| $y < $heightA*/; $y++)
			{
				$a = @$arrayA[$y][$x];
				$b = @$arrayB[$y][$x];
				if (1 == $a && 1 == $b)
				{
					$countA++;
				}
				if (1 == $b || 1 == $a)
				{
					$countB++;
				}

			}
		}

		return $countA / $countB - abs($widthA - $widthB) / ($widthB + $widthA) - 2 * abs($heightA - $heightB) / ($widthB + $widthA);
	}

	/**
	 * This was identify verify code function.
	 *
	 * @author Rijn
	 * @param image verify code
	 * @return string
	 */
	public function Recognize_Image($image)
	{
		$result = array();
		$text   = "";

		list($width, $height, $type, $attr) = getimagesize($image);
		$img = imagecreatefromgif($image);

		$img   = $this->Binaryzation($img, $width, $height);
		$img   = $this->Erosion($img, $width, $height);
		$split = $this->x_aWave($img, $width, $height);
		sort($split);

		$chars = array();
		for ($i = 0; $i < 4; $i++)
		{
			$chars[$i] = $this->Thinest($img, $split[$i] - 1, $split[$i + 1] + 1, $height);
			$chars[$i] = $this->Binaryzation2($chars[$i], imagesx($chars[$i]), imagesy($chars[$i]));
			$chars[$i] = $this->Erosion($chars[$i], imagesx($chars[$i]), imagesy($chars[$i]));
			$chars[$i] = $this->Trim($chars[$i]);
			$chars[$i] = $this->Image_2_String($chars[$i]);
		}

		for ($k = 0; $k < 4; $k++)
		{
			$result = array();
			$sort   = array();

			for ($i = 0; $i < count($this->Resource); $i++)
			{
				$key = $this->Resource[$i]['key'];
				$p   = $this->Calc_Array_Percentage($chars[$k], $this->Resource[$i]['array']);
				array_push($result,
					(object) array(
						'key' => $key,
						'p'   => $p,
						'id'  => $i,
					)
				);
				array_push($sort, $result[$i]->p);
			}

			usort($result, "cmp");

			//var_dump($result[0]);

			/*arsort($sort);

			foreach ($sort as $key => $val)
			{
			$id = $key;
			break;
			}

			echo ("[$id] ".$result[$id]->key." => ".$result[$id]->p."<br/>");
			*/
			//echo ("[".$result[0]->id."] ".$result[0]->key." => ".$result[0]->p."<br/>");
			$text .= $result[0]->key;

		}
		return $text;
	}

	public function Optimize_Database($image, $result)
	{
		list($width, $height, $type, $attr) = getimagesize($image);
		$img = imagecreatefromgif($image);

		$img   = $this->Binaryzation($img, $width, $height);
		$img   = $this->Erosion($img, $width, $height);
		$split = $this->x_aWave($img, $width, $height);
		sort($split);

		$chars = array();
		for ($k = 0; $k < 4; $k++)
		{
			$chars[$k] = $this->Thinest($img, $split[$k] - 1, $split[$k + 1] + 1, $height);
			$chars[$k] = $this->Binaryzation2($chars[$k], imagesx($chars[$k]), imagesy($chars[$k]));
			$chars[$k] = $this->Trim($chars[$k]);

			$a = $result[$k];

			for ($i = 0; $i < count($this->Resource); $i++)
			{
				$key = $this->Resource[$i]['key'];

				if ($key == $a)
				{
					$p = $this->Calc_Percentage($chars[$k], $this->Resource[$i]['image']);

					if ($p < 1)
					{
						//echo ("[$i] => ($key) => $p <br/>");
						$this->Resource[$i]['d'] = (@$this->Resource[$i]['d'] + $p);
					}

				}

			}
		}
	}

	public function Trim_Database()
	{
		$output = array();
		$list   = array();

		$sort = array();

		for ($i = 0; $i < count($this->Resource); $i++)
		{
			array_push($sort, $this->Resource[$i]['d']);
		}

		arsort($sort);

		//var_dump($sort);

		foreach ($sort as $id => $val)
		{
			$key = $this->Resource[$id]['key'];

			if ($list[$key] < 30)
			{
				$list[$key] = @$list[$key] + 1;
				array_push($output, $this->Resource[$id]);
			}
		}

		$this->Resource = $output;

		for ($i = 0; $i < count($this->Resource) - 1; $i++)
		{
			for ($j = $i + 1; $j < count($this->Resource); $j++)
			{
				if ($this->Resource[$i]['key'] > $this->Resource[$j]['key'])
				{
					$temp = $this->Resource[$i];
					$this->Resource[$i] = $this->Resource[$j];
					$this->Resource[$j] = $temp;

				}
			}
		}

		for ($i = 0; $i < count($this->Resource) - 1; $i++)
		{
			for ($j = $i + 1; $j < count($this->Resource); $j++)
			{
				if ($this->Resource[$i]['d'] < $this->Resource[$j]['d'] && $this->Resource[$i]['key'] == $this->Resource[$j]['key'])
				{
					$temp = $this->Resource[$i];
					$this->Resource[$i] = $this->Resource[$j];
					$this->Resource[$j] = $temp;
				}
			}
		}

		ksort($list);

		foreach ($list as $id => $val)
		{
			echo ("[$id]=>$val<br/>");
		}

		echo ("<br/>");

		for ($i = 0; $i < count($this->Resource); $i++)
		{
			$key = $this->Resource[$i]['key'];
			echo ("[$key]=>".$this->Resource[$i]['d']."<br/>");
		}

		return true;
	}

	public function View_Database($sort = true)
	{
		$result = imagecreatetruecolor(600, (int) (count($this->Resource) / 10 * 35 + 100));
		$white  = imagecolorallocate($result, 255, 255, 255);
		$black  = imagecolorallocate($result, 0, 0, 0);
		imagefill($result, 0, 0, $white);
		imagestring($result, 2, 5, 0, "Database count = ".count($this->Resource), $black);

		for ($i = 0; $i < count($this->Resource); $i++)
		{
			$this->Resource[$i]['id'] = $i;
		}

		if ($sort)
		{
			for ($i = 0; $i < count($this->Resource) - 1; $i++)
			{
				for ($j = $i + 1; $j < count($this->Resource); $j++)
				{
					if ($this->Resource[$i]['key'] > $this->Resource[$j]['key'])
					{
						$temp = $this->Resource[$i];
						$this->Resource[$i] = $this->Resource[$j];
						$this->Resource[$j] = $temp;

					}
				}
			}

			for ($i = 0; $i < count($this->Resource) - 1; $i++)
			{
				for ($j = $i + 1; $j < count($this->Resource); $j++)
				{
					if ($this->Resource[$i]['d'] < $this->Resource[$j]['d'] && $this->Resource[$i]['key'] == $this->Resource[$j]['key'])
					{
						$temp = $this->Resource[$i];
						$this->Resource[$i] = $this->Resource[$j];
						$this->Resource[$j] = $temp;
					}
				}
			}
		}

		for ($i = 0; $i < count($this->Resource); $i++)
		{
			$image = $this->Resource[$i]['image'];
			$key   = $this->Resource[$i]['key'];

			imagecopyresized($result, $image, (int) ($i % 10) * 60 + 5, (int) ($i / 10) * 35 + 25, 0, 0, imagesx($image), imagesy($image), imagesx($image), imagesy($image));

			imagechar($result, 2, (int) ($i % 10) * 60 + 20, (int) ($i / 10) * 35 + 25, $key, $black);
			imagestring($result, 2, (int) ($i % 10) * 60 + 25, (int) ($i / 10) * 35 + 25, "[".$this->Resource[$i]['id']."]", $black);
			imagestring($result, 2, (int) ($i % 10) * 60, (int) ($i / 10) * 35 + 40, "d=".(int) ($this->Resource[$i]['d'] * 1000), $black);
		}
		$this->view($result);
	}

	public function view($image)
	{
		header('Content-Type: image/jpeg');
		imagejpeg($image, NULL, 100);
	}

}
