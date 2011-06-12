// smfFadeIndex: the current item in smfFadeContent.
var smfFadeIndex = -1;
// smfFadePercent: percent of fade. (-64 to 510.)
var smfFadePercent = 510
// smfFadeSwitch: direction. (in or out)
var smfFadeSwitch = false;
// smfFadeScroller: the actual div to mess with.
var smfFadeScroller = document.getElementById('smfFadeScroller');
// The ranges to fade from for R, G, and B. (how far apart they are.)
var smfFadeRange = {
	'r': smfFadeFrom.r - smfFadeTo.r,
	'g': smfFadeFrom.g - smfFadeTo.g,
	'b': smfFadeFrom.b - smfFadeTo.b
};

// Divide by 20 because we are doing it 20 times per one ms.
smfFadeDelay /= 20;

// Start the fader!
window.setTimeout('smfFader();', 20);

// Main	fading function... called 50 times every second.
function smfFader()
{
	if (smfFadeContent.length <= 1)
		return;

	// A fix for Internet Explorer 4: wait until the document is loaded so we can use setInnerHTML().
	if (typeof(window.document.readyState) != "undefined" && window.document.readyState != "complete")
	{
		window.setTimeout('smfFader();', 20);
		return;
	}

	// Starting out?  Set up the first item.
	if (smfFadeIndex == -1)
	{
		setInnerHTML(smfFadeScroller, smfFadeBefore + smfFadeContent[0] + smfFadeAfter);
		smfFadeIndex = 1;

		// In Mozilla, text jumps around from this when 1 or 0.5, etc...
		if (typeof(smfFadeScroller.style.MozOpacity) != "undefined")
			smfFadeScroller.style.MozOpacity = "0.90";
		else if (typeof(smfFadeScroller.style.opacity) != "undefined")
			smfFadeScroller.style.opacity = "0.90";
		// In Internet Explorer, we have to define this to use it.
		else if (typeof(smfFadeScroller.style.filter) != "undefined")
			smfFadeScroller.style.filter = "alpha(opacity=100)";
	}

	// Are we already done fading in?  If so, fade out.
	if (smfFadePercent >= 510)
		smfFadeSwitch = !smfFadeSwitch;
	// All the way faded out?
	else if (smfFadePercent <= -64)
	{
		smfFadeSwitch = !smfFadeSwitch;

		// Go to the next item, or first if we're out of items.
		setInnerHTML(smfFadeScroller, smfFadeBefore + smfFadeContent[smfFadeIndex++] + smfFadeAfter);
		if (smfFadeIndex >= smfFadeContent.length)
			smfFadeIndex = 0;
	}

	// Increment or decrement the fade percentage.
	if (smfFadeSwitch)
		smfFadePercent -= 255 / smfFadeDelay * 2;
	else
		smfFadePercent += 255 / smfFadeDelay * 2;

	// If it's not outside 0 and 256... (otherwise it's just delay time.)
	if (smfFadePercent < 256 && smfFadePercent > 0)
	{
		// Easier... also faster...
		var tempPercent = smfFadePercent / 255, rounded;

		if (typeof(smfFadeScroller.style.MozOpacity) != "undefined")
		{
			rounded = Math.round(tempPercent * 100) / 100;
			smfFadeScroller.style.MozOpacity = rounded == 1 ? "0.99" : rounded;
		}
		else if (typeof(smfFadeScroller.style.opacity) != "undefined")
		{
			rounded = Math.round(tempPercent * 100) / 100;
			smfFadeScroller.style.opacity = rounded == 1 ? "0.99" : rounded;
		}
		else
		{
			var done = false;
			if (typeof(smfFadeScroller.filters.alpha) != "undefined")
			{
				// Internet Explorer 4 just can't handle "try".
				eval("try\
					{\
						smfFadeScroller.filters.alpha.opacity = Math.round(tempPercent * 100);\
						done = true;\
					}\
					catch (err)\
					{\
					}");
			}

			if (!done)
			{
				// Get the new R, G, and B. (it should be bottom + (range of color * percent)...)
				var r = Math.ceil(smfFadeTo.r + smfFadeRange.r * tempPercent);
				var g = Math.ceil(smfFadeTo.g + smfFadeRange.g * tempPercent);
				var b = Math.ceil(smfFadeTo.b + smfFadeRange.b * tempPercent);

				// Set the color in the style, thereby fading it.
				smfFadeScroller.style.color = 'rgb(' + r + ', ' + g + ', ' + b + ')';
			}
		}
	}

	// Keep going.
	window.setTimeout('smfFader();', 20);
}