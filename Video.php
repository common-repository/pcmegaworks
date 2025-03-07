<?php
if(session_id() == '' || !isset($_SESSION))
  // We need session here
  session_start();
require_once( $_SERVER['DOCUMENT_ROOT'] . '/wp-config.php' );
require_once('PCPluginHeader.php');
//sanitize our GET
$arrGET = filter_var_array($_GET,FILTER_SANITIZE_STRING);
if(array_key_exists('videoid',$arrGET) && (int)$arrGET['videoid'] > 0){
  if($objVideoData = PCMW_VideoAccess::Get()->GetVideo($arrGET['videoid'],$_SESSION['CURRENTUSER']['pcgroup']['admingroupid'],TRUE)){
    $strVideoSource = str_replace(get_site_url().'/',PCMW_ConfigCore::Get()->objConfig->GetServerPath().DIRECTORY_SEPARATOR,$objVideoData->strVideoSource);
    $strVideoSource = str_replace('/',DIRECTORY_SEPARATOR,$strVideoSource);
    $objStream = new PCMW_VideoStream($strVideoSource);
    $objStream->objFileData = $objVideoData;
    $objStream->start();
  }
  else{
  PCMW_Logger::Debug('Video ['.$arrGET['videoid'].'] not available. LINE['.__LINE__.']',1);
   exit;
  }
}
else{
  PCMW_Logger::Debug('Video ['.$arrGET['videoid'].'] not available. LINE['.__LINE__.']',1);
  exit;
}
/**
 * Description of VideoStream
 *
 * @author Rana
 * @link http://codesamplez.com/programming/php-html5-video-streaming-tutorial
 */
class PCMW_VideoStream
{
    private $path = "";
    private $stream = "";
    private $buffer = 102400;
    private $start  = -1;
    private $end    = -1;
    private $size   = 0;
    public $objFileData;

    function __construct($filePath)
    {
        $this->path = $filePath;
    }

    /**
     * Open stream
     */
    private function open()
    {
        if (!($this->stream = fopen($this->path, 'rb'))) {
            PCMW_Logger::Debug('Could not open stream for reading ['.$this->path.'] proof ['.$this->objFileData->intVideoId.'] ['.__LINE__.']',1);
            die('Could not open stream for reading');
        }

    }

    /**
     * Set proper header to serve the video content
     */
    private function setHeader()
    {
        ob_get_clean();
        header("Content-Type: ".$this->objFileData->strVideoType."");
        header("Cache-Control: max-age=2592000, public");
        header("Expires: ".gmdate('D, d M Y H:i:s', time()+2592000) . ' GMT');
        header("Last-Modified: ".gmdate('D, d M Y H:i:s', @filemtime($this->path)) . ' GMT' );
        $this->start = 0;
        $this->size  = filesize($this->path);
        $this->end   = $this->size - 1;
        header("Accept-Ranges: 0-".$this->end);

        if (isset($_SERVER['HTTP_RANGE'])) {

            $c_start = $this->start;
            $c_end = $this->end;

            list(, $range) = explode('=', $_SERVER['HTTP_RANGE'], 2);
            if (strpos($range, ',') !== false) {
                PCMW_Logger::Debug('HTTP/1.1 416 Requested Range Not Satisfiable ['.__LINE__.']',1);
                header('HTTP/1.1 416 Requested Range Not Satisfiable');
                header("Content-Range: bytes $this->start-$this->end/$this->size");
                exit;
            }
            if ($range == '-') {
                $c_start = $this->size - substr($range, 1);
            }else{
                $range = explode('-', $range);
                $c_start = $range[0];

                $c_end = (isset($range[1]) && is_numeric($range[1])) ? $range[1] : $c_end;
            }
            $c_end = ($c_end > $this->end) ? $this->end : $c_end;
            if ($c_start > $c_end || $c_start > $this->size - 1 || $c_end >= $this->size) {
                PCMW_Logger::Debug('HTTP/1.1 416 Requested Range Not Satisfiable ['.__LINE__.']',1);
                header('HTTP/1.1 416 Requested Range Not Satisfiable');
                header("Content-Range: bytes $this->start-$this->end/$this->size");
                exit;
            }
            $this->start = $c_start;
            $this->end = $c_end;
            $length = $this->end - $this->start + 1;
            fseek($this->stream, $this->start);
            header('HTTP/1.1 206 Partial Content');
            header("Content-Length: ".$length);
            header("Content-Range: bytes $this->start-$this->end/".$this->size);
        }
        else
        {
            header("Content-Length: ".$this->size);
        }

    }

    /**
     * close curretly opened stream
     */
    private function end()
    {
        fclose($this->stream);
        exit;
    }

    /**
     * perform the streaming of calculated range
     */
    private function stream()
    {
        $i = $this->start;
        set_time_limit(0);
        while(!feof($this->stream) && $i <= $this->end) {
            $bytesToRead = $this->buffer;
            if(($i+$bytesToRead) > $this->end) {
                $bytesToRead = $this->end - $i + 1;
            }
            $data = fread($this->stream, $bytesToRead);
            echo $data;
            flush();
            $i += $bytesToRead;
        }
            PCMW_Logger::Debug('File stream complete ['.__LINE__.']',1);
    }

    /**
     * Start streaming video content
     */
    function start()
    {
        $this->open();
        $this->setHeader();
        $this->stream();
        $this->end();
    }
}
?>