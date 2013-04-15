
function createXmlHttpRequest() {
    try {
        if (window.XMLHttpRequest) {
            return new XMLHttpRequest();
        } else {
            return new ActiveXObject("Microsoft.XMLHTTP");
        }
    } catch (err) {
        return null;
    }
}

function upvoteSubmit(id, direction) {
    xmlhttp = createXmlHttpRequest();
    if (xmlhttp == null) {
        // fall back to regular form submission
        return true;
    }

    xmlhttp.open("POST", "/upvote.php", true);
    xmlhttp.onreadystatechange=function() {
        if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
            xml = xmlhttp.responseXML;
            if (xml != null) {
                xmlStatus = xml.getElementsByTagName('status')[0].firstChild.data;
                xmlUpArrow = xml.getElementsByTagName('uparrow')[0].firstChild.data;
                xmlDownArrow = xml.getElementsByTagName('downarrow')[0].firstChild.data;
                xmlTotal = xml.getElementsByTagName('total')[0].firstChild.data;
            }

            if (xml == null || xmlStatus != "pass") {
                if (direction == "up") {
                    document.forms["upvoteUpForm" + id].submit();
                } else if (direction == "down") {
                    document.forms["upvoteDownForm" + id].submit();
                }
                return;
            }

            upImages = document.getElementsByName("upvoteUpImage" + id);
            for (i = 0; i < upImages.length; i++) {
                if (xmlUpArrow == "Y") {
                    upImages[i].src = "/images/upvote-selected.gif";
                } else {
                    upImages[i].src = "/images/upvote.gif";
                }
            }
            downImages = document.getElementsByName("upvoteDownImage" + id);
            for (i = 0; i < downImages.length; i++) {
                if (xmlDownArrow == "Y") {
                    downImages[i].src = "/images/downvote-selected.gif";
                } else {
                    downImages[i].src = "/images/downvote.gif";
                }
            }
            // FIXME: Use a better way of identifying the counter that allows
            // multiple of the same upvote/downvote arrows.
            counter = document.getElementById("upvoteCounter" + id);
            if (xmlUpArrow == "Y") {
                counter.className = "upvotecount_upvoted";
            } else if (xmlDownArrow == "Y") {
                counter.className = "upvotecount_downvoted";
            } else {
                counter.className = "upvotecount";
            }
            counter.innerHTML = parseInt(xmlTotal);
        }
    }
    xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    xmlhttp.send(
        'upvotes_direction=' + encodeURIComponent(direction) + 
        '&upvotes_id=' + encodeURIComponent(id)
    );
    return false;
}
