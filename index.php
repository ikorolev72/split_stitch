<?php
/*
Split and stitch video
Author Korolev Igor
https://github.com/ikorolev72
2018.07.08
version 1.0
*/
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Split and stitch video</title>
	<LINK href='main.css' type=text/css rel=stylesheet>
<script>
  function confirm_prompt( text,url ) {
     if (confirm( text )) {
      window.location = url ;
    }
  }

function checkTime(t) {
  let re = /^(\d\d):(\d\d):(\d\d(\.\d*)?)$/;
  if (re.test(t)) {
    return (true);
  }
  re = /^(\d\d):(\d\d(\.\d*)?)$/;
  if (re.test(t)) {
    return (true);
  }
  alert( "incorrect time value "+t+ ".  Must be in [HH:]MM:SS[.m...] format. Eg 00:00:15.02")
  return (false);
}


</script>
</head>
<body>

<?php
echo "
<a href=".$_SERVER['PHP_SELF']."> Home </a>
<h2>Split and stitch video</h2> 
";

include_once "split_stitch.php";

//$debug = true;
$debug = false;
$basedir = dirname(__FILE__);
$main_upload_dir = "$basedir/uploads/";
$main_upload_url = "./uploads";
$bin_dir = "$basedir/bin";
$tmp_dir = "/tmp";
$timelines = 6;
$fadeDuration = 0.5;

$today = date("F j, Y, g:i a");
$dt = date("U");

$in = array();
$data = null;

$json_in = split_stitch::get_param('in');
if ($json_in) {
    $in = json_decode($json_in, true);
    if (!$in) {
        split_stitch::$errors[] = "Incorrect json string in parameter 'in'";
        echo split_stitch::showErrors();
        echo split_stitch::showMessages();
        exit(1);
    }
} else {
    $in = array();
    $in['step'] = 0;
}

foreach ($_REQUEST as $k => $val) {
    if ('in' == $k) {
        continue;
    }

    $in[$k] = split_stitch::get_param($k);
}
if ($debug) {
    echo "<pre>";
    echo var_dump($in);
    echo "</pre>";
}

$string = json_encode($in, JSON_PRETTY_PRINT);

// processing video
if (40 == $in['step']) {
    $tempVideos = array();

    for ($k = 0; $k < $in['parts']; $k++) {
        $index = $in['part_order'][$k];
        echo "<font color=green>Split video to parts. Part $k </font><br>";
        flush();
        ob_flush();
        $rnd = rand(10000, 99999);
        $tempLog = "${tmp_dir}/${dt}_${rnd}.log";

        $tempOut = "${tmp_dir}/${dt}_${rnd}.ts";
        $tempVideos[] = $tempOut;
        $fadeIn = true;
        $fadeOut = true;
        if (0 == $k) {
            $fadeIn = false;
        }
        if ($in['parts'] == ($k + 1)) {
            $fadeOut = false;
        }

//        $cmd = split_stitch::splitVideo($in['input'], $tempOut, $in['part'][$index]['start'], $in['part'][$index]['end']) . " >$tempLog 2>&1";
        $cmd = split_stitch::splitVideoFade($in['input'], $tempOut, $in['part'][$index]['start'], $in['part'][$index]['end'], $fadeIn, $fadeOut, $fadeDuration) . " >$tempLog 2>&1";
        if ($debug) {
            echo "$cmd<br>";
        }
        if (!split_stitch::doExec($cmd)) {
            split_stitch::$errors[] = "Fatal error. Something wrong while doing command: $cmd . Please, check log file $tempLog";
            echo split_stitch::showErrors();
            exit(1);
        }
        @unlink($tempLog);
    }

    // stitch videos
    echo "<font color=green>Stitch videos into entire video</font><br>";
    flush();
    ob_flush();

    $rnd = rand(10000, 99999);
    $tempLog = "${tmp_dir}/${dt}_${rnd}.log";
    $tempOut = "${tmp_dir}/${dt}_${rnd}.mp4";
    $cmd = split_stitch::stitchVideo($tempVideos, $tempOut) . " >$tempLog 2>&1";
    if ($debug) {
        echo "$cmd<br>";
    }
    if (!split_stitch::doExec($cmd)) {
        split_stitch::$errors[] = "Fatal error. Something wrong while doing command: $cmd . Please, check log file $tempLog";
        echo split_stitch::showErrors();
        exit(1);
    }
    // unlink temp files
    foreach ($tempVideos as $filename) {
        @unlink($filename);
    }

    if (file_exists($in['output'])) {
        if (isset($in['overwrite']) && $in['overwrite']) {
            $rnd = rand(10000, 99999);
            $in['output'] = $in['output'] . "_${rnd}.mp4";
        }
    }
    if (@rename($tempOut, $in['output'])) {
        split_stitch::$messages[] = "Congratulation! All ok! Your video file: " . $in['output'];
        @unlink($tempOut);
        @unlink($tempLog);
    } else {
        split_stitch::$messages[] = "Congratulation! Your video file: $tempOut";
        split_stitch::$errors[] = "Your file is ready, but I cannot reaname temporary video file to output filename";
        @unlink($tempLog);
    }
    echo split_stitch::showErrors();
    echo split_stitch::showMessages();
    exit(0);
}

// check right data fr step 10
if (30 == $in['step']) {
    split_stitch::$messages[] = "Processing file '" . $in['input'] . "'";
    echo split_stitch::showErrors();
    echo split_stitch::showMessages();
    if (!isset($in['output'])) {
        $in['output'] = split_stitch::generateOutputFilename($in['input']);
    }
    echo "<h3>Please, enter output filename for stiched video</h3>
<form action='index.php' method='post' multipart='' enctype='multipart/form-data'>
<table>
<tr>
  <td><input type='text' name='output' value='" . $in['output'] . "' size=50></td>
</tr>
<tr>
  <td>Overwrite if exists <input type='checkbox' name='overwrite' value='1' checked='cheched'> </td>
</tr>
<tr>
  <td><input type='submit'  name='save' id='save' value='Go'> </td>
</tr>
</table>
<input type='hidden'  name='step' id='step' value='40'>
<input type='hidden'  name='in' id='in' value='$string'>
</form>
</body>
</html>
";
    exit(0);
}

// check right data fr step 10
if (20 == $in['step']) {
    split_stitch::$messages[] = "Processing file '" . $in['input'] . "'";
    echo split_stitch::showErrors();
    echo split_stitch::showMessages();
    echo "<h3>Please, enter orders of parts in stiched video</h3>
  <form action='index.php' method='post' multipart='' enctype='multipart/form-data'>
  <table>
    <tr>
      <td>
    ";
    for ($k = 0; $k < $in['parts']; $k++) {
        if (!$in['part'][$k]['end']) {
            continue;
        }
        echo "<select name=part_order[$k]>";
        for ($i = 0; $i < $in['parts']; $i++) {
            if (!$in['part'][$i]['end']) {
                continue;
            }
            $selected = '';
            if ($i == $k) {
                $selected = 'selected';
            }
            echo "<option value=$i $selected>Part $i</option> ";
        }
        echo "</select>";
    }
    echo "
        </td>
      </tr>
    ";

    echo "
      <tr>
        <td><input type='submit'  name='save' id='save' value='Go'> </td>
      </tr>
  </table>
  <input type='hidden'  name='step' id='step' value='30'>
  <input type='hidden'  name='in' id='in' value='$string'>
  </form>
</body>
</html>
";
    exit(0);
}

// check right data fr step 10
if (10 == $in['step']) {
    if (!file_exists($in['input'])) {
        split_stitch::$errors[] = "File " . $in['input'] . "do not exists.";
        $in['step'] = 0;
    }
    if (!split_stitch::getStreamInfo($in['input'], 'video', $data)) {
        split_stitch::$errors[] = "Sommething wrong with file " . $in['input'] . " . Cannot get info about video stream in this file.";
        $in['step'] = 0;
    }
    if (!isset($data['duration']) || 0 == $data['duration']) {
        split_stitch::$errors[] = "Sommething wrong with file " . $in['input'] . " . Cannot get duration of video stream in file.";
        $in['step'] = 0;
    }
}

if (10 == $in['step']) {
    split_stitch::$messages[] = "Processing file '" . $in['input'] . "'";
    echo split_stitch::showErrors();
    echo split_stitch::showMessages();
    echo "<h3>Please, enter split timelines ( format [HH:]MM:SS[.m...])</h3>
			<form action='index.php' method='post' multipart='' enctype='multipart/form-data'>
      <table>
        <tr>
          <td>Part no</td>
          <td>Start</td>
          <td>End</td>
        </tr>
        ";
    for ($i = 0; $i < $in['parts']; $i++) {
        $start = split_stitch::float2time(($i) * $data['duration'] / $in['parts'], 1);
        $end = split_stitch::float2time(($i + 1) * $data['duration'] / $in['parts'], 1);

        echo "
          <tr>
            <td>Part $i</td>
            <td>
              <input type='text' name='part[$i][start]' id='part[$i][start]' value='$start' onchange='checkTime(this.value)' >
            </td>
            <td>
              <input type='text' name='part[$i][end]' id='part[$i][end]' value='$end'  onchange='checkTime(this.value)'>
            </td>
<!--
            <td>
              <input type='number'  step=0.1 name='part[$i][start]' id='part[$i][start]' value='$start' min=0 max=" . $data['duration'] . ">
            </td>
            <td>
              <input type='number'  step=0.1 name='part[$i][end]' id='part[$i][end]' value='$end' min=0 max=" . $data['duration'] . ">
            </td>
-->
          </tr>
        ";
    }
    echo "
          <tr>
            <td><input type='submit'  name='save' id='save' value='Go'> </td>
            <td></td>
            <td></td>
          </tr>
			</table>
			<input type='hidden'  name='step' id='step' value='20'>
      <input type='hidden'  name='in' id='in' value='$string'>
			</form>
		</body>
	</html>
	";
    exit(0);
}

// check right data for step 5
// add parts
if (5 == $in['step']) {
    if (!file_exists($in['input'])) {
        split_stitch::$errors[] = "File " . $in['input'] . "do not exists.";
        $in['step'] = 0;
    }
}

if (5 == $in['step']) {
    split_stitch::$messages[] = "Processing file '" . $in['input'] . "'";
    echo split_stitch::showErrors();
    echo split_stitch::showMessages();
    echo "<h3>Please, enter how many parts you will split</h3>
  <form action='index.php' method='post' multipart='' enctype='multipart/form-data'>
  <table>

      <tr>
        <td>Parts</td>
        <td>
          <input type='number'  step=1 name='parts' id='parts' value='2' min=2 max=$timelines>
        </td>
      </tr>
      <tr>
        <td><input type='submit'  name='save' id='save' value='Go'> </td>
        <td></td>
      </tr>
  </table>
  <input type='hidden'  name='step' id='step' value='10'>
  <input type='hidden'  name='in' id='in' value='$string'>
  </form>
</body>
</html>
";
    exit(0);
}

// if step 0
if (!$in['step']) {
    echo split_stitch::showErrors();
    echo split_stitch::showMessages();
    if (!isset($in['input'])) {
        $in['input'] = '';
    }
    echo "<h3>Please, enter path of video file</h3>
			<form action='index.php' method='post' multipart='' enctype='multipart/form-data'>
			<table>
			<tr>
        <td><input type='text' name='input' value='" . $in['input'] . "' size=50></td>
      </tr>
      <tr>
				<td><input type='submit'  name='save' id='save' value='Go'> </td>
			</tr>
			</table>
			<input type='hidden'  name='step' id='step' value='5'>
			</form>
		</body>
	</html>
	";
    exit(0);
}

?>

</body>
</html>