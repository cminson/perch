/*
 * main.js
 * Here's where all the site javascript code lives.
 *
 * Author: Christophewr Minson
 * https://www.christopherminson.com
 */

const WEB_ROOT = "https://54.71.108.91/";
const IMAGE_BUSY = WEB_ROOT + "resources/utils/busy.gif";
const CONVERSIONS_DIR = "/CONVERSIONS/";

var ListImageURLS = [];
var ListImageStats = [];

var CurrentPosition = 0
var PreviousPosition = 0
var CurrentOp = "";
var MaxPosition =  0;
var HomePosition = 0;
var MAXIMAGES = 100;
var BaseDivId = "imagediv";
var BaseImageId = "image";
var BaseStatusId = "ID_IMAGE_STATS";
var OpImage;
var	SNDisplayed = 0;
var	BusyDisplayed = 0;
var HelpPageDisplayed = false;

var Ias = null;
var ImageAreaSelected = false;


// the dimensions of the currently displayed image
// these two variables are used to scale image picks
var CurrentImageWidth = 1;
var CurrentImageHeight = 1;



/************************************************************/
/* Local ANIM functions */
var SelecteFrame = 1;
function chooseFrameFile(frame)
{
    var e;

    console.log('chooseFrameFile');
    SelectedFrame = frame;

    e  = document.getElementById('ID_SUBMIT_FRAMEFILE');
    console.log(e);
	e.value=""; // CJM - MUST do this to avoid load caching!
    e.click();
}

function submitFrameFile()
{
    var frame,e;

    e  = document.getElementById('ID_FRAME_LOADFORM');
    e.submit();

    frame = "FRAME"+SelectedFrame;
    e  = document.getElementById(frame);
    e.src = IMAGE_BUSY;
}


function completeFrameLoad(imageList,text)
{
    console.log('completeFrameLoad');
    console.log(imageList);
    var image,frame,frameId,e;

	var imageArray = imageList.split(",");
	var imageCount = imageArray.length;
	
	for (i = 0; i < imageCount; i++)
	{

		image = imageArray[i];
		frameId = SelectedFrame + i;
		frame = "FRAME"+frameId;
		e  = document.getElementById(frame);
		if (e != null)
		{
			e.src = image;
		}

		frame = "FRAMEPATH"+frameId;
		e  = document.getElementById(frame);
		if (e != null)
		{
			e.value = image;
		}
	}

}

function reportFrameLoadError(error)
{
var e,frame;

	frame = "FRAME"+SelectedFrame;
	e  = document.getElementById(frame);
    e.src = WEB_ROOT+"/wimages/tools/ezimbanoop.png";

	frame = "FRAMEPATH"+SelectedFrame;
	e  = document.getElementById(frame);
    e.src = WEB_ROOT+"/wimages/tools/ezimbanoop.png";

	e = document.getElementById('ID_IMAGE_STATS');
	e.innerHTML = error;
}


function deleteFrameImage(frameId)
{
    var frame,e;

    frame = "FRAME"+frameId;
    e  = document.getElementById(frame);
    e.src = WEB_ROOT+"/wimages/tools/ezimbanoop.png";

    frame = "FRAMEPATH"+frameId;
    e  = document.getElementById(frame);
    e.value = WEB_ROOT+"/wimages/tools/ezimbanoop.png";
}


//
// *******************************************
//

function test() 
{
    console.log('test');
    window.location.href = "#home";
}

function chooseFile() 
{
    var e;

	e  = document.getElementById('ID_SUBMIT_FILE');
	if (e == null)
	{
		var eSet  = document.getElementsByName('FILENAME');
		e = eSet[0];
	}
    if (e == null)
    {
        alert("Internal load error #1");
		return;
    }
	e.value=""; // CJM - MUST do this to avoid load caching!

    // make sure we're at top of page
    window.location.href = "#home";
	e.click();
}

// this gets executed via the click function in choosefile
function submitFile() 
{
    console.log('submitFile');
    var e = document.getElementById('ID_LOAD_FORM');

    if (e == null)
    {
        alert("Internal load error #2");
		return;
	}
	e.submit();
	executeLoad();
}

function updateview(img, selection)
{
    if (!selection.width || !selection.height)
        return;

    var scaleX = 100 / selection.width;
    var scaleY = 100 / selection.height;

    $('#X1').val(selection.x1);
    $('#Y1').val(selection.y1);
    $('#X2').val(selection.x2);
    $('#Y2').val(selection.y2);
    $('#w').val(selection.width);
    $('#h').val(selection.height);

}

function viewOpList(id)
{
	hide('opslist1');
	hide('opslist2');
	hide('opslist3');
	hide('opslist4');

	show(id);
}

function hide(id)
{
    var e = document.getElementById(id);

    if (e != null)
        e.style.display = 'none';
    return;
}

function show(id)
{
    var e = document.getElementById(id);
    if (e != null)
        e.style.display = 'block';
    return;
}

function setElement(id,v)
{
	var e = document.getElementById(id);
    if (e != null)
        e.value = v;
}

//
// *****************************************************************
//




//
// optionally displays selection box for current image
// this happens when we are in Crop or Overlay ops
//
function displayImageSelection()
{
    var id;

    if (Ias != null)
    {
        Ias.cancelSelection();
        $('#opimage').imgAreaSelect({remove:true});
        Ias = null;
    }
    if ((CurrentOp.indexOf("crop") > -1) || (CurrentOp.indexOf("overlay") > -1))
    {
        Ias = $('#opimage').imgAreaSelect({ handles: true,
            fadeSpeed: 200, onSelectChange: updateview, instance: true });
        $('#opimagex').imgAreaSelect({ x1: 5, y1: 5, x2: 60, y2: 40 });
        setElement('X1',5);
        setElement('Y1',5);
        setElement('X2',60);
        setElement('Y2',40);
        setElement('W',55);
        setElement('H',35);
    }
    PreviousPosition = CurrentPosition;
    //Ias.setOptions({ show: true });
    //Ias.update();
}

function deleteImageAreaSelection()
{
    if (Ias != null)
    {
        Ias.cancelSelection();
        id = '#image'+CurrentPosition;
        $(id).imgAreaSelect({remove:true});
        Ias = null;
        CurrentOp = ""; // must do so we won't think we're still on an op page
    }
}

function openCurrentImage()
{
	OpImage = getCurrentImage();
	window.open(OpImage,"_blank");
}

function viewCurrentImage()
{
	OpImage = getCurrentImage();
	imageArray = OpImage.split("/");
	len = imageArray.length;
	var image = CONVERSIONS_DIR+imageArray[len-1];
	var e = document.getElementById('viewimage');
	var v = WEB_ROOT+"/ops/displayimage.html?CURRENTFILE="+image;
	e.href=v;
}

function returnToMainArea()
{
    var e;

    hide('ID_RETURN_TO_MAINPAGE');
	e = document.getElementById('ID_MAIN_SLIDER');
	if (e != null)
	{
			var scroll = 0;
			var s = "-"+scroll+"px";
			e.style.left = s;
	}
    deleteImageAreaSelection();
    return;
}

function selectArg(argValue) 
{
    var arg1 = document.getElementById('ARG1');
    arg1.value = argValue;
    console.log('selectArg', arg1);

    submitOpForm();
}


/*

function selectArg(selection) 
{
    var i = 0;
    console.log('SelectArg');

	while (true)
    {
        var id = 'ARG_BUTTON_ID' + i;
        var e = document.getElementById(id);
        var arg1 = document.getElementById('ARG1');
        if (e == null) break;

        var name = e.name;
        if (selection == name)
        {
            e.className = 'argHandler_selected';
        }
        else
        {
            e.className = 'argHandler';
        }

        arg1.value = selection;

        i += 1;
    }
}
*/




function displayOpForm()
{
var e;

	e = document.getElementById('ID_MAIN_SLIDER');
	if (e != null)
	{
			var scroll = 900;
			var s = "-"+scroll+"px";
			e.style.left = s;
	}
}

function displayCurrentImage()
{

	var imageURL = ListImageURLS[CurrentPosition];
	var stats = ListImageStats[CurrentPosition];

	// set the this displayed image as the one to share (should user hit share button)
	//imageURL = WEB_ROOT + imageURL;
    /*
    $("#share_container").jsSocials({
        url : imageURL,
    	shares: ["email", "twitter", "facebook", "reddit", "linkedin"],
    });
    */

	// now display image and stats
    var e_opimage = document.getElementById('ID_MAIN_IMAGE');
    var e_stats = document.getElementById('ID_IMAGE_STATS');

	stats = "[" + (CurrentPosition+1) + "/" + ListImageStats.length + "] " + stats;
    /*
	stats = "[" + (CurrentPosition+1) + "/" + ListImageStats.length + "]";

	var b = "&nbsp;&nbsp;<button id=\"share\" onclick=\"socialShare(event)\">share</button>";
	stats = stats + b;
    */

    console.log('displayCurrentImage: ' + imageURL);
	e_opimage.src = imageURL;
    e_opimage.onload = function() {

		setHiddenImage(e_opimage);
    };

	e_stats.innerHTML = stats;
	setDownloadImageLink(imageURL);
}


function hideBusyImage()
{
	BusyDisplayed = 0;
}

function displayBusyImage()
{
    var e = document.getElementById('ID_MAIN_IMAGE');
    

    e.src = IMAGE_BUSY;
    console.log('displayBusyImage');

	BusyDisplayed = 1;
}

function getCurrentImage()
{

	var imageURL = null;

	if (CurrentPosition < ListImageURLS.length)  {
		imageURL = ListImageURLS[CurrentPosition];
	}

	return imageURL;
}

function setDownloadImageLink(imageURL)
{
    console.log('setdownloadimage');
    var downloadLink = document.getElementById('ID_DOWNLOAD_IMAGE');
	if (downloadLink != null) {
        console.log(imageURL);
		downloadLink.href = imageURL;
	}
}

function setCurrentImage(imageURL)
{
    var e = document.getElementById('ID_MAIN_IMAGE');
    e.src = imageURL;
	setDownloadImageLink(imageURL);

}

function imageReady()
{
    displayImageSelection();
}

function setHiddenImage(image)
{

	var c = document.getElementById("ID_HIDDEN_IMAGE");
	var ctx = c.getContext("2d");
	ctx.drawImage(image,0,0);

	// now get the real width and height of image.  
	// can't use image for this, as it will be scaled.
	var tmpImage = document.createElement("img");
	tmpImage.src = image.src;
    tmpImage.onload = function() {

        CurrentImageWidth = tmpImage.width;
        CurrentImageHeight = tmpImage.height;
    };

}


//
// get the color at this event point.
// images are stored both in the displayable opimage area as 
// well as in a hidden canvas.  we sample the point at the canvas image,
// taking into account scaling of the images
//
function getImageColorAtCurrentPoint(event)
{

    var image = document.getElementById('ID_MAIN_IMAGE');
	var image_width = image.width;
	var image_height = image.height;
	var image_rect = image.getBoundingClientRect();

	var x = Math.floor(event.clientX - image_rect.x);
	var y = Math.floor(event.clientY - image_rect.y);

    scale_x = CurrentImageWidth / image.width;
    scale_y = CurrentImageHeight / image.height;
    x = Math.floor(x * scale_x);
    y = Math.floor(y * scale_y);

	var c=document.getElementById("ID_HIDDEN_IMAGE");
	var ctx=c.getContext("2d");
	var imgData = ctx.getImageData(x,y,1,1);
	red=imgData.data[0];
	green=imgData.data[1];
	blue=imgData.data[2];

	var hexRed = red.toString(16);
	if (hexRed.length < 2) hexRed = '00';
	var hexGreen = green.toString(16);
	if (hexGreen.length < 2) hexGreen = '00';
	var hexBlue = blue.toString(16);
	if (hexBlue.length < 2) hexBlue = '00';
	var hex ="#"+hexRed+hexGreen+hexBlue;

    e = document.getElementById('PICKCOLOR');
	if (e != null)
	{
		e.value = hex;
	}
	e=document.getElementById("COLOR1");
	if (e != null)
	{
		e.style.backgroundColor = hex;
	}

    e = document.getElementById('CLIENTX');
	if (e != null)
	{
		e.value = x;
	}
    e = document.getElementById('CLIENTY');
	if (e != null)
	{
		e.value = y;
	}
}


function setCurrentStatus(image,text)
{
	var id = BaseStatusId+CurrentPosition;
    var e = document.getElementById('opstatus');

	// special case: if this is result of batchoperation then
	// display a link to the batch viewer
	if ((text.indexOf("BATCH")) != -1)
	{
		OpImage = getCurrentImage();
		imageArray = OpImage.split("/");
		len = imageArray.length;
		image = CONVERSIONS_DIR+imageArray[len-1];
		var url=WEB_ROOT+"/ops/batchview.html?"+"CURRENTFILE="+image;
		var link="<a target=blank href="+url+">Batched Results</a>";
		text = text+"&nbsp;&nbsp;&nbsp;&nbsp;"+link;
	}
    e.innerHTML = text;
}



// 
// add image to the end of the array of possible images (max=MAXIMAGES).
// if reached end of the array, then loop back and overwrite images 
// beginning at the first (1) position.
//
function addImage(imageURL,text)
{
	hideBusyImage();

	ListImageURLS.push(imageURL);
	ListImageStats.push(text);
	CurrentPosition = ListImageURLS.length - 1;
	displayCurrentImage();

}

function nextImage()
{
    CurrentPosition++;
	if (CurrentPosition >= ListImageURLS.length) CurrentPosition = 0;
	displayCurrentImage();
}

function previousImage()
{

    CurrentPosition--;
	if (CurrentPosition < 0) CurrentPosition = ListImageURLS.length - 1;
	displayCurrentImage();
}

function homeImage()
{
	if (HomePosition >= ListImageURLS.length) HomePosition = 0;
	CurrentPosition = HomePosition;
	displayCurrentImage();
}

function setHomeImage(imageURL,position)
{
	show('ID_HOME_IMAGE');
    var e = document.getElementById('ID_HOME_IMAGE');
	HomePosition = position;

	if (imageURL == null)
	{
		imageURL = WEB_ROOT+"/wimages/tools/blank.jpg";
		HomePosition = 0;
	}
	e.src = imageURL;
}

function enableConvertButton()
{
	var e = document.getElementById('convert1');
	if (e != null)
	{
		e.disabled = false;
	}
}

function disableConvertButton()
{
	var e = document.getElementById('convert1');
	if (e != null)
	{
		e.disabled = true;
	}
}

function executeLoad()
{
    console.log("executeLoad");
	show('opimage');

    OpImage = getCurrentImage();
	displayBusyImage();
}

function completeWithNoAction()
{
	enableConvertButton();
	hideBusyImage();
	if (CurrentPosition < 1)
	{
		//hide('imagearea');
	}
}


function completeImageLoad(image,text)
{
    console.log("completeImageLoad: ", image, text);
	enableConvertButton();

	if (BusyDisplayed == 0)
	{
		return;
	}
	hideBusyImage();
	//show('imagearea');
	show('ID_MAIN_SLIDER');

	//var relImage = image.replace(WEB_ROOT,".");
	var relImage = image.replace(WEB_ROOT,"");

	//console.log("Adding Image: ", relImage);
	//addImage(relImage,text);
	addImage(image,text);

	setHomeImage(image,CurrentPosition);
}


function completeImageOp(image,text)
{
	enableConvertButton();


	//var relImage = image.replace(WEB_ROOT,".");
	var relImage = image.replace(WEB_ROOT,"");
	console.log("completeImageOp: ", image, relImage);

	// if this is true, indicates the op was cancelled via 
	// delete button prior to completion.
	if (BusyDisplayed == 0)
	{
		return;
	}
	addImage(relImage,text);
	hideBusyImage();
}

function xsubmitOpForm()
{
    var e = document.getElementById('ID_OP_SUBMITFORM');
    if (e == null)
    {
        alert("processing error - please try again");
        return;
    }
    console.log('xsubmitOpForm', e);
    executeOp();
    e.submit();

}

function submitOpForm()
{
    // CJM DEV - more validation here
    setTimeout(xsubmitOpForm, 500);
}

function executeOp()
{
var e;
var image;
var len;
var imageArray;

	// if already busy don't allow multiple converts
	if (BusyDisplayed == 1)
	{
		disableConvertButton();
	}
	else
	{
		enableConvertButton();
	}
	displayBusyImage();

	//show('imagearea');

	image = "";
    OpImage = getCurrentImage();
	if (OpImage != null)
	{
		imageArray = OpImage.split("/");
		len = imageArray.length;
		image = CONVERSIONS_DIR+imageArray[len-1];
        console.log('executeOp');
        console.log(image);
	}

	e = document.getElementById('current');
	e.value = image;
}

function reportOpError(error)
{
	hideBusyImage();
	e = document.getElementById('ID_IMAGE_STATS');
	e.innerHTML = error;
}


function reportLoadError(error)
{
var e;
    console.log("reportLoadError");

	hideBusyImage();
	show('ID_MAIN_SLIDER');
	if (CurrentPosition < 1)
	{
		//hide('imagearea');
	}
	e = document.getElementById('ID_IMAGE_STATS');
	e.innerHTML = error;
}

function getajaxRequest()
{
    var ajaxRequest;

    try{
        // Opera 8.0+, Firefox, Safari
        ajaxRequest = new XMLHttpRequest();
    } catch (e){
        // Internet Explorer Browsers
        try{
            ajaxRequest = new ActiveXObject("Msxml2.XMLHTTP");
        } catch (e) {
            try{
                ajaxRequest = new ActiveXObject("Microsoft.XMLHTTP");
            } catch (e){
                // Something went wrong
                return false;
            }
        }
    }
    return ajaxRequest;
}


function displayOp(op)
{
	var image;
	var imageArray;
	var len;
    var ajaxRequest = getajaxRequest();

    CurrentOp = op;
    ajaxRequest.onreadystatechange = function()
    {
        if(ajaxRequest.readyState == 4)
        {
            var response=ajaxRequest.responseText;
			var e;
            if (response.length > 10)
            {
			e  = document.getElementById('ID_OP_FORM');
			e.innerHTML = response;
			displayOpForm();
            show('ID_RETURN_TO_MAINPAGE');
            displayImageSelection();
            }
        }
	}

	var params="";
	OpImage = getCurrentImage();
	if (OpImage != null) 
	{
		imageArray = OpImage.split("/");
		len = imageArray.length;
		image = CONVERSIONS_DIR+imageArray[len-1];
		params="CURRENTFILE="+image;
	}
	ajaxRequest.open("POST",op,true);
	ajaxRequest.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	ajaxRequest.send(params);
}

function execSimpleOp(op,target)
{
	var image;
	var imageArray;
	var l;

    if (ListImageURLS.length == 0)
        return;

    var ajaxRequest = getajaxRequest();

    ajaxRequest.onreadystatechange = function()
    {
        if(ajaxRequest.readyState == 4)
        {
			var image;
			var text;
			var a;
           	var response = ajaxRequest.responseText;
		
			// indicating op was cancelled prior to completion
			if (BusyDisplayed == 0)
			{
				hideBusyImage();
				return;
			}
			a = response.split("?");
			image = a[0];
			text = a[1];
			
			addImage(image,text);
        }
	}

	var params="";
	OpImage = getCurrentImage();
	if (OpImage != null)	
	{
		displayBusyImage();
		imageArray = OpImage.split("/");
		len = imageArray.length;
		image = CONVERSIONS_DIR+imageArray[len-1];
        console.log(image);
		params="CURRENTFILE="+image+"&TGT="+target;
		ajaxRequest.open("POST",op,true);
		ajaxRequest.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
		ajaxRequest.send(params);
	}
}


function selectTableItem(id,path,file,status)
{
    var sel;

    sel = document.getElementById("IMAGE");
    sel.src = path+file;
    sel = document.getElementById(id);
    sel.value = file;
    sel = document.getElementById("STATUS");
    sel.innerHTML = status;
}


function logTrace(text)
{
	text = "JSTRACE: " + text;
	var log = WEB_ROOT+"/zs/jstrace.php";
	var params = "VALUE="+text;
    var ajaxRequest = getajaxRequest();
	ajaxRequest.open("POST",log,true);
	ajaxRequest.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	ajaxRequest.send(params);
}


function createSharedImage(imageURL)
{
	var target = WEB_ROOT+"/zs/jsshare.php";
	var params = "VALUE="+imageURL;
    var ajaxRequest = getajaxRequest();
	ajaxRequest.open("POST",target,true);
	ajaxRequest.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	ajaxRequest.send(params);
}


function socialShare(e)
{
    if ($("#ID_SHARE_CONTAINER").is(":visible")) {
        $("#ID_SHARE_CONTAINER").hide();
    }
    else {
        $("#ID_SHARE_CONTAINER").show();
    }
}

function toggleHelpPage()
{
    if (HelpPageDisplayed == true) {

        HelpPageDisplayed = false;
        hide('ID_HELP_AREA');
        show('ID_CONTENT_AREA');
    } 
    else {

        HelpPageDisplayed = true;
        show('ID_HELP_AREA');
        hide('ID_CONTENT_AREA');
    }

}



function test1(setting, name)
{
    console.log('test1');
    console.log(setting, name);
    pickerPopup302(setting,name);

}

    

