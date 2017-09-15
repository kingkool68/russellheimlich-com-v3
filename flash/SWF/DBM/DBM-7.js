//------- Functions -------//
function execute(ratioName, w, h){ 
var AD = activeDocument
var origName = AD.name
var docName = ratioName + "-" + origName
var CurrentFolder = AD.path
AD.duplicate()
activeDocument.resizeCanvas(w,h)
var outputFolder = CurrentFolder + "/" + docName
saveFile = new File(outputFolder);
saveOptions = new JPEGSaveOptions();
      saveOptions.embedColorProfile = true;
      saveOptions.formatOptions = FormatOptions.STANDARDBASELINE;
      saveOptions.quality = 12;

AD.saveAs(saveFile, saveOptions);
activeDocument = documents[docName]
activeDocument.close(SaveOptions.DONOTSAVECHANGES) ;
}

function prep(){
	
var dbText = new File(path + "/Presets/Deviant Bordermaker/DB-Write.txt" );
   dbText.open ('r');
   
var arrNum = dbText.readln()
var docLoc = dbText.readln()
open(File(docLoc))
var hexColor = dbText.readln()
dbText.readln()


var globalArray = new Array(arrNum);
for(a=0;a<=[arrNum-1];a++){
      globalArray[a] = new Array(3);
   } 
for(a=1;a<=[arrNum];a++){
      for(b=1;b<=3;b++){
         globalArray[a-1][b-1] = dbText.readln();
       }
      dbText.readln();
   }

var bgColor = new SolidColor();
bgColor.rgb.hexValue = hexColor;
backgroundColor = bgColor

for (a=0;a<=[arrNum-1];a++){
var ratioName = String(globalArray[a][0])
var w = Number(globalArray[a][1])
var h = Number(globalArray[a][2])
execute(ratioName, w, h)
}

activeDocument.close()
}

//------- sTart of Main Script -------//
var defaultRulerUnits = preferences.rulerUnits;
preferences.rulerUnits = Units.PIXELS;
prep()
preferences.rulerUnits = defaultRulerUnits



