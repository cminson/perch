/*
 * main.js
 * Here's where all the site javascript code lives.
 *
 * Author: Christophewr Minson
 * https://www.christopherminson.com
 */
const BASE_URL = "http://54.71.108.91";   // also set in common.inc

const IMAGE_BUSY = BASE_URL + "/resources/utils/busy.gif";
const CONVERSIONS_PATH = "/CONVERSIONS/";

var ListImageURLS = [];  // list of all images in current session
var ListImageStats = []; // the parallel list of stats for the image list
var ListImageRegions = []; // the parallel list of regions for the image list
var CurrentPosition = 0; // position of the current image being worked on

var	BusyDisplayed = 0;
var HelpPageDisplayed = false;


// the dimensions of the currently displayed image
var CurrentImageWidth = 1;
var CurrentImageHeight = 1;


/************************************************************/
function chooseFrameFile(frame)
{
    var e;

    SelectedFrame = frame;

    e  = document.getElementById('ID_SUBMIT_FRAMEFILE');
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
    e.src = BASE_URL+"/wimages/tools/ezimbanoop.png";

	frame = "FRAMEPATH"+SelectedFrame;
	e  = document.getElementById(frame);
    e.src = BASE_URL+"/wimages/tools/ezimbanoop.png";

	e = document.getElementById('ID_IMAGE_STATS');
	e.innerHTML = error;
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
    document.getElementById('ID_LOAD_FORM').submit();
	displayBusyImage();
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
    document.getElementById(id).style.display = 'none';
}

function show(id)
{
    document.getElementById(id).style.display = 'block';
}

//
// *****************************************************************
//


function viewCurrentImage()
{
	var imagePath = getCurrentImagePath();
    if (imagePath != null) 
    {
	    document.getElementById('viewimage').href = BASE_URL+"/displayimage.html?CURRENTIMAGE="+imagePath;
        console.log(BASE_URL+"/displayimage.html?CURRENTIMAGE="+imagePath);
    }
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
    var arg1 = document.getElementById('ARG1');
    arg1.value = argValue;

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

	stats = "[" + (CurrentPosition+1) + "/" + ListImageStats.length + "] " + stats;

    // currently inactive.  not clear if needed
    /*
    document.getElementById('ID_MAIN_IMAGE').onload = function() {

		setHiddenImage(document.getElementById('ID_MAIN_IMAGE'));
    };
    */
	document.getElementById('ID_MAIN_IMAGE').src = imageURL;
	document.getElementById('ID_IMAGE_STATS').innerHTML = stats;
	setDownloadImageLink(imageURL);
    displayRegions(ListImageRegions[CurrentPosition]);

}

function hideBusyImage()
{
	BusyDisplayed = 0;
}

function displayBusyImage()
{
    document.getElementById('ID_MAIN_IMAGE').src = IMAGE_BUSY;

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

function getCurrentImagePath()
{
	var imageURL = getCurrentImageURL();
    if (imageURL == null) return null;

    var imageArray = imageURL.split("/");
	return CONVERSIONS_PATH+imageArray[imageArray.length - 1];
}

function setDownloadImageLink(imageURL)
{
    var downloadLink = document.getElementById('ID_DOWNLOAD_IMAGE');
	if (downloadLink != null) {
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
// images are stored both in the displayable ID_MAIN_IMAGE area as 
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
    var e = document.getElementById('opstatus');
    e.innerHTML = text;
}


// 
// add image to the end of the array of possible images 
//
function addImage(imageURL,text,regions)
{
	hideBusyImage();

	ListImageURLS.push(imageURL);
	ListImageStats.push(text);
	ListImageRegions.push(regions.split(','));
	CurrentPosition = ListImageURLS.length - 1;
	displayCurrentImage();

    if (ListImageURLS.length > 1) 
    {
        show('ID_PREVIOUS_IMAGE');
        show('ID_NEXT_IMAGE');
    }

}

function displayRegions(regionList)
{

    // determine how much image dimensions are altered in view (encoded in aspects)
    var viewedImage = document.getElementById('ID_MAIN_IMAGE');
    var aspectX = (viewedImage.clientWidth / CurrentImageWidth).toFixed(2);
    var aspectY = (viewedImage.clientHeight / CurrentImageHeight).toFixed(2);

    // ensure the overlay canvas size is exactly the same as the viewed image
    var canvas  = document.getElementById('ID_CANVAS');
    var ctx = canvas.getContext("2d");
    ctx.canvas.width  = viewedImage.clientWidth;
    ctx.canvas.height = viewedImage.clientHeight;
    ctx.clearRect(0, 0, CurrentImageWidth, CurrentImageWidth);

    // for all regions (except the background), draw the region bounding box
    for (i = 0; i < regionList.length; i++) {

        var region = regionList[i];
        console.log('Region: ', region);
        if (region.includes('background')) continue;

        // 
        // assumes images of form: name.score.type.box.suffix
        // therefore we want the 3rd part of this string (box)
        // if this form changes then this code must change!
        //
        var boundingBox = region.split('.')[3];
        var terms = boundingBox.split('_');
        var x = parseInt(terms[0]);
        var y = parseInt(terms[1]);
        var w = parseInt(terms[2]);
        var h = parseInt(terms[3]);
        console.log(x, y, w, h, aspectX, aspectY);

        ctx.beginPath();
        ctx.lineWidth = "2";
        ctx.strokeStyle = "green";

        var x = x * aspectX;
        var y = y * aspectY;
        var width = w * aspectX;
        var height = h * aspectY;

        console.log(x, y, width, height);
        ctx.rect(x, y, width, height);
        ctx.stroke();
    }
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
	CurrentPosition = 0;
	displayCurrentImage();
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
function completeImageLoad(imageURL,text,regions,width,height)
{
    CurrentImageWidth = width;
    CurrentImageHeight = height;
    console.log("completeImageLoad: ", imageURL, text);
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
    ListImageRegions = [];
    CurrentPosition = 0;
    NextPosition = 0;

	addImage(imageURL,text,regions);

    document.getElementById('ID_HOME_IMAGE').src = imageURL;

    hide('ID_PREVIOUS_IMAGE');
    hide('ID_NEXT_IMAGE');

    //document.getElementById('ID_OBJECT_VALUES').innerHTML = segmentInfo;
}

//
// Invoked once a conversion has been executed on an image.
// This function is invoked the PHP RecordAndComplete() in common.inc.
//
function completeImageOp(imageURL,text,regions)
{
    console.log('completeImageOp', imageURL, text, regions);
	enableConvertButton();


	// if this is true, indicates the op was cancelled via 
	// delete button prior to completion.
	if (BusyDisplayed == 0)
	{
		return;
	}

	addImage(imageURL, text, regions);
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
    var imagePath = getCurrentImagePath();
	document.getElementById('current').value = imagePath;

    // execute the fom POSTR
    document.getElementById('ID_OP_SUBMITFORM').submit();
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

    var imagePath = getCurrentImagePath();
	document.getElementById('current').value = imagePath;
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
	var imagePath = getCurrentImagePath();
	var homeImageURL = ListImageURLS[0];
    if (imagePath != null) 
    {
        console.log("POST: ", imagePath, op);
        $.post(op, 
            {
                CURRENTIMAGE: imagePath, 
                HOMEIMAGE: homeImageURL
            },
            function(data, status) 
            {
                if (data.length > 10)
                {
			        document.getElementById('ID_OP_FORM').innerHTML = data;
			        displayOpForm();
                    show('ID_RETURN_TO_MAINPAGE');
                }
            }
        
        );
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
	var log = BASE_URL+"/zs/jstrace.php";
	var params = "VALUE="+text;
    var ajaxRequest = getajaxRequest();
	ajaxRequest.open("POST",log,true);
	ajaxRequest.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	ajaxRequest.send(params);
}


function createSharedImage(imageURL)
{
	var target = BASE_URL+"/zs/jsshare.php";
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


    

