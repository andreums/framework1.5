<?php
try {
    chdir("../");
    include "framework/bootstrap.php";
    $config = Config::getInstance();
    $baseURL = $config->getbaseurl();
    if (!defined("BASE_URL")) {
        define("BASE_URL",$baseURL);
    }
    $filter = BaseFilter::getInstance();
}
catch (Exception $ex) {
    var_dump($ex);
}
?>
var req;

function navigate(month,year,evt) {
    setFade(0);
	if (month == "") {
		month = "1";
	}
	if (year== "") {
		year = "1";
	}
	if (evt== "") {
		evt = "1";
	}
    var url = "<?php print BASE_URL ?>index.php/calendarPlugin/getCalendar/month/"+month+"/year/"+year+"/";
    if(window.XMLHttpRequest) {
        req = new XMLHttpRequest();
    } else if(window.ActiveXObject) {
        req = new ActiveXObject("Microsoft.XMLHTTP");
    }
    req.open("GET", url, true);
    req.onreadystatechange = callback;
    req.send(null);
}

function callback() {
    if(req.readyState == 4) {
        var response = req.responseXML;
        var resp = response.getElementsByTagName("response");
        getObject("calendar").innerHTML = resp[0].getElementsByTagName("content")[0].childNodes[0].nodeValue;
        fade(70);
    }
}

function getObject(obj) {
    var o;
    if(document.getElementById) o = document.getElementById(obj);
    else if(document.all) o = document.all.obj;
    return o;
}

function fade(amt) {
    if(amt <= 100) {
        setFade(amt);
        amt += 10;
        setTimeout("fade("+amt+")", 5);
    }
}

function setFade(amt) {
    var obj = getObject("calendar");
    amt = (amt == 100)?99.999:amt;
    obj.style.filter = "alpha(opacity:"+amt+")";
    obj.style.KHTMLOpacity = amt/100;
    obj.style.MozOpacity = amt/100;
    obj.style.opacity = amt/100;
}

function showJump(obj) {
    var curleft = curtop = 0;
    if (obj.offsetParent) {
        curleft = obj.offsetLeft
        curtop = obj.offsetTop
        while (obj = obj.offsetParent) {
            curleft += obj.offsetLeft
            curtop += obj.offsetTop
        }
    }
    var jump = document.createElement("div");
    jump.setAttribute("id","jump");
    jump.style.position = "absolute";
    jump.style.top = curtop+15+"px";
    jump.style.left = curleft+"px";
    var output = '<select id="month">\n';
    var months = new Array('Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre');
    var n;
    for(var i=0;i<12;i++) {
        n = ((i+1)<10)? '0'+(i+1):i+1;
        output += '<option value="'+n+'">'+months[i]+'  </option>\n';
    }
    output += '</select> \n<select id="year">\n';
    for(var i=0;i<=15;i++) {
        n = (i<10)? '0'+i:i;
        output += '<option value="20'+n+'">20'+n+'  </option>\n';
    }
    output += '</select> <a href="javascript:jumpTo()"><img src="style/default/images/calGo.gif" alt="go" /></a> <a href="javascript:hideJump()"><img src="style/default/images/calStop.gif" alt="close" /></a>';
    jump.innerHTML = output;
    document.body.appendChild(jump);
}

function hideJump() {
    document.body.removeChild(getObject("jump"));
}

function jumpTo() {
    var m = getObject("month");
    var y = getObject("year");
    navigate(m.options[m.selectedIndex].value,y.options[y.selectedIndex].value,'');
    hideJump();
}