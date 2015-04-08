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
        <input type="button" value="Scan 192.168.1.* port 80" onclick="lan_scan(this.form);" />
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
                    <input type="button" value="Scan" onclick="custom_scan(this.form);" />
                </td>
            </tr>
        </table>
    </form>
    <div id="custom_result"></div>
</div>

<div id="testdiv" style="visibility: hidden"></div>

<script>
    var start_time;
    var next_octet;

    function custom_scan(form)
    {
        var ip = form.custom_ipaddr.value();
        var port = form.custom_port.value();
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

        document.getElementById("custom_result").innerHTML = "Scanning...";

        start_time = (new Date()).getTime();
        document.getElementById("testdiv").innerHTML = '<img src="http://' + ip + ':' + port + '" alt="" onerror="custom_cont();" />';
    }

    function custom_cont()
    {
        var end_time = (new Date()).getTime();
        var time = end_time - start_time;

        if (time < 1500) {
            document.getElementById("custom_result").innerHTML = "Case 1 (" + time + " ms)."
        } else {
            document.getElementById("custom_result").innerHTML = "Case 2 (" + time + " ms).";
        }
    }

    function lan_scan(form)
    {
        next_octet = 2;
        var ip = "192.168.1." + next_octet;
        start_time = (new Date()).getTime();
        document.getElementById("lan_results").innerHTML = "Scanning... <br />";
        document.getElementById("testdiv").innerHTML = '<img src="http://' + ip + '" alt="" onerror="lan_cont();" />';
    }

    function lan_cont()
    {
        var time = (new Date()).getTime() - start_time;
        var res_div = document.getElementById("lan_results");
        res_div.innerHTML += "192.168.1." + next_octet + ": ";
        if (time < 1500) {
            res_div.innerHTML += "Case 1";
        } else {
            res_div.innerHTML += "Case 2";
        }
        res_div.innerHTML += " (" + time + " ms) <br />";

        next_octet += 1;
        if (next_octet < 255) {
            start_time = (new Date()).getTime();
            var ip = "192.168.1." + next_octet;
            document.getElementById("testdiv").innerHTML = '<img src="http://' + ip + '" alt="" onerror="lan_cont();" />';
        }
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
        scan another on the Internet as part of an attack. Since the scan came
        from your connection, you might get blamed.
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
        your network for printers, then profile them (e.g. by requesting known
        image URLs for common brands of printers), potentially learning the
        make, model, and firmware version of your printer.
    </li>
</ol>

<h2>Which browsers and operating systems are vulnerable?</h2>

<p>
I've tested this and found it to work on the following systems:
</p>

<ul>
    <li>Firefox 37.0 on Arch Linux</li>
    <li>Chromium 41.0.2272.118 on Arch Linux</li>
</ul>

<p>
I've found that it does not work on:
</p>

<ul>
    <li>Firefox 37.0 with NoScript on Arch Linux</li>
</ul>

<h2>How do I protect myself?</h2>

<p>
Unfortunately, I don't know of an easy way to stop a website from using your
browser to scan the <em>Internet</em>.
</p>

<p>
However, there is a simple way to stop a website from scanning your <em>local
network</em>. All your browser has to do is deny requests from Internet pages to
your local network. If you use Firefox, the NoScript extension will do this for
you (its main feature is to disable JavaScript, but it has a lot of extra
security features).
</p>

</ul>

