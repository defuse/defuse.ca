<h1>Negative Feedback for Insecure Connections</h1>

<p>
Web browsers inform the user that they have a secure connection to a website
with a lock icon:
</p>

<center>
    <img
        style="box-shadow: 5px 5px 3px #888888; margin-bottom: 20px;"
        src="/images/ssl-regular.png"
        alt="Web Site with SSL but no EV"
        >
</center>

<p>
If the web site is willing to spend a little more money, they can get an
"Extended Validation" certificate, which will make the browser show a green bar:
</p>

<center>
    <img
        style="box-shadow: 5px 5px 3px #888888; margin-bottom: 20px;"
        src="/images/ssl-ev.png"
        alt="Web Site with EV SSL"
        >
</center>

<p>
When the connection is <em>not</em> secure, the browser gives no special
indication. Unless the user looks for the lock and sees that it isn't there,
they won't know that the page is insecure:
</p>

<center>
    <img
        style="box-shadow: 5px 5px 3px #888888; margin-bottom: 20px;"
        src="/images/ssl-none.png"
        alt="Web Site with No SSL"
        >
</center>

<p>
Even though insecure connections are still extremely common, there's no reason
it has to be this way. The browser <em>could</em> warn the user by displaying
a broken lock, like this:
</p>

<center>
    <img
        style="box-shadow: 5px 5px 3px #888888; margin-bottom: 20px;"
        src="/images/mockup-1.png"
        alt="Web Site with No SSL"
        >
</center>

We think browsers <em>should</em> do this.
