/*********************************************************
Project		XML Photo Gallery
Creation	Tue Nov 30, 2004  10:02 PM
Author		Matthew Keefe [mkeefedesign]
*********************************************************
License
*********************************************************
This can be used in both Education and Commercial, 
the only thing I ask in return is for you to contact me 
if you do decide to use it. I am always wondering what 
people get out of my examples.
*********************************************************/
title_txt.htmlText = "<font color=\"#000000\">Title: </font><font color=\"#FFFFFF\">Splash Screen</font>";
/* Debug Variables */
//debug = 1; //Comment this "debug" line to stop the movie from 'tracing' or 'outputting'
holderMC._alpha = 0;
var picArray = new Array();
var tempArray = new Array();
var linkArray = new Array();
var SPACING = 10;
var DEPTH = 0;
var xmlFILE = "galleryAction.xml";
function printer(str) {
	// If debug flag is set, output different operation in the movie
	debug ? trace(str) : null;
}
MovieClip.prototype.loadPic = function(pic) {
	picnum_txt.text = (pic+1)+"/"+NUMOFPICS;
	// Add one to the 'pic' var since an array starts at 0
	printer("STATUS: Loading Picture "+pic);
	holderMC._alpha = 0;
	cur = pic;
	if (pathToPICS.length>1) {
		this.loadMovie(pathToPICS+picArray[pic]);
	} else {
		this.loadMovie(picArray[pic]);
	}
	this._parent.onEnterFrame = function() {
		var t = holderMC.getBytesTotal(), l = holderMC.getBytesLoaded();
		if (t != 0 && Math.round(l/t) == 1 && holderMC._width>0) {
			var w = holderMC._width+SPACING, h = holderMC._height+SPACING;
			border.resizePic(w, h, pic);
			delete this._parent.onEnterFrame;
		}
	};
};
MovieClip.prototype.resizePic = function(w, h, pic) {
	var speed = 3;
	holderMC._alpha = 0;
	this.onEnterFrame = function() {
		this._width += (w-this._width)/speed;
		this._height += (h-this._height)/speed;
		title_txt.htmlText = "<font color=\"#000000\"><b>Currently Viewing:</b></font>&nbsp;"+"<font color=\"#FFFFFF\">"+tempArray[pic]+"</font>";
		url_btn.onRelease = function() {
			trace("Image Url: "+linkArray[pic]);
			getURL(linkArray[pic], "_blank");
		};
		if (Math.abs(this._width-w)<1 && Math.abs(this._height-h)<1) {
			this._width = w;
			this._height = h;
			holderMC._x = this._x-this._width/2+SPACING/2;
			holderMC._y = this._y-this._height/2+SPACING/2;
			holderMC._alpha += 5;
			if (holderMC._alpha>90) {
				holderMC._alpha = 100;
				delete this.onEnterFrame;
			}
		}
	};
};
var gallery_xml = new XML();
gallery_xml.ignoreWhite = true;
gallery_xml.onLoad = function(success) {
	if (success) {
		printer("RESULT: XML File Loaded!");
		var gallery = this.firstChild;
		pathToPICS = gallery.attributes.picturePath;
		NUMOFPICS = gallery.childNodes.length;
		printer("Number of Pics: '"+NUMOFPICS+"'");
		printer("Path to Pics: '"+pathToPICS+"'");
		for (var i = 0; i<NUMOFPICS; i++) {
			tempArray.push(gallery.childNodes[i].attributes.title);
			picArray.push(gallery.childNodes[i].attributes.source);
			linkArray.push(gallery.childNodes[i].attributes.link);
		}
		//loading first picture
		holderMC.loadPic(0);
		// Button Code
		prev.onRelease = function() {
			if (cur == 0) {
				holderMC.loadPic(picArray.length-1);
			} else {
				holderMC.loadPic(cur-1);
			}
		};
		prev.onRollOver = function() {
			this.gotoAndStop(2);
		};
		prev.onRollOut = function() {
			this.gotoAndStop(1);
		};
		next.onRelease = function() {
			if (cur == picArray.length-1) {
				holderMC.loadPic(0);
			} else {
				holderMC.loadPic(cur+1);
			}
		};
		next.onRollOver = function() {
			this.gotoAndStop(2);
		};
		next.onRollOut = function() {
			this.gotoAndStop(1);
		};
	} else {
		printer("ERROR: XML File NOT Loaded '"+xmlFILE+" '");
	}
};
gallery_xml.load(xmlFILE);
// Load the xml file
printer("XML File: "+xmlFILE);
