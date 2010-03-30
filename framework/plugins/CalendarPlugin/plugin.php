<?php
// The calendar Plugin :)
class CalendarPlugin extends BasePlugin {

    public function getCalendar() {
        date_default_timezone_set("Europe/Madrid");
        header("Expires: Mon, 26 Jul 1997 05:00:00 GMT" );
        header("Last-Modified: " . gmdate( "D, d M Y H:i:s" ) . "GMT" );
        header("Cache-Control: no-cache, must-revalidate" );
        header("Pragma: no-cache" );
        header("Content-Type: text/xml; charset=utf-8");
        $xml = '<?xml version="1.0" ?><response><content><![CDATA[';
        if(Request::getGetParam("event") != '') {
            //TODO: Write in a future some element to get events from the database

        }
        else {

            $month = 0;
            $year = 0;
            $event = 0;

            $params = Request::getParameters();

            if (isset($params["month"])) {
                $month = $params["month"]["value"];
            }

            if (isset($params["year"])) {
                $year =  $params["year"]["value"];
            }

            if (isset($params["event"])) {
                $event = $params["event"]["value"];
            }

            if ($month=="1") {
                $time = time();
                $month = date("n",$time);
            }

            if($year == "1") {
                $time = time();
                $year = date('Y',$time);
            }

            $month = intval($month);
            $year  = intval($year);

            $date = getdate(mktime(0,0,0,$month,1,$year));
            $today = getdate();
            $hours = $today['hours'];
            $mins = $today['minutes'];
            $secs = $today['seconds'];

            if(strlen($hours)<2) {
                $hours="0".$hours;
            }

            if(strlen($mins)<2) {
                $mins="0".$mins;
            }
            if(strlen($secs)<2) {
                $secs="0".$secs;
            }

            $days=date("t",mktime(0,0,0,$month,1,$year));
            $start = $date['wday'];
            $name = $date['month'];



            $year2 = $date['year'];
            $offset = $days + $start - 1;

            if($month==12) {
                $next=1;
                $nexty=$year + 1;
            }
            else {
                $next=$month + 1;
                $nexty=$year;
            }

            if($month==1) {
                $prev=12;
                $prevy=$year - 1;
            }
            else {
                $prev=$month - 1;
                $prevy=$year;
            }

            if($offset <= 28) {
                $weeks=28;
            }
            elseif($offset > 35) {
                $weeks = 42;
            }
            else {
                $weeks = 35;
            }

            switch ($name) {

                case "January":
                    $aname=_("Enero");
                    break;

                case "February":
                    $aname=_("Febrero");
                    break;

                case "March":
                    $aname=_("Marzo");
                    break;

                case "April":
                    $aname=_("Abril");
                    break;

                case "May":
                    $aname=_("Mayo");
                    break;

                case "June":
                    $aname=_("Junio");
                    break;

                case "July":
                    $aname=_("Julio");
                    break;

                case "August":
                    $aname=_("Agosto");
                    break;

                case "September":
                    $aname=_("Septiembre");
                    break;

                case "October":
                    $aname=_("Octubre");
                    break;

                case "November":
                    $aname=_("Noviembre");
                    break;

                case "December":
                    $aname=_("Diciembre");
                    break;
            };


            $xml .= "<table class='cal' cellpadding='0' cellspacing='1'>
                    <tr>
                        <td colspan='7' class='calhead'>
                            <table>
                            <tr>
                                <td>
                                    <a href='javascript:navigate($prev,$prevy,\"\")' style='border:none'><img src='".BASE_URL."style/default/images/calLeft.gif' alt='prev' /></a> <a href='javascript:navigate(\"\",\"\",\"\")' style='border:none'><img src='".BASE_URL."style/default/images/calCenter.gif' alt='current' /></a> <a href='javascript:navigate($next,$nexty,\"\")' style='border:none'><img src='".BASE_URL."style/default/images/calRight.gif' alt='next' /></a> <a href='javascript:void(0)' onClick='showJump(this)' style='border:none'><img src='".BASE_URL."style/default/images/calDown.gif' alt='jump' /></a>
                                </td>
                                <td align='right' class='monthHead'>
                                    $aname $year2
                                </td>
                            </tr>
                            </table>
                        </td>
                    </tr>
                    <tr class='dayhead'>
                        <td>"._("L")."</td>
                        <td>"._("M")."</td>
                        <td>"._("X")."</td>
                        <td>"._("J")."</td>
                        <td>"._("V")."</td>
                        <td>"._("S")."</td>
                        <td>"._("D")."</td>
                    </tr>";

            $col=1;
            $cur=1;
            $next=0;

            for($i=1;$i<=$weeks;$i++) {

                if($next==3) {
                    $next=0;
                }
                if($col==1) {
                    $xml.="\n<tr class='dayrow'>";
                }

                $xml.="\t<td valign='top' onMouseOver=\"this.className='dayover'\" onMouseOut=\"this.className='dayout'\">";

                if($i <= ($days+($start-1)) && $i >= $start) {


                    if( ($cur==$today["mday"]) && ($name==$today["month"]) && ($year2==$today["year"])) {
                        $xml.="<div class='day'><b style='color:#C00'>$cur</b></div>";
                    }
                    else {
                        $xml.="<div class='day'><b";
                        $xml.=">$cur</b></div>";

                    }


                        //$xml.="<div class='calevent'><a href='javascript:navigate(\"\",\"\",\"".$row[0]."\")'>Event</a></div>";


                    $xml.="\n\t</td>\n";

                    $cur++;
                    $col++;

                } else {
                    $xml.="&nbsp;\n\t</td>\n";
                    $col++;
                }

                if($col==8) {
                    $xml.="\n</tr>\n";
                    $col=1;
                }
            }

            $xml.="</table>";

        }

        $xml .= "]]></content></response>";
        print $xml;
    }

    public function displayCalendar() {

        $html = "<div id=\"calback\">
                    <div id=\"calendar\">
                    </div>
                 </div>";
        return $html;
    }

    public function jsLoad() {
        return "<script type=\"text/javascript\">navigate('','','');</script>";
    }

    public function getInfo() {
        print "Foo";
    }
}

?>