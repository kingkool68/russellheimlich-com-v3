function getElem(x) { //x = ID of element passed to this function. 
	var result = Number(document.form1[x].value) //Makes sure the value is a number
	if (result == ""){ //If there is nothing entered we will add a zero.
		document.form1[x].value = 0 //Write zero to the field.
		result = 0
		}
	
	if (isNaN(result)){
		alert(document.form1[x].value+" is not a number. Please correct this and try again."); //Error message if it is not a zero.
		document.form1[x].select(); //Highlights the field that is wrong.
		
		}
	return result; 
	} 
function calc() {
if (isNaN(document.form1.frameRate.value)){ //Defaults to 30 fps if no framerate has been selected.
	document.form1.frameRate.value = 30
	alert("No framerate set. 30 fps will be used instead.")
	}
var hrs = getElem("hrs")
var mins = getElem("mins")
var secs = getElem("secs")
var frames = getElem("frames")
var frameRate = getElem("frameRate")

var totFrames = (hrs*60*60*frameRate) + (mins*60*frameRate) + (secs*frameRate) + frames //Calculates the total number of frames for the amount entered.
document.form1.totFrames.value = totFrames
dataRateArray = new Array(0,270,143,143,270,1500,25,50,100,50,440,135,100,90,50,50,18,25,25,25) // Data rate (Mbps) values for rows 1-19.  dataRate[0] = 0 for simplicity.
GB = 0.000122070312 // 1 Mb = 0.000122070312 GB
for (var x=1;x<=4;x++) {
	dataRate = dataRateArray[x] //Finds the correct data rate for the position in the loop based on the value of x.   
	GBpf = (GB * dataRate)/frameRate //Gigabytes per frame
	alert(dataRate + "*" + GB + ")/" + frameRate + "=" + GBpf)
	totGB = GBpf * totFrames
	var rowPos = "a"+x
	var field = document.getElementById(rowPos)
	field.firstChild.nodeValue = Math.round(totGB*1000)/1000
	//solve(x,totGB);
	
	}

	}
function solve (x,totGB) {

	}