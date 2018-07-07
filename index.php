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

$today = date("F j, Y, g:i a");
$dt = date("U");

$in = array();

$in['step'] = 0;
foreach ($_REQUEST as $k => $val) {
    $in[$k] = split_stitch::get_param($k);
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
    for ($k = 0; $k < $timelines; $k++) {
        if (!$in['part'][$k]['end']) {
            continue;
        }
        echo "<select name=part_order[$k]>";
        for ($i = 0; $i < $timelines; $i++) {
            if (!$in['part'][$i]['end']) {
                continue;
            }
            $selected='';
            if( $i==$k ) {
              $selected='selected';
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
  <input type='hidden'  name='step' id='step' value='20'>
  <input type='hidden'  name='input' id='input' value='" . $in['input'] . " '>
  <input type='hidden'  name='part' id='part' value='" . $in['part'] . " '>
  </form>
</body>
</html>
";
    exit(0);
}


$data = null;
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

$data = null;
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
    for ($i = 0; $i < $timelines; $i++) {
        $start = round(($i) * $data['duration'] / $timelines, 1);
        $end = round(($i + 1) * $data['duration'] / $timelines, 1);
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
            <td></td>
            <td></td>
            <td><input type='submit'  name='save' id='save' value='Go'> </td>
          </tr>
			</table>
			<input type='hidden'  name='step' id='step' value='20'>
			<input type='hidden'  name='input' id='input' value='" . $in['input'] . " '>
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
				<td><input type='submit'  name='save' id='save' value='Go'> </td>
			</tr>
			</table>
			<input type='hidden'  name='step' id='step' value='10'>
			</form>
		</body>
	</html>
	";
    exit(0);
}

if (split_stitch::get_param('new')) {
    echo "<h3>Add new project</h3>
			<form action='index.php' method='post' multipart='' enctype='multipart/form-data'>
			<table>
			<tr>
				<td><input type='text' name='project_name' value='New project $today' size=50></td>
				<td><input type='submit'  name='save' id='save' value='Save'> </td>

			</tr>
			</table>
			<input type='hidden'  name='add' id='add' value='1'>
			</form>
		</body>
	</html>
	";
    exit(0);
}

if (split_stitch::get_param('add')) {
    $project = array();
    $project_name = split_stitch::get_param('project_name');
    $project_id = $dt . sha1($project_name . $today);
    $project['project_name'] = $project_name;
    $project['project_id'] = $project_id;
    $project['main_upload_dir'] = $main_upload_dir;

    if (!mkdir("$main_upload_dir/$project_id", 0777, true)) {
        echo "<h2><font color=red>Error! Cannot make the directory $main_upload_dir/$project_id</font></h2>";
        exit(0);
    }
    $myfile = fopen("$main_upload_dir/$project_id/project.txt", "w");
    if (!$myfile) {
        "<h2><font color=red>Error! Unable to save file $main_upload_dir/$project_id/project.txt</font></h2>";
        exit(0);
    }
    fwrite($myfile, json_encode($project));
    fclose($myfile);

    echo "<h3>Project '$project_name' created</h3>
			<form action='upload_images.php' method='post' multipart='' enctype='multipart/form-data'>
			<table>
			<tr>
				<td><input type='submit'  name='save' id='save' value='Add images to project'> </td>
			</tr>
			</table>
			<input type='hidden' name='project_id' value='$project_id'>
			</form>
		</body>
	</html>
	";
    // renew the fonts list
    $command = "find /usr/share/fonts/truetype/ |grep ttf\$ >$main_upload_dir/fonts.txt 2>/dev/null";
    #$command="convert -list font | awk '/Font:/ {print $2}' >$basedir/fonts.txt 2>/dev/null";
    exec($command);
    exit(0);
}

if (split_stitch::get_param('del') && split_stitch::get_param('project_id')) {
    $project_id = split_stitch::get_param('project_id');
    if (file_exists("$main_upload_dir/$project_id/project.txt")) {
        exec("rm -rf $main_upload_dir/$project_id");
        echo "<h2><font color=green>Project with id '$project_id' removed</font></h2>";
    } else {
        echo "<h2><font color=red>Error! Cannot remove the project '$project_id'</font></h2>";
    }
}

echo '
			<table>
			<tr>
				<td bgcolor=#FDF2FF><a href="index.php?new=1">Add new project</a></td>
				<td></td>
				<td></td>
			</tr>

		';
$Dirs = scandir($main_upload_dir, SCANDIR_SORT_DESCENDING);
/*        echo '<pre>';
echo var_dump($Dirs );
echo '</pre>';
echo '<pre>';
echo var_dump($dir );
echo '</pre>';
 */
foreach ($Dirs as $dir) {
    if (file_exists("$main_upload_dir/$dir/project.txt")) {
        $string = file_get_contents("$main_upload_dir/$dir/project.txt");
        $project = json_decode($string, true);
        $project_name = $project['project_name'];
        $project_id = $project['project_id'];

        echo "	<tr>
							<td><a href='edit_effect.php?project_id=$project_id'>$project_name</a></td>
							<td>[ <a href='clone_project.php?old_project_id=$project_id'> Clone this project</a> ]</td>
							<td>[ <a href='' onclick=\"confirm_prompt( 'Are you sure to remove this project?','?del=1&project_id=$project_id'); return false;\">Remove this project</a> ]</td>
						</tr>\n";
    }
}
echo '
				</form>
			</table>

		</body>
	</html>
';

?>

</body>
</html>