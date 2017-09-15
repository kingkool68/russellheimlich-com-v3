function emailCheck(emailStr) {
	var emailPat = /^(.+)@(.+)$/;
	var specialChars = "\\(\\)<>@,;:\\\\\\\"\\.\\[\\]";
	var validChars = "\[^\\s" + specialChars + "\]";
	var quotedUser = "(\"[^\"]*\")";
	var ipDomainPat = /^\[(\d{1,3})\.(\d{1,3})\.(\d{1,3})\.(\d{1,3})\]$/;
	var atom = validChars + '+';
	var word = "(" + atom + "|" + quotedUser + ")";
	var userPat = new RegExp("^" + word + "(\\." + word + ")*$");
	var domainPat = new RegExp("^" + atom + "(\\." + atom + ")*$");
	if (emailStr == "") {
		return ("is required!");
	}
	var matchArray = emailStr.match(emailPat);
	if (matchArray == null) {
		return ("is incorrect.\nCheck @ and .'s");
	}
	var user = matchArray[1];
	var domain = matchArray[2];
	if (user.match(userPat) == null) {
		return ("is incorrect.\nCheck to the left of the @");
	}
	var IPArray = domain.match(ipDomainPat);
	if (IPArray != null) {
		for (var i = 1; i <= 4; i++) {
			if (IPArray[i] > 255) {
				return ("is incorrect.\nMake sure your IP address contains numbers 255 and lower.");
			}
		}
		return true;
	}
	var domainArray = domain.match(domainPat);
	if (domainArray == null) {
		return ("is incorrect.\nCheck the domain name");
	}
	var atomPat = new RegExp(atom, "g");
	var domArr = domain.match(atomPat);
	var len = domArr.length;
	if (domArr[domArr.length - 1].length < 2 || domArr[domArr.length - 1].length > 3) {
		return ("is incorrect.\nThe address must end with .com or .co.uk for example.")
	}
	return ("is correct.")
}

function nameEval() {
	var realName = document.getElementById("realname")
	var nameStar = document.getElementById("nameStar")
	if (realName.value == "") {
		nameStar.firstChild.nodeValue = "Your Name is required!"
		nameStar.className = "bad"
	} else {
		nameStar.firstChild.nodeValue = "Your Name is good!"
		nameStar.className = "good"
	}
}

function emailEval() {
	var emailFrom = document.getElementById("emailFrom")
	var emailStar = document.getElementById("emailStar")
	var message = emailCheck(emailFrom.value)
	emailStar.firstChild.nodeValue = "Your Email Address " + message
	if (message == "is correct.") {
		emailStar.className = "good"
	} else {
		emailStar.className = "bad"
	}
}

function messageEval() {
	var messageField = document.getElementById("message")
	var messageStar = document.getElementById("messageStar")
	if (messageField.value == "") {
		messageStar.firstChild.nodeValue = "Your Message is required!\n Don't you want to tell me something?"
		messageStar.className = "bad"
	} else {
		messageStar.firstChild.nodeValue = "Your Message is good!"
		messageStar.className = "good"
	}
}

function allEval() {
	nameEval()
	emailEval()
	messageEval()
	if (document.getElementById("messageStar").className == "bad" || document.getElementById("emailStar").className == "bad" || document.getElementById("nameStar").className == "bad") {
		var error = confirm("There are errors with the data you entered. \n\n To submit the form anyway press OK \n To go back and fix the errors in red press CANCEL")
		return error
	} else {
		return true
	}
}