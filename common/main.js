/*
 * main.js
 * Here's where all the site javascript code lives.
 *
 * https://www.christopherminson.com
 *
 */

// 
// URL and directory roots
//
const BASE_URL = "http://54.71.108.91";   // root. also set in common.inc
const CONVERSIONS_PATH = "/CONVERSIONS/"; // where images are stored

// 
// Our current session state
//
var ListImageURLS = [];  // list of all images in current session
var ListImageStats = []; // the parallel list of stats for the image list
var ListImageRegions = []; // the parallel list of regions for the image list
var CurrentPosition = 0; // position of the current image being worked on
var CurrentRegions = ''; // the regions associated with current image
var CurrentOp = null; // the op we are currently viewing

var BusyIcon = null;    // set to PATH_BUSY_ICON when system is busy
const PATH_BUSY_ICON = './resources/utils/busy2.gif'; 

var HelpPageDisplayed = false;
var ViewROIS = true;

//
// the dimensions of the currently displayed image
//
var CurrentImageWidth = 1;
var CurrentImageHeight = 1;

//
// POST Endpoints
//
ENDPOINT_SEGMENT = './ops/segmentx.php';


/************************************************************/



//
// Executed by the Load Image button.
// Bring up the file chooser 
//
function chooseFile() 
{
	var inputForm  = document.getElementById('ID_SUBMIT_FILE');
	inputForm.value=""; //  MUST do this to avoid load caching!

    // make sure we're at top of page
    window.location.href = "#home";

    // click the button.  This will bring up the file chooser
    // which will then execute submitFile() once an image is selected
	inputForm.click();
}


//
// Executed via the click function in choosefile
// Submit the form and display Busy Indicator
//
function submitFile() 
{
    document.getElementById('ID_LOAD_FORM').submit();
	busyStateActivate();
}

//
// Submit the operation form.  This gets called when
// user clicks the Convert button
//
function submitOpForm()
{
	document.getElementById('ID_IMAGE_STATS').innerHTML = 'Loading Image ...';

    // Don't want to block.  
    // Therefore run submission in background, not in main thread
    setTimeout(backgroundSubmitOpForm, 500);
}


// 
// Scroll the main area out
// And the op form into the bottom of the screen
//
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


//
// Scroll the op form out
// And the main area into the bottom of the screen
//
function returnToMainArea()
{
    var e;

    CurrentOp = null;

    hide('ID_RETURN_TO_MAINPAGE');
	var scroll = 0;
	var s = "-"+scroll+"px";
    document.getElementById('ID_MAIN_SLIDER').style.left = s;
}

//
// Update the op form with HTML required for that op
//
function displayOp(op)
{
    console.log('displayOp');
    CurrentOp = op; 

	var imagePath = getCurrentImagePath();
	var homeImageURL = ListImageURLS[0];
	var regions = ListImageRegions[CurrentPosition];

    if (imagePath != null) 
    {
        console.log("POST: ", imagePath, op);
        $.post(op, 
            {
                CURRENTIMAGE: imagePath, 
                CURRENTREGIONS: regions, 
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


//
// Execute the operation submission
// This is called in the background via a timer from submitOpForm()
// 
function backgroundSubmitOpForm()
{

    // disable convertButton. display busy image 
	disableConvertButton();
	busyStateActivate();

    // set current variable to the current image
    // this is the image we will operate on
    // this gets sent to php during the submit
    var imagePath = getCurrentImagePath();
	document.getElementById('current').value = imagePath;

    // execute the POST
    document.getElementById('ID_OP_SUBMITFORM').submit();
}


//
// Invoked when an operation terminates with no change to image
//
function completeWithNoAction()
{
	enableConvertButton();
	busyStateDeactivate();
	if (CurrentPosition < 1)
	{
		//hide('imagearea');
	}
}

//
// Invoked once the image has been successfully loaded
// This function is invoked via javascript injection at ./ops/loadx.php
//
function completeImageLoad(imageURL, text, regions, width, height)
{
    CurrentImageWidth = width;
    CurrentImageHeight = height;
    console.log("completeImageLoad: ", imageURL, text, regions);
	enableConvertButton();

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

    // have the AI analyze the image for regions of interest (ROI)
    executeImageAnalysis();
}

//
// Invoked once the image has been ROI analyzed
// Save off the regions and then display them. 
// Terminate any busy indicators
//
function completeImageAnalysis(imagePath, regions)
{
    console.log('completeImageAnalysis', imagePath, regions);
    ListImageRegions[CurrentPosition] = regions; 
    displayRegions(ListImageRegions[CurrentPosition]);
    busyStateDeactivate();
	document.getElementById('ID_IMAGE_STATS').innerHTML = 'Image Ready';

    var regionList = regions.split(',');
    var regionNames = '';
    for (i = 0; i < regionList.length; i++) {
        var region = regionList[i];
        if (region.includes('background')) continue;

        var name = region.split('.')[2];
        regionNames += name;
        regionNames += '  ';
    }

	document.getElementById('ID_IMAGE_STATS').innerHTML = regionNames;
}


//
// Invoked once a conversion has been executed on an image.
// This function is invoked the PHP InformUI() in common.inc.
//
function completeImageOp(imageURL, text, regions)
{
    console.log('completeImageOp', imageURL, text, regions);
	enableConvertButton();

    //
    // if passed region is null, then we don't have any regions
    // if passed region is empty string, then use parent regions
    //
    /*
    if (regions == null) { 
        regions = ListImageRegions[CurrentPosition]; 
        console.log('SETTING PREVIOUS REGIONS: ', regions);
    }
    */
    regions = ListImageRegions[CurrentPosition]; 

	addImage(imageURL, text, regions);
	busyStateDeactivate();
}


//
// Ask the AI to perform a ROI analysis of the current image
// The AI will return a comman delimitted string of all the 
// detected ROI masks.
//
function executeImageAnalysis()
{
	var imagePath = getCurrentImagePath();

	document.getElementById('ID_IMAGE_STATS').innerHTML = 'AI Analyzing Image ...';

    var op = './ops/segmentx.php';
    $.post(ENDPOINT_SEGMENT, 
        {
            CURRENTIMAGE: imagePath 
        },
        function(data, status) 
        {
            completeImageAnalysis(imagePath, data);
        }
    );
}


//
// Render all the region rects associated with the current image
//
function displayRegions(regions)
{

    console.log('displayRegions: ', regions);
    var canvas  = document.getElementById('ID_CANVAS');
    var ctx = canvas.getContext("2d");

    // determine how much image dimensions are altered in view (encoded in aspects)
    var aspectX = (canvas.width / CurrentImageWidth).toFixed(2);
    var aspectY = (canvas.height / CurrentImageHeight).toFixed(2);
    /*
    console.log('CANVAS w h', canvas.width, canvas.height);
    console.log('CURRENTIMAGE w h', CurrentImageWidth, CurrentImageHeight);
    console.log('ASPECTS X Y', aspectX, aspectY);
    */

    
    // clear the view
    //ctx.clearRect(0, 0, canvas.width, canvas.height);

    if (regions == '') return;
    if (regions.length < 10) return;

    // for all regions (except the background), draw the region bounding box
	var regionList = regions.split(',');
    for (i = 0; i < regionList.length; i++) {

        var region = regionList[i];

        //console.log('Region: ', region);
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
        //console.log(x, y, w, h, aspectX, aspectY);


        var x = x * aspectX;
        var y = y * aspectY;
        // tasmania aspectx:  0.73 0.58
        var width = w * aspectX;
        var height = h * aspectY;


        ctx.lineWidth = "2";
        ctx.strokeStyle = "red";
        ctx.strokeRect(x, y, width, height);
        console.log('Drawing region: x y w h ', x, y, width, height);
    }
}



//
// Executed by the View Image button
// Set the link to the currently display image URL (if any)
//
function viewCurrentImage()
{
	var imagePath = getCurrentImagePath();
    if (imagePath != null) 
    {
	    document.getElementById('viewimage').href = BASE_URL+"/displayimage.html?CURRENTIMAGE="+imagePath;
        //console.log(BASE_URL+"/displayimage.html?CURRENTIMAGE="+imagePath);
    }
}


// 
// Add image to the end of the array of possible images 
//
function addImage(imageURL,text,regions)
{
    console.log('addImage');
	ListImageURLS.push(imageURL);
	ListImageStats.push(text);
	ListImageRegions.push(regions);
	CurrentPosition = ListImageURLS.length - 1;
    CurrentRegions = regions;
	displayCurrentImage();

    if (ListImageURLS.length > 1) 
    {
        show('ID_PREVIOUS_IMAGE');
        show('ID_NEXT_IMAGE');
    }

    // update op view, if any.
    //if (CurrentOp != null) displayOp(CurrentOp);

}


//
// Render the current image into the canvas
//
function displayCurrentImage()
{
    console.log('displayCurrentImage');
	var imageURL = ListImageURLS[CurrentPosition];
	var stats = ListImageStats[CurrentPosition];

	stats = "[" + (CurrentPosition+1) + "/" + ListImageStats.length + "] " + stats;

    var img = new Image();
    img.src = imageURL;

    img.onload = function(){

        var canvas  = document.getElementById('ID_CANVAS');
        var ctx = canvas.getContext("2d");

        var aspectRatioY = canvas.height / img.height;
        canvas.width = img.width * aspectRatioY;

        ctx.drawImage(img, 0, 0, img.width, img.height,
                   0, 0, canvas.width, canvas.height);
        //console.log(canvas.width, canvas.height);
        displayRegions(ListImageRegions[CurrentPosition]);
    }

	document.getElementById('ID_IMAGE_STATS').innerHTML = stats;
	setDownloadImageLink(imageURL);

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


function setCurrentStatus(image,text)
{
    var e = document.getElementById('opstatus');
    e.innerHTML = text;
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


function busyStateActivate()
{
    if (BusyIcon != null) return;

    var statArea = document.getElementById('ID_STAT_AREA');
    var canvas = document.getElementById('ID_CANVAS');
    var ctx = canvas.getContext("2d");

    var width = document.body.clientWidth;
    var height = ctx.canvas.height;

    y = 180;
    x = (width / 2) - 30;
    //console.log(x,y);

    BusyIcon = new Image();
    BusyIcon.src = PATH_BUSY_ICON;
    BusyIcon.style.position = "absolute";
    BusyIcon.style.left = x + "px"
    BusyIcon.style.top = y  + "px"
    BusyIcon.style.width = "60px";
    BusyIcon.style.height = "60px";
    BusyIcon.id = 0;

    document.body.appendChild(BusyIcon);
}


function busyStateDeactivate()
{
    if (BusyIcon != null)
    {
        document.body.removeChild(BusyIcon);
    }
    BusyIcon = null;
}


function toggleViewROIS()
{
    console.log('toggleViewROS');

    var viewDisplayRegions = document.getElementById('ID_VIEW_ROIS').checked;

    if (viewDisplayRegions == true) {
        show('ID_CANVAS');
        console.log('show');
    }
    else {
        hide('ID_CANVAS');
        console.log('hide');
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

function selectArg(argValue) 
{
    var arg1 = document.getElementById('ARG1');
    arg1.value = argValue;

    submitOpForm();
}


function hide(id)
{
    document.getElementById(id).style.display = 'none';
}


function show(id)
{
    document.getElementById(id).style.display = 'block';
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

function reportOpError(error)
{
	busyStateDeactivate();
	document.getElementById('ID_IMAGE_STATS').innerHTML = error;
}


function reportLoadError(error)
{
var e;

	busyStateDeactivate();
	show('ID_MAIN_SLIDER');
	if (CurrentPosition < 1)
	{
		//hide('imagearea');
	}
	e = document.getElementById('ID_IMAGE_STATS');
	e.innerHTML = error;
}

function regionTest()
{
var canvas  = document.getElementById('ID_CANVAS');
var ctx = canvas.getContext("2d");
ctx.lineWidth = "4";
ctx.strokeStyle = "blue";
ctx.strokeRect(0, 0, 100, 100);
}


    

