<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Project list</title>
	<LINK href='main.css' type=text/css rel=stylesheet>
<script>
  function confirm_prompt( text,url ) {
     if (confirm( text )) {
      window.location = url ;
    }
  }
</script>
</head>
<body>
<h2>Projects</h2>
<?php
include_once "split_stitch.php";

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

$in['step'] = 0;
foreach ($_REQUEST as $k => $val) {
    $in[$k] = split_stitch::get_param($k);
}

echo "<pre>";
echo var_dump($in);
echo "</pre>";


// processing video
if (40 == $in['step']) {
    $tempVideos = array();

    for ($i = 0; $i < $in['parts']; $i++) {
        echo "<font color=green>Split video to parts. Part $i </font><br>";
        $rnd = rand(10000, 99999);
        $tempOut = "${tmp_dir}/${dt}_${rnd}.ts";
        $cmd = split_stitch::splitVideo($in['input'], $tempOut, $in['part'][$i]['start'], $in['part'][$i]['end']);
        echo "$cmd\n";
    }
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
<input type='hidden'  name='input' id='input' value='" . $in['input'] . "'>
<input type='hidden'  name='part' id='part' value='" . $in['part'] . "'>
<input type='hidden'  name='parts' id='parts' value='" . $in['parts'] . "'>
<input type='hidden'  name='part_order' id='part_order' value='" . $in['part_order'] . "'>

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
  <input type='hidden'  name='input' id='input' value='" . $in['input'] . "'>
  <input type='hidden'  name='part' id='part' value='" . $in['part'] . "'>
  <input type='hidden'  name='parts' id='parts' value='" . $in['parts'] . "'>
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
    echo "<h3>Please, enter split timelines ( in second ). If you wish ignore one or several parts, please, set end value to 0 (zero)</h3>
			<form action='index.php' method='post' multipart='' enctype='multipart/form-data'>
      <table>
        <tr>
          <td>Part no</td>
          <td>Start</td>
          <td>End</td>
        </tr>
        ";
    for ($i = 0; $i < $in['parts']; $i++) {
        $start = round(($i) * $data['duration'] / $in['parts'], 1);
        $end = round(($i + 1) * $data['duration'] / $in['parts'], 1);
        echo "
          <tr>
            <td>Part $i</td>
            <td>
              <input type='number'  step=0.1 name='part[$i][start]' id='part[$i][start]' value='$start' min=0 max=" . $data['duration'] . ">
            </td>
            <td>
              <input type='number'  step=0.1 name='part[$i][end]' id='part[$i][end]' value='$end' min=0 max=" . $data['duration'] . ">
            </td>
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
			<input type='hidden'  name='input' id='input' value='" . $in['input'] . "'>
			<input type='hidden'  name='parts' id='parts' value='" . $in['parts'] . "'>
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
  <input type='hidden'  name='input' id='input' value='" . $in['input'] . "'>
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