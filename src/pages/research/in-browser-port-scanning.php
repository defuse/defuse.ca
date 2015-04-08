<h1>Port Scanning in the Browser</h1>

<div style="border: solid black 1px; padding: 20px;">
    <p style="text-align: center; font-size: 16pt; margin: 0;">
    <strong>Timing Side Channel Port Scanner in the Browser</strong>
    </p>

    <p>
        This is a TCP port scanner inside your browser! It can scan your local
        network! Since this web page can do it, any web page you visit can do
        it, possibly behind the scenes without you knowing about it.
    </p>

    <p>
        Actually, there is an important limitation. It can only distinguish between
        two cases:
    </p>

    <ol>
        <li>The port is open <em>or</em> the port is closed and responds right away with a TCP RST or ICMP packet.</li>
        <li>The port is &quot;stealthed&quot; or there isn't a host at that IP address at all.</li>
    </ol>

    <p>
        The scanner works by timing how long it takes your browser to give up
trying to load a nonexistent image file. If it fails fast (less than 1.5s), it's
Case 1. If it fails slowly (more than 1.5s), it's Case 2.
    <p>

    <p>
        <strong>Local Network Scan (Proof of Concept)</strong>
    </p>

    <form>
        <input type="button" id="lan_button" value="Scan 192.168.1.* port 80" onclick="lan_scan(this.form);" />
        <input type="button" id="lan_button_stop" value="Stop Scan" onclick="lan_stop(this.form);" disabled="disabled"/>
    </form>

    <div id="lan_results" style="padding-top: 10px;"></div>

    <p>
        <strong>Custom Scan</strong>
    </p>

    <form>
        <table>
            <tr>
                <td>IP Address:&nbsp;&nbsp;</td>
                <td><input type="text" name="custom_ipaddr" value="192.168.1.1"></input></td>
            </tr>
            <tr>
                <td>Port:</td>
                <td><input type="text" name="custom_port" value="80"></input></td>
            </tr>
            <tr>
                <td></td>
                <td style="text-align: right;">
                    <input type="button" value="Scan" id="custom_button" onclick="custom_scan(this.form);" />
                </td>
            </tr>
        </table>
    </form>

    <div id="custom_result"></div>

    <p>
            Note: This doesn't always work with all ports. <br /> For example, Firefox
seems to know not to send a request to port 22 (SSH) or 110 (POP).
    </p>
</div>

<div id="testdiv" style="visibility: hidden"></div>

<script>

    /* The scanner needs these global variables for an ugly hack. */
    var last_scanobj_index = 0;
    var scanobjs = {};
    function PortScanner(ip, port)
    {
        
        this.ip = ip;
        this.port = port;
        this.on_open_or_closed = null;
        this.on_stealthed = null;
        this.start_time = null;
        this.timed_out = null;
        this.total_time = null;

        this.run = function () {
            /* Check that the client gave us all the callbacks we need. */
            if (this.on_open_or_closed == null) {
                alert("Please set the on_open_or_closed callback!");
            }
            if (this.on_stealthed == null) {
                alert("Please set the on_stealthed callback!");
            }

            /* Save this object in the global directory (UGLY HACK). */
            var our_scanobj_index = last_scanobj_index;
            last_scanobj_index++;
            scanobjs[our_scanobj_index] = this;

            /* Record the starting time. */
            this.start_time = (new Date()).getTime();

            /* Create the div to load the image, passing our object's index into
                the global directory so that it can be retrieved. */
            document.getElementById("testdiv").innerHTML = '<img src="http://' + ip + ':' + port + 
                '" alt="" onerror="error_handler(' + our_scanobj_index + ');" />';

            // XXX: What's the right way to do this in JS?
            var thiss = this;
            setTimeout(
                function () {
                    /* This will be non-null if the event hasn't fired yet. */
                    if (scanobjs[our_scanobj_index]) {
                        scanobjs[our_scanobj_index] = null;
                        thiss.timed_out = true;
                        thiss.on_stealthed();
                    }
                },
                10000
            );
        }
    }

    function error_handler(index)
    {
        /* Get the PortScanner object back. */
        var thiss = scanobjs[index];

        /* If it's null, the scan timed out. */
        if (thiss == null) {
            return;
        }
        /* Set it to null so the timeout knows we handled it. */
        scanobjs[index] = null;
        thiss.timed_out = false;

        /* Measure the amount of time it took for the load to fail. */
        thiss.total_time = (new Date()).getTime() - thiss.start_time;

        /* Call the appropriate callback. */
        if (thiss.total_time < 1500) {
            thiss.on_open_or_closed();
        } else {
            thiss.on_stealthed();
        }
    }

    function custom_scan(form)
    {
        var ip = form.custom_ipaddr.value;
        var port = form.custom_port.value;
        var ip_addr_re = /^(\d{1,3})\.(\d{1,3})\.(\d{1,3})\.(\d{1,3})$/;

        var match = ip_addr_re.exec(ip);
        if ( match == null ) {
            alert("That isn't a valid IPv4 address.");
            return;
        }

        if (match[1] > 255 || match[2] > 255 || match[3] > 255 || match[4] > 255) {
            alert("That isn't a valid IPv4 address.");
        }

        port = parseInt(port);
        if (isNaN(port) || port < 0 || port > 65535) {
            alert("Bad port number");
        }

        document.getElementById("custom_button").disabled = true;
        document.getElementById("custom_result").innerHTML = "Scanning... This will take up to 10 seconds.";

        var scanner = new PortScanner(ip, port);

        scanner.on_stealthed = function () {
            if (scanner.timed_out) {
                document.getElementById("custom_result").innerHTML = "Case 2 (no response after 10s).";
            } else {
                document.getElementById("custom_result").innerHTML = "Case 2 (" + this.total_time + " ms).";
            }
            document.getElementById("custom_button").disabled = false;
        }

        scanner.on_open_or_closed = function () {
            document.getElementById("custom_result").innerHTML = "Case 1 (" + this.total_time + " ms)."
            document.getElementById("custom_button").disabled = false;
        }

        scanner.run();
    }

    /* This variable keeps track of which 192.168.1 IP to scan next. */
    var current_octet;
    function lan_scan(form)
    {
        document.getElementById("lan_button").disabled = true;
        document.getElementById("lan_button_stop").disabled = false;

        /* Skip .1 since it might visibly prompt for a password. */
        current_octet = 2;

        var scanner = new PortScanner("192.168.1." + current_octet, 80);
        scanner.on_stealthed = lan_on_stealthed;
        scanner.on_open_or_closed = lan_on_open_or_closed;
        scanner.run();

        document.getElementById("lan_results").innerHTML = "Scanning... <br />";
    }

    function lan_stop(form)
    {
        /* Set it past 255 so the scan will stop after the next one. */
        current_octet = 1000;
        document.getElementById("lan_button").disabled = false;
        document.getElementById("lan_button_stop").disabled = true;
    }

    function lan_on_stealthed()
    {
        var res_div = document.getElementById("lan_results");
        res_div.innerHTML += "192.168.1." + current_octet + ": ";
        if (this.timed_out) {
            res_div.innerHTML += "Case 2 (no response after 10 seconds). <br />";
        } else {
            res_div.innerHTML += "Case 2 (" + this.total_time + " ms). <br />";
        }

        current_octet += 1;

        if (current_octet >= 255) {
            return;
        }

        var scanner = new PortScanner("192.168.1." + current_octet, 80);
        scanner.on_stealthed = lan_on_stealthed;
        scanner.on_open_or_closed = lan_on_open_or_closed;
        scanner.run();
    }

    function lan_on_open_or_closed()
    {
        var res_div = document.getElementById("lan_results");
        res_div.innerHTML += "192.168.1." + current_octet + ": ";
        res_div.innerHTML += "Case 1 (" + this.total_time + " ms). <br />";

        current_octet += 1;

        if (current_octet >= 255) {
            return;
        }

        var scanner = new PortScanner("192.168.1." + current_octet, 80);
        scanner.on_stealthed = lan_on_stealthed;
        scanner.on_open_or_closed = lan_on_open_or_closed;
        scanner.run();
    }

</script>

<h2>Why is this a problem?</h2>

<p>
I don't think this is a very big deal, but there are a few reasons you might not
want it to be possible:
</p>

<ol>
    <li>
        If you visit an attacker's website, they can use your connection to port
scan another host on the Internet as part of an attack. Since the scan came from
your connection, you might get blamed.
    </li>
    <li>
        Websites you visit can get a little bit of information about what you
        have running on your local network, which might help if they want to
        attack you later.
    </li>
    <li>
        If the layout of your local network is unique, then it can be used as
        a kind of &quot;supercookie&quot; to help track you online. This is
        probably impractical because scanning takes a long time.
    </li>
    <li>
        This scanner works in Tails 1.3.2, and could potentially be used to
        deanonymize you. For example, if you have a printer with a web
        administration page on your local network, a website you visit could
        scan your network for printers, then profile them (e.g. by requesting
        known image URLs for common brands of printers), potentially learning
        the make, model, and firmware version of your printer.
    </li>
</ol>

<h2>Which browsers and operating systems are affected?</h2>

<p>
I've tested this and found it to work on the following systems:
</p>

<ul>
    <li>Firefox 37.0 on Arch Linux</li>
    <li>Chromium 41.0.2272.118 on Arch Linux</li>
    <li>Opera 28.0 on Arch Linux</li>
    <li>Firefox 37.0 on Windows XP</li>
    <li>Google Chrome 41.0.2272.118 on Windows XP</li>
    <li>Internet Explorer 8.0.6001.18702 on Windows XP</li>
    <li>Opera 27.0.1689.66 on Windows XP</li>
</ul>

<h2>How do I protect myself?</h2>

<p>
Unfortunately, I don't know of an easy way to stop websites from using your
browser to scan the <em>public Internet</em>, besides turning off JavaScript.
</p>

<p>
However, to stop websites from scanning your <em>local network</em>, all your
browser needs to do is deny any request from an Internet web page to a local IP
address. If you use Firefox, the NoScript extension will do this for you.
NoScript's main purpose is to disable JavaScript, but it has a lot of extra
security features, and that is one of them.
</p>

<p>
I'm not aware of any Chrome or Internet Explorer extensions that block local
requests. If you know of one, let me know and I will add it here.
</p>

