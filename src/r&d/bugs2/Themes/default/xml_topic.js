var smf_topic, smf_start, smf_show_modify, quickReplyCollapsed, buff_message;
var cur_msg_id, cur_msg_div, buff_subject, cur_subject_div, in_edit_mode = 0;

function doQuote(messageid, cur_session_id)
{
	if (quickReplyCollapsed)
		window.location.href = smf_scripturl + "?action=post;quote=" + messageid + ";topic=" + smf_topic + "." + smf_start + ";sesc=" + cur_session_id;
	else
	{
		if (window.XMLHttpRequest)
		{
			if (typeof window.ajax_indicator == "function")
				ajax_indicator(true);
			getXMLDocument(smf_scripturl + "?action=quotefast;quote=" + messageid + ";sesc=" + cur_session_id + ";xml", onDocReceived);
		}
		else
			reqWin(smf_scripturl + "?action=quotefast;quote=" + messageid + ";sesc=" + cur_session_id, 240, 90);

		if (navigator.appName == "Microsoft Internet Explorer")
			window.location.hash = "quickreply";
		else
			window.location.hash = "#quickreply";
	}
}

function onDocReceived(XMLDoc)
{
	var text = "";
	for (var i = 0; i < XMLDoc.getElementsByTagName("quote")[0].childNodes.length; i++)
		text += XMLDoc.getElementsByTagName("quote")[0].childNodes[i].nodeValue;

	replaceText(text, document.forms.postmodify.message);
	if (typeof window.ajax_indicator == "function")
		ajax_indicator(false);
}


function modify_msg(msg_id, cur_session_id)
{
	if (!window.XMLHttpRequest)
		return;
	if (typeof(window.opera) != "undefined")
	{
		var test = new XMLHttpRequest();
		if (typeof(test.setRequestHeader) != "function")
			return;
	}
	if (in_edit_mode == 1)
		modify_cancel();
	in_edit_mode = 1;
	if (typeof window.ajax_indicator == "function")
		ajax_indicator(true);
	getXMLDocument(smf_scripturl + '?action=quotefast;quote=' + msg_id + ';sesc=' + cur_session_id + ';modify;xml', onDocReceived_modify);
}

function onDocReceived_modify(XMLDoc)
{
	var text = "";
	var subject = "";

	// Grab the message ID.
	cur_msg_id = XMLDoc.getElementsByTagName("message")[0].getAttribute("id");

	// Replace the body part.
	for (var i = 0; i < XMLDoc.getElementsByTagName("message")[0].childNodes.length; i++)
		text += XMLDoc.getElementsByTagName("message")[0].childNodes[i].nodeValue;
	cur_msg_div = document.getElementById(cur_msg_id);
	buff_message = getInnerHTML(cur_msg_div);

	// Actually create the content, with a bodge for dissapearing dollar signs.
	text = text.replace(/\$/g,"{&dollarfix;$}");
	text = smf_template_body_edit.replace(/%body%/, text).replace(/%msg_id%/g, cur_msg_id.substr(4));
	text = text.replace(/\{&dollarfix;\$\}/g,"$");
	setInnerHTML(cur_msg_div, text);
	
	// Replace the subject part.
	cur_subject_div = document.getElementById('subject_' + cur_msg_id.substr(4));
	buff_subject = getInnerHTML(cur_subject_div);

	subject = XMLDoc.getElementsByTagName("subject")[0].childNodes[0].nodeValue;
	subject = subject.replace(/\$/g,"{&dollarfix;$}");
	subject = smf_template_subject_edit.replace(/%subject%/, subject);
	subject = subject.replace(/\{&dollarfix;\$\}/g,"$");
	setInnerHTML(cur_subject_div, subject);
	if (typeof window.ajax_indicator == "function")
		ajax_indicator(false);
}

function modify_cancel()
{
	// Roll back the HTML to its original state.
	setInnerHTML(cur_msg_div, buff_message);
	setInnerHTML(cur_subject_div, buff_subject);

	// No longer in edit mode, that's right.
	in_edit_mode = 0;

	return false;
}

function modify_save(cur_session_id)
{
	if (!in_edit_mode)
		return true;

	var i, x = new Array();
	x[x.length] = 'subject=' + escape(textToEntities(document.forms.quickModForm['subject'].value.replace(/&#/g, "&#38;#"))).replace(/\+/g, "%2B");
	x[x.length] = 'message=' + escape(textToEntities(document.forms.quickModForm['message'].value.replace(/&#/g, "&#38;#"))).replace(/\+/g, "%2B");
	x[x.length] = 'topic=' + parseInt(document.forms.quickModForm.elements['topic'].value);
	x[x.length] = 'msg=' + parseInt(document.forms.quickModForm.elements['msg'].value);

	if (typeof window.ajax_indicator == "function")
		ajax_indicator(true);

	sendXMLDocument(smf_scripturl + "?action=jsmodify;topic=" + smf_topic + ";sesc=" + cur_session_id + ";xml", x.join("&"), modify_done);

	return false;
}

function modify_done(XMLDoc)
{
	if (!XMLDoc)
	{
		modify_cancel();
		return;
	}

	var message = XMLDoc.getElementsByTagName("smf")[0].getElementsByTagName("message")[0];
	var body = message.getElementsByTagName("body")[0];
	var error = message.getElementsByTagName("error")[0];

	if (body)
	{
		// Show new body.
		var bodyText = "";
		for (i = 0; i < body.childNodes.length; i++)
			bodyText += body.childNodes[i].nodeValue;

		bodyText = bodyText.replace(/\$/g,"{&dollarfix;$}");
		bodyText = smf_template_body_normal.replace(/%body%/, bodyText);
		bodyText = bodyText.replace(/\{&dollarfix;\$\}/g,"$");
		setInnerHTML(cur_msg_div, bodyText);
		buff_message = bodyText;

		// Show new subject.
		var subject = message.getElementsByTagName("subject")[0];
		var subject_text = subject.childNodes[0].nodeValue;
		subject_text = subject_text.replace(/\$/g,"{&dollarfix;$}");
		var subject_html = smf_template_subject_normal.replace(/%msg_id%/g, cur_msg_id.substr(4)).replace(/%subject%/, subject_text);
		subject_html = subject_html.replace(/\{&dollarfix;\$\}/g,"$");
		setInnerHTML(cur_subject_div, subject_html);
		buff_subject = subject_html;
		
		// If this is the first message, also update the topic subject.
		if (subject.getAttribute("is_first") == 1)
		{
			var subject_top = smf_template_top_subject.replace(/%subject%/, subject_text);
			subject_top = subject_top.replace(/\{&dollarfix;\$\}/g,"$");
			setInnerHTML(document.getElementById("top_subject"), subject_top);
		}

		// Show this message as "modified on x by y".
		if (smf_show_modify)
		{
			var cur_modify_div = document.getElementById('modified_' + cur_msg_id.substr(4));
			setInnerHTML(cur_modify_div, message.getElementsByTagName("modified")[0].childNodes[0].nodeValue);
		}
	}
	else if (error)
	{
		setInnerHTML(document.getElementById("error_box"), error.childNodes[0].nodeValue);
		document.forms.quickModForm.message.style.border = error.getAttribute("in_body") == "1" ? "1px solid red" : "";
		document.forms.quickModForm.subject.style.border = error.getAttribute("in_subject") == "1" ? "1px solid red" : "";
	}

	if (typeof window.ajax_indicator == "function")
		ajax_indicator(false);
}

function showModifyButtons()
{
	var numImages = document.images.length;
	for (var i = 0; i < numImages; i++)
		if (document.images[i].id.substr(0, 14) == 'modify_button_')
			document.images[i].style.display = '';
}

function expandThumb(thumbID)
{
	var img = document.getElementById('thumb_' + thumbID);
	var link = document.getElementById('link_' + thumbID);
	var tmp = img.src;
	img.src = link.href;
	link.href = tmp;
	img.style.width = '';
	img.style.height = '';
	return false;
}

function swapQuickReply()
{
	document.getElementById("quickReplyExpand").src = smf_images_url + "/" + (quickReplyCollapsed ? "collapse.gif" : "expand.gif");
	document.getElementById("quickReplyOptions").style.display = quickReplyCollapsed ? "" : "none";

	quickReplyCollapsed = !quickReplyCollapsed;
}
