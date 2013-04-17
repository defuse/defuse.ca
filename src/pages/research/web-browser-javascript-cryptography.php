<?php
    Upvote::render_arrows(
        "webbrowsercryptography",
        "defuse_pages",
        "Web Browser Cryptography is a Good Thing",
        "Why we should support the development of browser-based crypto applications.",
        "https://defuse.ca/web-browser-javascript-cryptography.htm"
    );
?>
<h1>Web Browser Cryptography is a Good Thing</h1>

<p>
Recently I have seen a lot of security experts objecting to the use of
Javascript in the browser for cryptography. Most of these objections have been
raised in response to the release of two websites that implement their cryptography in Javascript: <a href="https://crypto.cat/">Nadim Kobeissi's Cryptocat</a> and <a
href="https://mega.co.nz/">Kim Dotcom's Mega</a>. In response to the objections,
I give a very high-level description of a code signing system that could make it safe
to do cryptography in Javascript, and argue that we should start working at
making web browser cryptography secure.
</p>

<h2>Objections to Web Browser Cryptography</h2>

<p>
The objections fall into three categories, which I will address individually.
</p>

<ol>
    <li>
        <p><strong>The security of a website that does cryptography in Javascript
        depends on the security of SSL.</strong></p>

        <p>
        This is currently the case. If an attacker can break SSL, they can
        modify the Javascript code as it's being sent to the browser and insert
        some bit of code that leaks all of the cryptographic keys. We have good
        reasons to doubt the security of SSL, too. The recent Certificate
        Authority (CA) breaches (DigiNotar, etc.), and the general idea that
        hundreds of CAs, in countries all around the world, have the power to
        intercept our SSL connections, are all good reasons to avoid
        depending on the security of SSL.
        </p>


        <p>
        It's important to note that it's SSL's PKI&mdash;the entities we've
        chosen to trust&mdash;that we have problems with, not the SSL protocol
        itself (although there have been <a
        href="https://en.wikipedia.org/wiki/Transport_Layer_Security#BEAST_attack">some
        problems</a> with that too). Therefore, to make Javascript cryptography
        secure we have to provide code integrity checking without relying on the
        SSL PKI.
        </p>


        <p>
        One approach to removing the dependency on SSL's PKI would be to develop
        a code signing infrastructure for the web. Such an infrastructure would
        not only be useful for integrity checking cryptography code, but could
        be used to mitigate XSS attacks. It doesn't need to be very complicated,
        either. A browser add-on containing the web site owner's public key
        could verify a signature attached to the code before allowing it to
        execute. The problem of distributing the web site owner's public key
        remains, but the code signing keys would be expected to have long lives,
        so users would have good reason to be very suspicious when the public
        key changes (unlike SSL). A Trust On First Use (TOFU) model, through
        SSL, is probably sufficient. Or maybe the keys can be distributed through DNSSEC.
        </p>

        <p>
        Such a system would  be very simple and easy to implement for
        different browsers. It could even be standardized and built directly into
        our browsers or the HTTP protocol. At the very least, it
        would be more efficient than moving all of the application's
        client-side logic into a browser extension that has to be maintained for
        many different browsers (which is what happened to Cryptocat in response
        to criticism).
        </p>

    </li>


    <li>
        <strong>You have to trust the machines serving the Javascript code.</strong>

        <p>
        This is also currently the case. But if we suppose the code signing
        system mentioned above exists and is used, the problem becomes,
        "<strong>You have to trust the people (and machines) who can sign the
        code.</strong>" Assuming we've acquired the web site owner's
        public key securely, then this just says we have to trust the web site
        owner to not sign malicious code and to keep their private key secret.
        Now we're no worse off than we are for normal software:
        </p>

        <ul>
            <li>
            <p>
             Windows users have to trust Microsoft to not include malicious code and to
             ensure no one else can modify the Windows source code.
            </p>
            </li>
            <li>
            <p>
            Firefox users have to trust Mozilla to not let any malicious changes
            into the Firefox source code.
            </p>
            </li>
            <li>
            <p>
            And so on...
            </p>
            </li>
        </ul><br />


    </li>

    <li>
        <strong>Javascript is missing important cryptographic primitives.</strong>


        <p>
        There's a <a href="http://www.w3.org/2012/webcrypto/">Web Cryptography
        Standard</a> on the way, but current browsers are lacking essential
        cryptographic primitives. Most notable is the lack of
        cryptographically secure random number generator and efficient (native)
        implementation of key stretching algorithms like PBKDF2. Obviously if we
        want to do cryptography in the browser, our browsers will have to start
        providing these things.
        </p>

    </li>
</ol>


<h2>Advantages of Web Browser Cryptography</h2>

        <p>
        We get some significant advantages by implementing a code signing system
        and doing cryptography in Javascript:
        </p>

        <ul>
            <li>
            <p>
                Using tools like <a href="https://getfirebug.com/">Firebug</a>,
                concerned expert users can debug and step through the <em>actual
                code</em> that is handling their private data. Someone reviewing
                a program written in C would have to take the extra step to
                verify that the program they are using was actually compiled
                from the source code they have reviewed. This won't work if the
                Javascript code has been obfuscated, but we should never trust obfuscated
                (or closed-source) cryptography code.
            </p>
            </li>
            <li>
            <p>
                Security patches can be deployed quickly. Since the browser is
                requesting a new copy of the code every time the app gets used,
                no manual user effort is required to update. This creates
                a potential vulnerability that doesn't apply to static software:
                the web site can serve malicious code to only a very small set
                of users, such as a group of  dissidents. The risk can be
                reduced by getting the code before telling the website who you
                are, getting it from a trusted third-party source
                (which is safe since it's signed), or by implementing the
                next two points.
            </p>
            </li>

            <li>
            <p>
                The code signing system could <b>require the signature of more
                than one entity</b>. For example, it could require a signature
                from the web site owner as well as signatures from any number of
                reputable security auditing companies and security researchers.
                Such a system would give a huge incentive for web site owners to
                have their code audited. It's almost inevitable that some
                careless auditors will be let into the system, as is the case
                with the SSL PKI. But the consequences are not as catastrophic.
                In the worst case, users are given a false sense of security for
                a <em>particular</em> website, instead of <em>all</em> websites
                being made vulnerable by just one bad SSL CA. The more
                signatures, the better.
            </p>
            </li>

            <li>
            <p>
                We can create a global 'watchdog' network, analogous to <a
                href="http://perspectives-project.org/">Perspectives</a> or <a
                href="https://www.eff.org/observatory">The EFF SSL
                Observatory</a>, to monitor and permanently record changes made
                to the code.
            </p>
            </li>


            <li>
            <p>
                The Web is convenient. Everyone has a web browser, so if we can
                implement secure crypto apps in our browsers, we can get
                more people to use cryptography.
            </p>
            </li>
        </ul>

<h2>Conclusion</h2>

Being able to do cryptography in the browser opens up a whole new universe of
web applications. We can create web sites that know nothing about their users,
and we can get people to use cryptography without forcing them to download
strange and barely-usable software. The advantages are too great to ignore.
Therefore, I suggest that instead of bashing those who try to write cryptography
applications for the web browser, we should work together to make web browser
cryptography secure.
