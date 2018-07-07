<?php
class split_stitch
{

    public static $ffmpeg = "ffmpeg";
    public static $ffprobe = "ffprobe";
    public static $errors = array();
    public static $messages = array();

    public function showErrors()
    {
        $menu = "<font color='red'>" . join("<br>", self::$errors) . "</font><hr>";
        return $menu;
    }

    public function showMessages()
    {
        $menu = "<font color='green'>" . join("<br>", self::$messages) . "</font><hr>";
        return $menu;
    }

    public function reArrayFiles($file)
    {
        $file_ary = array();
        $file_count = count($file['name']);
        $file_key = array_keys($file);

        for ($i = 0; $i < $file_count; $i++) {
            foreach ($file_key as $val) {
                $file_ary[$i][$val] = $file[$val][$i];
            }
        }
        return $file_ary;
    }

    public function get_param($val)
    {
        global $_POST;
        global $_GET;
        $ret = isset($_POST[$val]) ? $_POST[$val] :
        (isset($_GET[$val]) ? $_GET[$val] : null);
        return $ret;
    }

    public function copy_files($src, $dst, $allowed)
    {
        $dir = opendir($src);
        #@mkdir($dst);
        while (false !== ($file = readdir($dir))) {
            $ext = pathinfo($file, PATHINFO_EXTENSION);
            if (in_array($ext, $allowed)) {
                link("$src/$file", "$dst/$file");
                # copy($src . '/' . $file,$dst . '/' . $file);
            }
        }
        closedir($dir);
        return true;
    }

    public function save_settings($item, $keys)
    {
        $saved_values = array();
        foreach ($keys as $k) {
            if (isset($item[1][$k])) {
                $saved_values[$k] = $item[1][$k];
            }

        }
        return ($saved_values);
    }

/**
 * getStreamInfo
 * function get info about video or audio stream in the file
 *
 * @param    string $fileName
 * @param    string $streamType    must be  'audio' or 'video'
 * @param    array &$data          return data
 * @return    integer 1 for success, 0 for any error
 */
    public function getStreamInfo($fileName, $streamType, &$data)
    {
        # parameter - 'audio' or 'video'
        $ffprobe = self::$ffprobe;

        if (!$probeJson = json_decode(`"$ffprobe" $fileName -v quiet -hide_banner -show_streams -of json`, true)) {
            self::writeToLog("Cannot get info about file $fileName");
            return 0;
        }
        if (empty($probeJson["streams"])) {
            self::writeToLog("Cannot get info about streams in file $fileName");
            return 0;
        }
        foreach ($probeJson["streams"] as $stream) {
            if ($stream["codec_type"] == $streamType) {
                $data = $stream;
                break;
            }
        }

        if (empty($data)) {
            self::writeToLog("File $fileName :  stream not found");
            return 0;
        }
        if ('video' == $streamType) {
            if (empty($data["height"]) || !intval($data["height"]) || empty($data["width"]) || !intval($data["width"])) {
                self::writeToLog("File $fileName : invalid or corrupt dimensions");
                return 0;
            }
        }

        return 1;
    }

/**
 * writeToLog
 * function print messages to console
 *
 * @param    string $message
 * @return    string
 */
    public function writeToLog($message)
    {
        #echo "$message\n";
        fwrite(STDERR, "$message\n");
    }

/**
 * doExec
 * @param    string    $Command
 * @return integer 0-error, 1-success
 */

    public function doExec($Command)
    {
        $outputArray = array();
        exec($Command, $outputArray, $execResult);
        if ($execResult) {
            self::writeToLog(join("\n", $outputArray));
            return 0;
        }
        return 1;
    }

}
