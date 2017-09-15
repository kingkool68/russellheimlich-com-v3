//------- Functions -------//

function CreateFolder(){
var paramsFolder = new Folder( path + "/Presets/Deviant Bordermaker");
		paramsFolder.create();
		//return ( new File( paramsFolder + "/DB.txt" ) );
}

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

function prep(DBMdata){
CreateFolder()

var dbText = new File(path + "/Presets/Deviant Bordermaker/DB-Write.txt" );
   dbText.open ('w');
   dbText.writeln(DBMdata)
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

app.activeDocument.close()
}

//------- sTart of Main Script -------//
var defaultRulerUnits = preferences.rulerUnits;
preferences.rulerUnits = Units.PIXELS;
var menu = "dialog{text:'Script Interface',bounds:[100,100,500,600],\
		container:Panel{bounds:[10,10,390,500] , text:'Deviant Bordermaker 2.0  ' ,properties:{borderStyle:'etched',su1PanelCoordinates:true},\
			OK:Button{bounds:[160,450,260,470] , text:'OK' },\
			cancelBtn:Button{bounds:[270,450,370,470] , text:'Cancel' },\
			code:EditText{bounds:[10,60,360,420] , text:'' ,properties:{multiline:true,noecho:false,readonly:false}},\
			copyLabel:StaticText{bounds:[10,40,130,57] , text:'Paste the code below ' ,properties:{scrolling:true,multiline:true}}\
		}\
};"
myMenu = new Window(menu)
myMenu.show()
var DBMdata = myMenu.container.code.text
prep(DBMdata)
preferences.rulerUnits = defaultRulerUnits



