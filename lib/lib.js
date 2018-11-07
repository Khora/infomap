var numberOfCookiesToBeUsed = 10;
var maxLengthOfCookieArray = 300;
var cookieValidDurationDays = 300;
var cookiePrefix = "ifc_"; // Infomap Favorites Cookie
var starElementPrefix = "s_";
var starImageElements = [];

function searchTable(isMobile) {
    var input, filter, found, table, tr, td, i, j;
    input = document.getElementById("infomapSearch");
    filter = input.value.toUpperCase();
    table = document.getElementById("table");
    tr = table.getElementsByTagName("tr");

    backgroundColor = "FDFDFD";
    startingPoint = 1;
    lengthOfDataset = 2;
    if (isMobile) {
        startingPoint = 0;
        lengthOfDataset = 6;
    }
    
    for (i = startingPoint; i < tr.length; i = i + lengthOfDataset) {
        containsText = false;
        for (j = i; j < i + lengthOfDataset && j < tr.length; j++) {
            td = tr[j].getElementsByTagName("td");
            for (k = 0; k < td.length; k++) {
                if (matchingMethod(td[k].innerHTML, filter)) {
                    containsText = true;
                }
            }
        }
        
        if (containsText || filter == "") {
            for (j = i; j < i + lengthOfDataset && j < tr.length; j++) {
                tr[j].style.display = "";
                if (!isMobile) {
                    td = tr[j].getElementsByTagName("td");
                    for (k = 0; k < td.length; k++) {
                        td[k].style.backgroundColor = backgroundColor;
                    }
                }
            }
            
            if (backgroundColor === "FDFDFD") {
                backgroundColor = "#F1F1F1";
            } else {
                backgroundColor = "FDFDFD";
            }
        } else {
            for (j = i; j < i + lengthOfDataset && j < tr.length; j++) {
                tr[j].style.display = "none";
            }
        }
    }
}

function matchingMethod(t, stringToBeChecked) {
    var retVal = (t.toUpperCase().indexOf(stringToBeChecked) > -1);
    if (t.toUpperCase().indexOf("ACTIVE.PNG") > -1) {
        retVal = false;
    }
    return retVal;
}

function changeIsOpen(element) {
	if(!element.style.display) {
		element.style.display = "block";
    }
	if (element.style.display !== "none") {
        element.style.display = "none";
    } else {
        element.style.display = "block";
    }
}

function elementIsVisible(element) {
    return element.style.display !== "none";
}

function closeOthersAndChangeIsOpen(element) {
	document.getElementById('settingsField').style.display = "none";
	document.getElementById('infoField').style.display = "none";
	changeIsOpen(element);
}

function hideDiv(element) {
	element.style.display = "none";
}

function showDiv(element) {
	element.style.display = "block";
}

function hideIfNoMobileDevice(element) {
	if(!isMobileOrTablet()) {
		hideDiv(element);
	}
}

function isMobileOrTablet() {
	if(navigator.userAgent.match(/Android/i)
		|| navigator.userAgent.match(/webOS/i)
		|| navigator.userAgent.match(/iPhone/i)
		|| navigator.userAgent.match(/iPad/i)
		|| navigator.userAgent.match(/iPod/i)
		|| navigator.userAgent.match(/BlackBerry/i)
		|| navigator.userAgent.match(/Windows Phone/i)){
		return true;
	}
	return false;
}

function getCurrentFileName() {
    var url = window.location.pathname;
    var lastUri = url.substring(url.lastIndexOf('/') + 1);
    if (lastUri.indexOf('?') != -1) {
        return lastUri.substring(0, lastUri.indexOf('?'));
    }
    else {
        return lastUri;
    }
}

function goToMobilePage() {
    if (!isMobileOrTablet()) {
        document.location = getCurrentFileName() + "?mobile=true";
    }
}

function resizeHeaderElements() {
    var widthOfDisplayArea = window.innerWidth || document.documentElement.clientWidth || document.body.clientWidth;
    var widthOfLogo = document.getElementById('logo').offsetWidth;
    var widthOfTitle = document.getElementById('title').clientWidth;
    var widthOfMenuArea = document.getElementById('menuArea').offsetWidth;
    
    var widthCombinedWithSpacing = widthOfLogo + widthOfTitle + widthOfMenuArea + 100;
    var spaceAvailableLeftAndRightCombined = Math.max(0, widthOfDisplayArea - widthCombinedWithSpacing);
    
    var leftOfLogo = spaceAvailableLeftAndRightCombined / 2;
    var leftOfTitle = spaceAvailableLeftAndRightCombined / 2 + 200;
    var leftOfMenuArea = spaceAvailableLeftAndRightCombined / 2 + 575;
    
    document.getElementById('logo').style.left = leftOfLogo + 'px';
    document.getElementById('title').style.left = leftOfTitle + 'px';
    document.getElementById('menuArea').style.left = leftOfMenuArea + 'px';
    
    if (spaceAvailableLeftAndRightCombined == 0) {
        goToMobilePage();
    }
}

function changeIcon(element, newIconPath) {
	element.src = newIconPath;
}

function hideAllInTableThatAreNotFavorites(isMobile) {
    var currentFavoritesIds = getFavorites();
    var table, tr, td, i, j;
    table = document.getElementById("table");
    tr = table.getElementsByTagName("tr");
    
    backgroundColor = "#ffffff";
    startingPoint = 1;
    lengthOfDataset = 2;
    if (isMobile) {
        startingPoint = 0;
        lengthOfDataset = 6;
    }
    
    for (i = startingPoint; i < tr.length; i = i + lengthOfDataset) {
        containsText = false;
        for (j = i; j < i + lengthOfDataset && j < tr.length; j++) {
            td = tr[j].getElementsByTagName("td");
            for (k = 0; k < td.length; k++) {
                if (td[k].innerHTML === '<img src="img/starActive.png">') {
                    containsText = true;
                }
            }
        }
        
        if (containsText) {
            for (j = i; j < i + lengthOfDataset && j < tr.length; j++) {
                tr[j].style.display = "";
                if (!isMobile) {
                    td = tr[j].getElementsByTagName("td");
                    for (k = 0; k < td.length; k++) {
                        td[k].style.backgroundColor = backgroundColor;
                    }
                }
            }
            
            if (backgroundColor === "FDFDFD") {
                backgroundColor = "#F1F1F1";
            } else {
                backgroundColor = "FDFDFD";
            }
        } else {
            for (j = i; j < i + lengthOfDataset && j < tr.length; j++) {
                tr[j].style.display = "none";
            }
        }
    }
}

function getCurrentlyVisible(isMobile) {
    var currentlyVisible = [];
    
    var input, filter, found, table, tr, td, i, j;
    input = document.getElementById("infomapSearch");
    filter = input.value.toUpperCase();
    table = document.getElementById("table");
    tr = table.getElementsByTagName("tr");

    startingPoint = 1;
    lengthOfDataset = 2;
    if (isMobile) {
        startingPoint = 0;
        lengthOfDataset = 6;
    }
    
    for (i = startingPoint; i < tr.length; i = i + lengthOfDataset) {
        containsText = false;
        for (j = i; j < i + lengthOfDataset && j < tr.length; j++) {
            td = tr[j].getElementsByTagName("td");
            for (k = 0; k < td.length; k++) {
                if (matchingMethod(td[k].innerHTML, filter)) {
                    containsText = true;
                }
            }
        }
        
        if (containsText || filter == "") {
            currentlyVisible.push(Math.ceil(i / lengthOfDataset) + 1 - startingPoint);
        }
    }
    
    return currentlyVisible;
}

function updateAllStarImages() {
    updateStarImages(starImageElements);
}

function updateStarImages(idsToUpdate) {
    var currentFavoritesIds = getFavorites();
    for (i = 0; i < idsToUpdate.length; i++) {
        if (currentFavoritesIds.indexOf(idsToUpdate[i]) >= 0) {
            document.getElementById(starElementPrefix + idsToUpdate[i]).innerHTML = "<img src='img/starActive.png'>";
        } else {
            document.getElementById(starElementPrefix + idsToUpdate[i]).innerHTML = "<img src='img/starInactive.png'>";
        }
    }
}

function toggleAllCurrentlyVisibleAndUpdateStarImages(isMobile) {
    toggleFavoritesAndUpdateStarImages(getCurrentlyVisible(isMobile));
}

function toggleFavoritesAndUpdateStarImages(listOfIds) {
    toggleFavorites(listOfIds);
    updateStarImages(listOfIds);
}

function toggleFavorites(listOfIds) {
    var currentFavoritesIds = getFavorites();
    var toAdd = [];
    var toRemove = [];
    for (i = 0; i < listOfIds.length; i++) { 
        if (currentFavoritesIds.indexOf(listOfIds[i]) < 0) {
            toAdd.push(listOfIds[i]);
        } else {
            toRemove.push(listOfIds[i]);
        }
    }
    removeFromFavorites(toRemove);
    addToFavorites(toAdd);
}

function addToFavorites(listOfIds) {
    var currentFavoritesIds = getFavorites();
    var newFavoritesIds = addElementsUniquelyToArray(currentFavoritesIds, listOfIds);
	rewriteCookiesWithFavorites(newFavoritesIds);
}

function removeFromFavorites(listOfIds) {
	var currentFavoritesIds = getFavorites();
    var newFavoritesIds = removeElementsFromArray(currentFavoritesIds, listOfIds);
	rewriteCookiesWithFavorites(newFavoritesIds);
}

function clearFavorites() {
	for (i = 0; i < numberOfCookiesToBeUsed; i++) { 
        deleteCookie(cookiePrefix + i);
    }
    updateAllStarImages();
}

function getFavorites() {
    var cookiesArray = [];
	for (i = 0; i < numberOfCookiesToBeUsed; i++) {
        var cookieString = getCookie(cookiePrefix + i);
        if (cookieString !== "") {
            cookiesArray.push(arrayStringToIntegerArray(cookieString));
        }
    }
    return combineArray(cookiesArray);
}

function rewriteCookiesWithFavorites(listOfIds) {
	for (i = 0; i < numberOfCookiesToBeUsed; i++) { 
        deleteCookie(cookiePrefix + i);
    }
    var splitUpFavorites = splitArray(listOfIds, maxLengthOfCookieArray);
    for (i = 0; i < splitUpFavorites.length; i++) { 
        setCookie(cookiePrefix + i, integerArrayToArrayString(splitUpFavorites[i]), cookieValidDurationDays)
    }
}

function addElementsUniquelyToArray(array, elementsToAddUniquely) {
    for (i = 0; i < elementsToAddUniquely.length; i++) {
        if (array.indexOf(elementsToAddUniquely[i]) < 0) {
            array.push(elementsToAddUniquely[i]);
        }
    }
    return array;
}

function removeElementsFromArray(array, elementsToRemove) {
    var retArray = array.slice(0);
    for (i = 0; i < elementsToRemove.length; i++) {
        indexToRemove = retArray.indexOf(elementsToRemove[i]);
        if (indexToRemove >= 0) {
            retArray.splice(indexToRemove, 1);
        }
    }
    return retArray;
}

function splitArray(array, splitAtEveryNumberOfItems) {
    var retArray = [];
	for (i = 0; i < array.length; i = i + splitAtEveryNumberOfItems) { 
        retArray.push(array.slice(i, i + splitAtEveryNumberOfItems));
    }
    return retArray;
}

function combineArray(arrayOfArrays) {
    var retArray = [];
	for (i = 0; i < arrayOfArrays.length; i = i + 1) { 
        for (j = 0; j < arrayOfArrays[i].length; j = j + 1) { 
            retArray.push(arrayOfArrays[i][j]);
        }
    }
    return retArray;
}

function setCookie(name, value, validDurationDays) {
    if (!checkCookie()) {
        alert("Cookies are disabled! Favorites Functionality will not work!");
        return;
    }
    
    var d = new Date();
    d.setTime(d.getTime() + (validDurationDays * 24 * 60 * 60 * 1000));
    var expires = "expires=" + d.toUTCString();
    document.cookie = name + "=" + value + ";" + expires + ";path=/";
}

function deleteCookie(name) {
    if (!checkCookie()) {
        alert("Cookies are disabled! Favorites Functionality will not work!");
        return;
    }
    
    document.cookie = name + "=;expires=Sat, 1 Jan 2000 00:00:00 GMT;path=/";
}

function getCookie(name) {
    if (!checkCookie()) {
        alert("Cookies are disabled! Favorites Functionality will not work!");
        return;
    }
    
    if (document.cookie.length > 0) {
        c_start = document.cookie.indexOf(name + "=");
        if (c_start != -1) {
            c_start = c_start + name.length + 1;
            c_end = document.cookie.indexOf(";", c_start);
            if (c_end == -1) {
                c_end = document.cookie.length;
            }
            return unescape(document.cookie.substring(c_start, c_end));
        }
    }
    return "";
}

function arrayStringToIntegerArray(data) {
    return JSON.parse(data);
}

function integerArrayToArrayString(data) {
    return JSON.stringify(data);
}

function checkCookie() {
    var cookieEnabled = navigator.cookieEnabled;
    if (!cookieEnabled) { 
        document.cookie = "testcookie";
        cookieEnabled = document.cookie.indexOf("testcookie") != -1;
    }
    return cookieEnabled;
}