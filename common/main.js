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
var SelectedRegion = 'ALL'; // the regions currently selected
var CurrentSecondaryImage = ''; // the secondary image
var CurrentSecondaryRegions = ''; // the regions associated with secondary image
var SelectedSecondaryRegion = 'ALL'; // the secondary regions currently selected
var CurrentOpForm = null; // the operation form we are currently viewing, if anuy
var SelectedOp = null; // currently selected operation, if any
var SelectedSetting = null; // currently selected generic setting, if any

var BusyIcon = null;    // set to PATH_BUSY_ICON when system is busy
const PATH_BUSY_ICON = './resources/utils/busy2.gif'; 

const PATH_BANNER_ICON = './resources/banners/banner01.jpg';

var HelpPageDisplayed = false;
var ViewROIS = true;

//
// the dimensions of the currently displayed image
//
var DisplayedImage = null; // DEV
var CurrentImageWidth = 1;
var CurrentImageHeight = 1;

//
// POST Endpoints
//
ENDPOINT_SEGMENT = './ops/segmentx.php';

//
// Which regions to use after for an image just generated via an operation
// cross-defined in common.inc
//
const REGIONS_PREVIOUS = 'PREVIOUS';
const REGIONS_NONE = 'NONE';

//
// Constants and variables used by the Busy Indicstor
//
const BUSY_RADIAN_INCREMENT = 0.1;
const BUSY_RADIAN_START = 1.5;
const BUSY_COLOR_LOADIMAGE = '#ff0000';
const BUSY_COLOR_ANALYZEIMAGE = '#00ff00';
const BUSY_SYSTEM_FPS = 60;   // hardset:  this is a built-in js system value
const BUSY_FPS = 15;        // our desired fps
const BUSY_STROKE_WIDTH = 9;
var BusyRadius = 60;
var BusyStartRadian = 0;
var BusyEndRadian = 0;
var BusyColor = BUSY_COLOR_LOADIMAGE;
var BusyStateActive = false;


//
// Region constants
//
const REGIONS_PEOPLE = ['person'];
const REGIONS_LIVING = ['dog', 'cat', 'bird', 'potted plant', 'elephant', 'horse', 'sheep', 'cow', 'bear', 'zebra', 'giraffe'];
const REGION_PEOPLE_COLOR = '#ff0000';
const REGION_LIVING_COLOR = '#00ff00';
const REGION_WORLD_COLOR = '#0000ff';

//const COLOR_BACKGROUND = '#013220';
const COLOR_BACKGROUND = '#d3d3d3';
const BANNER_ANNOUNCE = 'Dream Perch';
const BANNER_HELP1 = 'load an image  - click the upload arrow below';

/************************************************************/


function init()
{
    hide('ID_CONTROLS_SPAN');
    /*
    hide('ID_HOME_IMAGE');
    hide('ID_VIEW_ROIS');
    hide('ID_ROIS_TEXT');
    */
    drawInitialCanvas();
}


function drawInitialCanvas()
{

    var canvas  = document.getElementById('ID_CANVAS');
    var ctx = canvas.getContext("2d");

    ctx.fillStyle = COLOR_BACKGROUND;
    ctx.fillRect(0, 0, canvas.width, canvas.height);

    var x = Math.floor(canvas.width / 2) - 130;
    var y = Math.floor(canvas.height / 2);
    ctx.font = 'bold 40px Tahoma';
    ctx.fillStyle = "#000000";
    ctx.fillText(BANNER_ANNOUNCE, x, y);

    x = Math.floor(canvas.width / 2) - 150;
    y = canvas.height - 20;
    ctx.font = 'normal 15px Tahoma';
    ctx.fillStyle = "#000000";
    //ctx.fillText(BANNER_HELP1, x, y);
}


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
    console.log('submitFile');
    document.getElementById('ID_LOAD_FORM').submit();
	busyStateActivate();
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

    CurrentOpForm = null;

    hide('ID_RETURN_TO_MAINPAGE');
	var scroll = 0;
	var s = "-"+scroll+"px";
    document.getElementById('ID_MAIN_SLIDER').style.left = s;
}


function executeConversion()
{
	document.getElementById('ID_IMAGE_STATS').innerHTML = 'Processing Image ...';

    // Don't want to block.  
    // Therefore run submission in background, not in main thread
    setTimeout(executeConversionInBackground, 500);
}


//
// Execute the operation submission
// This is called in the background via a timer from submitOpForm()
// 
function executeConversionInBackground()
{

    // disable convertButton. display busy image 
	disableConvertButton();
	busyStateActivate();

    // set current variable to the current image
    // this is the image we will operate on
    // this gets sent to php during the submit
    var currentImage = getCurrentImagePath();
	document.getElementById('ID_CURRENT_IMAGE').value = currentImage;

    // execute the POST
    console.log('POSTING FORM: ', currentImage);
    document.getElementById('ID_OP_SUBMITFORM').submit();
}



//
// Update the op form with HTML required for that op
//
function displayOp(op)
{
    console.log('displayOp');
    console.log('REGIONS: ', CurrentSecondaryRegions, SelectedSecondaryRegion);
    CurrentOpForm = op; 

	var currentImage = getCurrentImagePath();
	var homeImageURL = ListImageURLS[0];
	var currentRegions = ListImageRegions[CurrentPosition];

    if (currentImage != null) 
    {
        console.log("POST: ", currentImage, op);
        console.log("POST REGIONS: ", SelectedSetting, SelectedRegion, SelectedSecondaryRegion);
        $.post(op, 
            {
                CURRENT_IMAGE: currentImage,
                CURRENT_REGIONS: currentRegions,
                SELECTED_REGION: SelectedRegion,
                SELECTED_OP: SelectedOp,
                SELECTED_SETTING: SelectedSetting,
                CURRENT_SECONDARY_IMAGE: CurrentSecondaryImage,
                CURRENT_SECONDARY_REGIONS: CurrentSecondaryRegions,
                SELECTED_SECONDARY_REGION: SelectedSecondaryRegion,
                HOME_IMAGE: homeImageURL
            },
            function(data, status) 
            {
                console.log(data.length, status);
                if (data.length > 10)
                {
			        document.getElementById('ID_OP_FORM').innerHTML = data;
			        displayOpForm()
                    show('ID_RETURN_TO_MAINPAGE');
                }
            }
        );
    }
}


//
// Invoked when an operation terminates with no change to image
//
function completeWithNoAction()
{
	var stats = ListImageStats[CurrentPosition];

	stats = "[" + (CurrentPosition+1) + "/" + ListImageStats.length + "] " + stats;

    console.log('completeWithNoAction');
	enableConvertButton();
	busyStateDeactivate();
	document.getElementById('ID_IMAGE_STATS').innerHTML = '(No Operation Executed) '+stats;
	if (CurrentPosition < 1)
	{
		//hide('imagearea');
	}
}

//
// Invoked once the image has been successfully loaded
// This function is invoked via javascript injection at ./ops/loadx.php
//
function completeImageLoad(imageURL, text, width, height)
{
    console.log('completeImageLoad');
    busyStateDeactivate();
    show('ID_HOME_IMAGE');

    CurrentImageWidth = width;
    CurrentImageHeight = height;
    console.log("completeImageLoad: ", imageURL, text);
	enableConvertButton();

	show('ID_MAIN_SLIDER');

    ListImageURLS = [imageURL];
    ListImageStats = [text];
    ListImageRegions = [null];
    CurrentRegions = null;

	CurrentPosition = ListImageURLS.length - 1;
	displayCurrentImage();

    if (ListImageURLS.length > 1) 
    {
        show('ID_PREVIOUS_IMAGE');
        show('ID_NEXT_IMAGE');
    }

    console.log("setting HOME", imageURL);
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

    show('ID_CONTROLS_SPAN');
    CurrentRegions = regions;
    ListImageRegions[CurrentPosition] = regions; 
    displayRegions();
    busyStateDeactivate();

    var regionList = regions.split(',');
    var regionAttributes = '';
    var regionCount = 0;
    for (i = 0; i < regionList.length; i++) {
        var region = regionList[i];
        if (region.includes('background')) continue;

        /*
        var name = region.split('.')[2];
        var regionDimensions = region.split('.')[3].split('_');
        var regionWidth = regionDimensions[2];
        var regionHeight = regionDimensions[3];

        var attribute = name + '_' + regionWidth + 'x' + regionHeight;
        regionAttributes += attribute;
        regionAttributes += ' ';
        */
        regionCount += 1;
    }

    if (regionCount == 0)
        regionAttributes = 'Image Loaded and Analyzed: No Regions Detected';
    else if (regionCount == 1)
        regionAttributes = 'Image Loaded and Analyzed: 1 Region Detected';
    else
        regionAttributes = 'Image Loaded and Analyzed: '+regionCount+' Regions Detected';

    console.log('Setting STAT: ', regionAttributes);
	document.getElementById('ID_IMAGE_STATS').innerHTML = regionAttributes;
    ListImageStats[CurrentPosition] = regionAttributes;
    if (CurrentOpForm != null) displayOp(CurrentOpForm);
}


//
// Invoked once a conversion has been executed on an image.
// This function is executed by InformUI() in common.inc.
//
// Re-enable convert button
// Determine what regions are associated with this converted image
//      1) if region == EGthen we use the previous image's regions (default)
//      2) if region == '0', then we will have no regions for this image
//      3) otherwise, use the string in regions as our new region set
// Store off all conversion data (imageURL, status text, regions)
// Disable busy state
//
function completeImageOp(imageURL, text, regions)
{
    console.log('completeImageOp', imageURL, text, regions);
	enableConvertButton();

    if (regions == REGIONS_PREVIOUS) regions = ListImageRegions[CurrentPosition]; 
    else if (regions == REGIONS_NONE) regions = '';

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

    busyStateActivate();
	document.getElementById('ID_IMAGE_STATS').innerHTML = 'Analyzing Image ...';

    var op = './ops/segmentx.php';
    $.post(ENDPOINT_SEGMENT, 
        {
            CURRENT_IMAGE: imagePath 
        },
        function(regions, status) 
        {
            completeImageAnalysis(imagePath, regions);
        }
    );
}


//
// Render all the region rects associated with the current image
//
function displayRegions()
{
    if (ViewROIS == false) return;
    if (ListImageRegions[CurrentPosition] == null) return;

    regions = ListImageRegions[CurrentPosition];

    //console.log('displayRegions: ', regions);
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
    var regionCount = 0;
    for (i = 0; i < regionList.length; i++) {

        var region = regionList[i];

        //console.log('Region: ', region);
        if (region.includes('background')) continue;

        // 
        // assumes images of form: name.score.type.box.suffix
        // therefore we want the 3rd part of this string (box)
        // if this form changes then this code must change!
        //
        var name = region.split('.')[2];
        var boundingBox = region.split('.')[3];
        var terms = boundingBox.split('_');
        var x = parseInt(terms[0]);
        var y = parseInt(terms[1]);
        var w = parseInt(terms[2]);
        var h = parseInt(terms[3]);
        //console.log(x, y, w, h, aspectX, aspectY);


        var x = Math.floor(x * aspectX);
        var y = Math.floor(y * aspectY);
        var width = Math.floor(w * aspectX);
        var height = Math.floor(h * aspectY);

        // colors are set based on category of region
        var color;
        if (REGIONS_PEOPLE.includes(name))
            color = REGION_PEOPLE_COLOR;
        else if (REGIONS_LIVING.includes(name))
            color = REGION_LIVING_COLOR;
        else
            color = REGION_WORLD_COLOR;

        var codeCharacter = String.fromCharCode(65 + regionCount);

        ctx.lineWidth = "2";
        ctx.strokeStyle = color;
        ctx.strokeRect(x, y, width, height);

        ctx.font = 'normal 20px Arial';
        ctx.fillStyle = color;
        ctx.fillText(codeCharacter, x, y);
        //console.log('Drawing region: code x y w h ', codeCharacter, x, y, width, height);
        regionCount += 1;
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
	    document.getElementById('ID_VIEW_IMAGE').href = BASE_URL+"/displayimage.html?CURRENTIMAGE="+imagePath;
        //console.log(BASE_URL+"/displayimage.html?CURRENTIMAGE="+imagePath);
    }
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

    DisplayedImage = new Image();
    DisplayedImage.src = imageURL;

    DisplayedImage.onload = function(){

        console.log(DisplayedImage);
        renderCurrentImage();
        displayRegions();
    }

	document.getElementById('ID_IMAGE_STATS').innerHTML = stats;
	setDownloadImageLink(imageURL);

}


function renderCurrentImage()
{
        var canvas  = document.getElementById('ID_CANVAS');
        var ctx = canvas.getContext("2d");

        if (DisplayedImage == null) 
        {
            drawInitialCanvas();
            return;
        }

        var aspectRatioY = canvas.height / DisplayedImage.height;
        canvas.width = DisplayedImage.width * aspectRatioY;

        ctx.drawImage(DisplayedImage, 
            0, 0, 
            DisplayedImage.width, 
            DisplayedImage.height,
            0, 0, 
            canvas.width, canvas.height);

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
	return(getPathFromURL(imageURL));
}

function getPathFromURL(imageURL)
{
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
    console.log("homeImage");
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


function toggleViewROIS()
{
    ViewROIS = !ViewROIS;

    renderCurrentImage();
    displayRegions();
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


function executeWithArg(argValue) 
{
    var arg1 = document.getElementById('ID_ARG1');
    arg1.value = argValue;
    executeConversion();
}


function hide(id)
{
    document.getElementById(id).style.display = 'none';
}


function show(id)
{
    console.log(id);
    // CJM - must be INLINE for ID_CONTROLS_SPAN
    document.getElementById(id).style.display = 'inline';
    //document.getElementById(id).style.display = 'block';
}


function reportOpError(error)
{
    console.log('reportOpError');
	busyStateDeactivate();
	document.getElementById('ID_IMAGE_STATS').innerHTML = error;
}


function reportLoadError(error)
{
var e;

    console.log('reportLoadError');
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


var BusyStateCounter = 0;
function busyStateActivate()
{
    BusyStartRadian = BUSY_RADIAN_START;
    BusyEndRadian = BUSY_RADIAN_START + BUSY_RADIAN_INCREMENT;;
    BusyEndRadian = Math.round(BusyEndRadian * 10 ) / 10;

    BusyStateActive = true;
    BusyStateCounter = 0;
    animateBusyDisplay();
}


function busyStateDeactivate()
{
    console.log('hideBusyImage');

    BusyStateActive = false;

    setTimeout(function () {

        renderCurrentImage();
        displayRegions();
    }, 300);
}

function animateBusyDisplay()
{
    if (BusyStateActive == false) return;

    BusyStateCounter += 1;

    requestAnimationFrame(animateBusyDisplay);

    // run animation at BUSY_FPS rate
    if ((BusyStateCounter % (BUSY_SYSTEM_FPS / BUSY_FPS)) != 0) return;

    var ctx  = document.getElementById('ID_CANVAS').getContext('2d');

    var centerX = (ctx.canvas.width / 2);
    var centerY = (ctx.canvas.height / 2);

    ctx.strokeStyle = BusyColor;
    ctx.fillStyle = BusyColor;

    renderCurrentImage();

    ctx.beginPath();
    ctx.strokeWidth = BUSY_STROKE_WIDTH;
    ctx.lineWidth = BUSY_STROKE_WIDTH;
    ctx.arc(centerX , centerY, BusyRadius, 0,(Math.PI * 2), false);
    ctx.stroke();

    ctx.beginPath();
    ctx.arc(centerX, centerY, BusyRadius,
        Math.PI * BusyStartRadian, Math.PI * BusyEndRadian, false);
    ctx.lineTo(centerX, centerY);
    ctx.fill();

    BusyStartRadian = BusyEndRadian;
    BusyEndRadian += BUSY_RADIAN_INCREMENT;
    if (BusyEndRadian > 2.0) BusyEndRadian = BUSY_RADIAN_INCREMENT;
    BusyEndRadian = Math.round(BusyEndRadian * 10 ) / 10;
}


//
// Save off UI selected region state 
// This is (used later so that user keeps her region selections when
// moving between screens).
//
// This function is called via onChange in DisplayRegionPicker [common.inc]
//
function saveRegionSelection()
{
    var e;

    e  = document.getElementById('ID_SELECTED_REGION');
    if (e != null)
    {
        SelectedRegion = e.options[e.selectedIndex].value;
        console.log('Saving Selected Region', SelectedRegion);
    }
    e  = document.getElementById('ID_SELECTED_SECONDARY_REGION');
    if (e != null)
    {
        SelectedSecondaryRegion = e.options[e.selectedIndex].value;
        console.log('Saving Selected Secondary Region', SelectedSecondaryRegion);
    }

    console.log('getRegionSelection', SelectedRegion);
}

//
// Save off UI selected operation state
// This is (used later so that user keeps her op selections when
// moving between screens).
//
// This function is called via onChange in DisplayOpPicker [common.inc]
//
function saveOpSelection()
{
    var e;

    e  = document.getElementById('ID_SELECTED_OP');
    if (e != null)
    {
        SelectedOp = e.options[e.selectedIndex].value;
        console.log('Saving Selected Op', SelectedOp);
    }
    console.log('saveOpSelection', SelectedOp);
}


function saveSettingSelection()
{
    var e;

    e  = document.getElementById('ID_SELECTED_SETTING');
    if (e != null)
    {
        SelectedSetting = e.options[e.selectedIndex].value;
        console.log('Saving Selected Setting', SelectedOp);
    }
}



function chooseSecondaryImage() 
{
    console.log('chooseSecondaryImage');
    e  = document.getElementById('SUBMITIMAGE');
    e.value=""; // CJM - MUST do this to avoid load caching!
    e.click();
}


function submitSecondaryImage() 
{
    console.log('submitSecondaryImage');
    //CJM DEV - this is where busy image goes
    //document.getElementById('ID_SECONDARY_IMAGE').src = imageURL;
    document.getElementById('ID_LOAD_SECONDARY_IMAGE').submit();
}


function completeSecondaryImageLoad(imageURL, text, width, height)
{
    console.log('completeSecondaryImageLoad:', imageURL);
    CurrentSecondaryImage = imageURL;

    var imageArray = imageURL.split("/");
	imagePath =  CONVERSIONS_PATH+imageArray[imageArray.length - 1];
    /*
    document.getElementById('ID_SECONDARY_IMAGE').src = imageURL;
    document.getElementById('ID_SECONDARY_IMAGE_PATH').value = imagePath;
    */

    var op = './ops/segmentx.php';
    $.post(ENDPOINT_SEGMENT, 
        {
            CURRENTIMAGE: imagePath 
        },
        function(regions, status) 
        {
            CurrentSecondaryRegions = regions;
            console.log('Secondary Regions: ', CurrentSecondaryRegions);

            var regionList = regions.split(',');
            var regionAttributes = '';
            var regionCount = 0;

            var regionCount = 0;
            var regionSelector = document.getElementById('ID_SELECT_SECONDARY_REGION')
            var el = document.createElement("option");
            el.textContent = 'Entire Image';
            el.value = 'ALL';
            regionSelector.appendChild(el);


            for (i = 0; i < regionList.length; i++) {
                var region = regionList[i];
                console.log(region);

                var terms = region.split('.');
                var regionName = terms[2];
                console.log(region, terms[3]);
                var regionDimensions = terms[3].split('_');
                var x = regionDimensions[0];
                var y = regionDimensions[1];
                var width = regionDimensions[2];
                var height = regionDimensions[3];

                if (region.includes('background') == true)
                {
                    code = ' ';
                }
                else
                {
                    code = String.fromCharCode(regionCount+65)+')';
                    regionCount += 1;
                }

                var regionAttribute = code+'  '+regionName+'  '+width+'x'+height;

                var el = document.createElement("option");
                el.textContent = regionAttribute;
                el.value = region;
                regionSelector.appendChild(el);

                var imagePath = getPathFromURL(imageURL);

                document.getElementById('ID_SECONDARY_REGION_PATH').value = region;
                document.getElementById('ID_SECONDARY_IMAGE').src = imageURL;
                document.getElementById('ID_SECONDARY_IMAGE_PATH').value = imagePath;
            }
            var e = document.getElementById('ID_SELECT_SECONDARY_REGION');
        }
    );
}



