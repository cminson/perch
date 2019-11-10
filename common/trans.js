var scrOfX = 0, scrOfY = 0;
function pointselect(event,id)
{
	var t=0;
	var l=0;
	var cNode = document.getElementById(id);
	var e;

//alert('pointselect '+id);

	while (cNode.tagName != 'BODY')
	{
		l += cNode.offsetLeft;
		t += cNode.offsetTop;
		cNode = cNode.offsetParent;
	}
	tempX = event.clientX - l;
	tempY = event.clientY - t;
    getScrollXY();
	e = document.getElementById('CLIENTX');
	e.value = tempX+scrOfX;
	e = document.getElementById('CLIENTY');
	e.value = tempY+scrOfY;
}


function getScrollXY() 
{
if( typeof( window.pageYOffset ) == 'number' ) 
{
    //Netscape compliant
    scrOfY = window.pageYOffset;
    scrOfX = window.pageXOffset;
} 
else if( document.body && ( document.body.scrollLeft || document.body.scrollTop ) ) 
{
    //DOM compliant
    scrOfY = document.body.scrollTop;
    scrOfX = document.body.scrollLeft;
} 
else if( document.documentElement && ( document.documentElement.scrollLeft || document.documentElement.scrollTop ) ) 
{
    //IE6 standards compliant mode
    scrOfY = document.documentElement.scrollTop;
    scrOfX = document.documentElement.scrollLeft;
}
}

function changeBGC(color)
{
    document.bgColor = color;
}


function changecolor(color,id)
{
    var n = document.getElementById('opimage');
    n.style.backgroundColor=color;


}
