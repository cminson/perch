/*
 * main.js
 * Here's where all the site javascript code lives.
 *
 * Author: Christophewr Minson
 * https://www.christopherminson.com
 */
const BASE_PATH = "http://54.71.108.91";   // also set in common.inc

const IMAGE_BUSY = BASE_PATH + "/resources/utils/busy.gif";
const CONVERSIONS_DIR = "/CONVERSIONS/";

var ListImageURLS = [];
var ListImageStats = [];

var CurrentPosition = 0;
var PreviousPosition = 0;
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
    e.src = BASE_PATH+"/wimages/tools/ezimbanoop.png";

	frame = "FRAMEPATH"+SelectedFrame;
	e  = document.getElementById(frame);
    e.src = BASE_PATH+"/wimages/tools/ezimbanoop.png";

	e = document.getElementById('ID_IMAGE_STATS');
	e.innerHTML = error;
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

    console.log('chooseFile');
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
    console.log('e click');
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




function viewCurrentImage()
{
	var imageDir = getCurrentImageDir();

	document.getElementById('viewimage').href = BASE_PATH+"/displayimage.html?CURRENTFILE="+imageDir;
}

function returnToMainArea()
{
    var e;

    hide('ID_RETURN_TO_MAINPAGE');
	var scroll = 0;
	var s = "-"+scroll+"px";
    document.getElementById('ID_MAIN_SLIDER').style.left = s;
}

function selectArg(argValue) 
{
    console.log('selectArg');
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
	//imageURL = BASE_PATH + imageURL;
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
    document.getElementById('ID_MAIN_IMAGE').src = IMAGE_BUSY;
    console.log('displayBusyImage');

	BusyDisplayed = 1;
}

function getCurrentImageURL()
{
	var imageURL = null;

	if (CurrentPosition < ListImageURLS.length)  {
		imageURL = ListImageURLS[CurrentPosition];
	}
	return imageURL;
}

function getCurrentImageDir()
{
	var imageURL = getCurrentImageURL();
    if (imageURL == null) return null;

    var imageArray = imageURL.split("/");
	return CONVERSIONS_DIR+imageArray[imageArray.length - 1];
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
    document.getElementById('ID_MAIN_IMAGE').src = imageURL;
	setDownloadImageLink(imageURL);
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
		imageURL = BASE_PATH+"/wimages/tools/blank.jpg";
		HomePosition = 0;
	}
	e.src = imageURL;
}

function enableConvertButton()
{
    var e = document.getElementById('convert1');
    if (e != null) e.disabled = false;
}

function disableConvertButton()
{
    var e = document.getElementById('convert1');
    if (e != null) e.disabled = true;
}

function executeLoad()
{
    console.log("executeLoad");
	show('opimage');

    OpImage = getCurrentImageURL();
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

//
// Invoked once the image has been successfully loaded
// This function is invoked via javascript injection at ./ops/loadx.php
//
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

    ListImageURLS = [];
    ListImageStats = [];
    CurrentPosition = 0;
    PreviousPosition = 0;
    NextPosition = 0;

	//var relImage = image.replace(BASE_PATH,".");
	var relImage = image.replace(BASE_PATH,"");
    console.log(relImage, image);

	//console.log("Adding Image: ", relImage);
	//addImage(relImage,text);
	addImage(image,text);

	setHomeImage(image,CurrentPosition);

    //document.getElementById('ID_OBJECT_VALUES').innerHTML = segmentInfo;
}

//
// Invoked once a conversion has been executed on an image.
// This function is invoked the PHP RecordAndComplete() in common.inc.
//
function completeImageOp(image,text)
{
	enableConvertButton();

	//var relImage = image.replace(BASE_PATH,".");
	var relImage = image.replace(BASE_PATH,"");
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

//
// Execute the operation submission
// This is called via a timer from submitOpForm()
// 
function backgroundSubmitOpForm()
{
    console.log('backgroundSubmitOpForm');

    // disable convertButton. display busy image 
	disableConvertButton();
	displayBusyImage();

    // set current variable to the current image
    // this is the image we will operate on
    // this gets sent to php during the submit
    var imageDir = getCurrentImageDir();
	document.getElementById('current').value = imageDir;

    // execute the fom POSTR
    document.getElementById('ID_OP_SUBMITFORM').submit();
}

function test()
{
    console.log('test');
}

//
// Submit the operation form.  This gets called when
// user clicks the Convert button
//
function submitOpForm()
{
    console.log('submitOpForm');

    // Don't want to block.  
    // Therefore run submission in background, not in main thread
    setTimeout(backgroundSubmitOpForm, 500);
}

function executeOp()
{

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

    var imageDir = getCurrentImageDir();
	document.getElementById('current').value = imageDir;
}

function reportOpError(error)
{
	hideBusyImage();
	document.getElementById('ID_IMAGE_STATS').innerHTML = error;
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
			document.getElementById('ID_OP_FORM').innerHTML = response;
			displayOpForm();
            show('ID_RETURN_TO_MAINPAGE');
            }
        }
	}

	var imageDir = getCurrentImageDir();
    if (imageDir != null) 
    {
	    var	params="CURRENTFILE="+imageDir;
	    ajaxRequest.open("POST",op,true);
	    ajaxRequest.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	    ajaxRequest.send(params);
    }
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

	var imageDir = getCurrentImageDir();
	if (imageDir != null)	
	{
		displayBusyImage();
        console.log(imageDir);
		var params="CURRENTFILE="+imageDir+"&TGT="+target;
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
	var log = BASE_PATH+"/zs/jstrace.php";
	var params = "VALUE="+text;
    var ajaxRequest = getajaxRequest();
	ajaxRequest.open("POST",log,true);
	ajaxRequest.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	ajaxRequest.send(params);
}


function createSharedImage(imageURL)
{
	var target = BASE_PATH+"/zs/jsshare.php";
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

    

