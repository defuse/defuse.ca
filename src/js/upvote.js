/*
 *                      Defuse Security's Upvote System
 *                             https://defuse.ca/
 */

var upvote;
if (!upvote) {
    var upvote = {};
}

// Returns an array of elements of type 'tag' that have class 'klass'
upvote.findElementsWithClass = function (tag, klass) {
    // This function was adapted from http://stackoverflow.com/a/3808886
    var elems = document.getElementsByTagName(tag);
    var elems_with_class = [];
    for (var i = 0; i < elems.length; i++) {
        // If we didn't add the spaces, then it could match part of another
        // class. e.g. class="abc" klass="bc".
        if ((' ' + elems[i].className + ' ').indexOf(' ' + klass + ' ') > -1) {
            elems_with_class.push(elems[i])
        }
    }
    return elems_with_class;
};

upvote.createXmlHttpRequest = function () {
    try {
        if (window.XMLHttpRequest) {
            return new XMLHttpRequest();
        } else {
            return new ActiveXObject("Microsoft.XMLHTTP");
        }
    } catch (err) {
        return null;
    }
};

upvote.submit = function (id, direction) {
    var xmlhttp = upvote.createXmlHttpRequest();
    if (xmlhttp == null) {
        // fall back to regular form submission
        return true;
    }

    xmlhttp.open("POST", "/upvote.php", true);
    xmlhttp.onreadystatechange=function() {
        if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
            // The following code runs when we get a reply from an AJAX request.

            // Put the results in variables for easy access.
            var xml = xmlhttp.responseXML;
            if (xml != null) {
                // "pass" if everything went well.
                var xmlStatus = xml.getElementsByTagName('status')[0].firstChild.data;
                if ( xmlStatus == "pass" ) {
                    // "Y" if the up arrow should be highlighted.
                    var xmlUpArrow = xml.getElementsByTagName('uparrow')[0].firstChild.data;
                    // "Y" if the down arrow should be highlighted.
                    var xmlDownArrow = xml.getElementsByTagName('downarrow')[0].firstChild.data;
                    // The new vote total.
                    var xmlTotal = xml.getElementsByTagName('total')[0].firstChild.data;
                }
            }

            // Fall back to a regular POST request if the AJAX failed.
            if (xml == null || xmlStatus != "pass") {
                if (direction == "up") {
                    var forms = findElementsWithClass('form', "upvoteUpForm" + id);
                    for (var i = 0; i < forms.length; i++) {
                        forms[i].submit();
                        break;
                    }
                } else if (direction == "down") {
                    var forms = findElementsWithClass('form', "upvoteDownForm" + id);
                    for (var i = 0; i < forms.length; i++) {
                        forms[i].submit();
                        break;
                    }
                }
                return;
            }

            // Upvote arrows.
            var upImages = document.getElementsByName("upvoteUpImage" + id);
            for (var i = 0; i < upImages.length; i++) {
                if (xmlUpArrow == "Y") {
                    upImages[i].src = "/images/upvote-selected.gif";
                } else {
                    upImages[i].src = "/images/upvote.gif";
                }
            }
            
            // Downvote arrows.
            var downImages = document.getElementsByName("upvoteDownImage" + id);
            for (var i = 0; i < downImages.length; i++) {
                if (xmlDownArrow == "Y") {
                    downImages[i].src = "/images/downvote-selected.gif";
                } else {
                    downImages[i].src = "/images/downvote.gif";
                }
            }

            // Find all "counters" (displayed counts) for this id.
            var counter_class = 'upvoteCounter' + id;
            var counters = upvote.findElementsWithClass('div', counter_class);
            // Update the color and number of each one.
            for (var i = 0; i < counters.length; i++) {
                var counter = counters[i];
                if (xmlUpArrow == "Y") {
                    counter.className = "upvotecount_upvoted " + counter_class;
                } else if (xmlDownArrow == "Y") {
                    counter.className = "upvotecount_downvoted " + counter_class;
                } else {
                    counter.className = "upvotecount " + counter_class;
                }
                counter.innerHTML = parseInt(xmlTotal);
            }
        }
    };
    xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    xmlhttp.send(
        'upvotes_direction=' + encodeURIComponent(direction) + 
        '&upvotes_id=' + encodeURIComponent(id)
    );
    return false;
};

