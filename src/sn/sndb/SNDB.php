<?php
// Index file format: array of 32-bit offsets (little endian) into $data_file, marking 
// the beginning of the episode data. e.g. ep 101 data starts at:
// data_file[index_file[101-1]]

// Data file format: Ep# \n Title \n Description \n HQ \n LQ \n HTML \n PDF \n TXT \n
// Date \n Runtime \n
define("INDEX_FILE", "idx.idx");
define("DATA_FILE", "db.db");

class SNDB
{
    private $epn;
    private $title;
    private $desc;
    private $hq;
    private $lq;
    private $html;
    private $pdf;
    private $txt;
    private $date;
    private $runtime;

    function __construct($number)
    {
        $if = fopen(INDEX_FILE, "rb");
        $if_offset = ($number - 1) * 4;
        fseek($if, $if_offset);
        $eb = fread($if, 4);
        $df_offset = ord($eb[0]) + (ord($eb[1]) << 8) + (ord($eb[2]) << 16) + (ord($eb[3]) << 24);
        $df = fopen(DATA_FILE, "rb");
        fseek($df, $df_offset);
        $this->epn = trim(fgets($df));
        $this->title = trim(fgets($df));
        $this->desc = trim(fgets($df));
        $this->hq = trim(fgets($df));
        $this->lq = trim(fgets($df));
        $this->html = trim(fgets($df));
        $this->pdf = trim(fgets($df));
        $this->txt = trim(fgets($df));
        $this->date = trim(fgets($df));
        $this->runtime = trim(fgets($df));
        fclose($if);
    }

    public function getEpNumber()
    {
        return $this->epn;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function getDescription()
    {
        return $this->desc;
    }

    public function getHQLink()
    {
        return $this->hq;
    }

    public function getLQLink()
    {
        return $this->lq;
    }

    public function getHTMLLink()
    {
        return $this->html;
    }

    public function getPDFLink()
    {
        return $this->pdf;
    }

    public function getPlainLink()
    {
        return $this->txt;
    }

    public function getDate()
    {
        return $this->date;
    }

    public function getRunTime()
    {
        return $this->runtime;
    }

    public static function createDatabase($index_file, $data_file)
    {

        // We can get all of the information from this page:
        $page = file_get_contents("https://www.grc.com/securitynow.htm");


        $df = fopen($data_file, "wb");
        $if = fopen($index_file, "wb");

        $ep = 1;
        while(strpos($page, "Episode&nbsp;#$ep") !== false)
        {
            $eptbl = substr($page, strpos($page, "<a name=\"$ep\"></a>"));
            $eptbl = substr($eptbl, 0, strpos($eptbl, '</table></td></tr></table></td></tr></table>'));

            $ep_pad = str_pad($ep, 3, "0", STR_PAD_LEFT);
            $hq = "http://media.grc.com/sn/sn-" . $ep_pad . ".mp3";
            $lq = "http://media.grc.com/sn/sn-" . $ep_pad . "-lq.mp3";
            $htm = "http://www.grc.com/sn/sn-" . $ep_pad . ".htm";
            $txt = "http://www.grc.com/sn/sn-" . $ep_pad . ".txt";
            $pdf = "http://www.grc.com/sn/sn-" . $ep_pad . ".pdf";

            $split = explode("|", $eptbl);

            $date = trim($split[1]);
            $runtime = substr($split[2], 1, strpos($split[2], "<") - 1);

            $title = substr($eptbl, strpos($eptbl, "<b>") + 3);
            $title = substr($title, 0, strpos($title, "</b>"));

            $desc_sep = '<img src="/image/transpixel.gif" width=1 height=4 border=0><br>';
            $desc = substr($eptbl, strpos($eptbl, $desc_sep) + strlen($desc_sep));
            $desc = substr($desc, 0, strpos($desc, "</font>"));

            $offset = ftell($df);
            fwrite($if, chr($offset & 0xFF) . chr(($offset >> 8) & 0xFF) . chr(($offset >> 16) & 0xFF) . chr(($offset >> 24) & 0xFF));

            // was: html_entitiy_decode
            $data = "$ep\n$title\n$desc\n$hq\n$lq\n$htm\n$pdf\n$txt\n$date\n$runtime\n";
            // Source: http://www.php.net/manual/en/function.html-entity-decode.php#104617
            //$data = preg_replace_callback("/(&#[0-9]+;)/", function($m) { return mb_convert_encoding($m[1], "UTF-8", "HTML-ENTITIES"); }, $data); 
            fwrite($df, $data);

            $ep++;
        }

        fclose($df);
        fclose($if);
    }
}
?>
