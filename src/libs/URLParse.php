<?php
/*==============================================================================

        Defuse Security's Secure & Lightweight CMS in PHP for Linux.

                      PUBLIC DOMAIN CONTRIBUTION NOTICE                             
   This work has been explicitly placed into the Public Domain for the
    benefit of anyone who may find it useful for any purpose whatsoever.
    
==============================================================================*/

/*
 * The purpose of this class is to process the current request URL to 
 * determine which page is to be displayed to the user, or to which URL
 * the user should be redirected. Once the user has been redirected to
 * the correct URL, and the desired page is determined, the page contents
 * can be loaded from a file into a dynamically generated web page.
 *
 * The URL parsing is split into four processes:
 * 1. First, the hostname (domain name) the request was made to is verified 
 *    against a list of "accepted hosts." If the hostname doesn't match any of
 *    these accepted hosts, the user is redirected to the same URL on the
 *    "master host." The accepted hosts and master host variables can be set
 *     by modifying the $ACCEPTED_HOSTS and $MASTER_HOST variables respectively.
 * 2. Second, an HTTPS connection is enforced if $FORCE_HTTPS is set to true.
 *    If $FORCE_HTTPS === true and the current connection is not secure, the
 *    user is redirected to a secure (https) URL.
 * 3. The desired page is determined from the URL (see below). If this page
 *    is really an alias for another page, the user is redirected to the proper
 *    page.
 * 4. If the user did not request the page using the cannonical filename,
 *    they are redirected to the cannonical URL for the page (see below).
 *
 * How the desired page is determined from the URL:
 *
 * Every page has a name, and there are two valid URLs for each page name.
 * For a page named "foobar", the following are valid URLs for the page:
 *      1. http://example.com/foobar
 *      2. http://example.com/foobar.htm
 * (2) is the cannonical URL for the page. So if the user were to type (1) into
 * their browser, they would be redirected to (2). The URL without the .htm
 * extension is recognized as a convienience so the URL can be spoken without
 * explicitly pronouncing the "dot h-t-m."
 *
 * Names can also contain forward slashes, allowing virtual directories to be
 * created. For example, the page name "foo/bar" is valid, with the following
 * URLs:
 *      1. http://example.com/foo/bar
 *      2. http://example.com/foo/bar.htm
 * With (2) being the cannonical form.
 * There is a special case of these names where no ".htm" extension is allowed.
 * For example, the name "" (meaning the homepage) is accessible though:
 *          http://example.com/
 * but NOT through:
 *          http://example.com/.htm
 * The same applies to names ending in "/", e.g. "foo/" is accessible through:
 *          http://example.com/foo/
 * but NOT through:
 *          http://example.com/foo/.htm
 * Note that a page named "foo/" and "foo" can exist simultaneously, but since
 * it is common to ommit the trailing "/" when typing the URL, this practice
 * is strongly discouraged. If the name "foo/" exists and the user omits the 
 * trailing "/", they will be redirected to the "foo/" URL. But if "foo/" and
 * "foo" both exist, they will be redirected to "foo.htm".
 */

// Keys used for definining page data arrays
define('P_FILE', 0); // File content (suffix to $ROOT_FOLDER)
define('P_TITL', 1); // <title>text</title>
define('P_METD', 2); // META tag description
define('P_METK', 3); // META tag keywords
define('P_RDIR', 4); // Redirect URL (has precidence)

class URLParse
{
    private static $ROOT_FOLDER = "pages/";
    private static $MASTER_HOST = "defuse.ca";
    private static $ACCEPTED_HOSTS = array(
                                            "localhost",
                                            "192.168.1.102",
                                            "defuse.h.defuse.ca",
                                            "defuse",
                                            );
    private static $FORCE_HTTPS = true;
    private static $DEFAULT_TITLE = "Defuse Security Research and Development";
    private static $DEFAULT_META_DESC = "Defuse Security. Home of PIE Bin, TRENT, and more...";
    private static $DEFAULT_META_KEYWORDS = "defuse security, encryption, privacy, programming, code, research";

    private static $PAGE_INFO = array(
            "" => array(
                P_FILE => "home.html",
                ),
            // Handles /index and /index.htm
            "index" =>      array(
                    P_RDIR => "",
                    ),
            "index.html" =>  array(
                P_RDIR => "",
                ),
            "index.php" =>  array(
                P_RDIR => "",
                ),
            "key" => array(
                P_RDIR => "contact"
                ),
            "about" =>      array(
                P_FILE => "about.html",
                P_METD => "About Defuse Security."
                ),
            "all-pages" =>      array(
                P_FILE => "all-pages.php",
                P_METD => "A list of all pages on defuse.ca, sorted by popularity."
                ),
            "library" => array(
                P_FILE => "library.php",
                P_METD => "The books I have read and am reading.",
                P_METK => "library, book collection, book review, security books",
                ),
            "honestyware" => array(
                P_FILE => "honestyware.php",
                P_TITL => "Honestyware - The right way to sell software.",
                P_METK => "honestyware, free software, trust user",
                P_METD => "Honestyware is a revolutionary way to sell software that embraces piracy."
                ),
            "purchase" => array(
                P_FILE => "purchase.php",
                P_TITL => "Purchase Confirmation",
                P_METK => "purchase complete",
                P_METD => "The page you see when you buy something from Defuse."
                ),
            "donated" => array(
                P_FILE => "donated.html"
                ),
            "resume" => array(
                P_FILE => "resume.html",
                P_TITL => "My Resume - Defuse Security",
                P_METD => "Defuse Security Resume",
                P_METK => "defuse cyber security, resume"
                ),          
            "vimrc" => array(     
                P_FILE => "vimrc.php",
                P_TITL => "My .vimrc - Defuse Security",
                P_METD => "My Vim configuration file",
                P_METK => "vim, vimrc, vim configuration"
                ),          
            "the-universe-is-made-of-cheese" => array(
                P_FILE => "misc/the-universe-is-made-of-cheese.php",
                P_TITL => "The Universe is Made of Cheese - A Formal Proof",
                P_METD => "A logical proof that the universe consists entirely of cheese.",
                P_METK => "cheese paradox, universe made of cheese, logical proof"
            ),
            "the-meaning-of-if" => array(
                P_FILE => "misc/the-meaning-of-if.php",
                P_TITL => "The meaning of \"IF\" -- An Introduction to Formal Logic",
                P_METD => "What the word \"If\" means in formal logic",
                P_METK => "formal logic introduction, meaning of if, what if means"
            ),
            "blowfish" => array(      
                P_FILE => "projects/blowfish.html",
                P_TITL => "Blowfish C# and C++ Source Code - Defuse Security",
                P_METD => "C# and C++ implementation of the BLOWFISH block cipher."
                ),
            "secure-php-encryption" => array(
                P_FILE => "projects/secure-php-encryption.php",
                P_TITL => "Encrypting Data in PHP (HowTo) - Defuse Security",
                P_METD => "Source code for encrypting data in PHP with mcrypt. The right way to do it.",
                P_METK => "php encrypiton, php aes, encryption in php, php decryption"
                ),
            "email-spoofing-in-ruby" => array(
                P_FILE => "projects/email-spoofing-in-ruby.php",
                P_TITL => "Spoofing SMTP email with a Ruby script - Defuse Security",
                P_METD => "Example script for communicating with an SMTP server in Ruby to send spoofed email.",
                P_METK => "email spoofing, smtp spoofing, ruby email, ruby smtp"
                ),
            "online-free-computer-science-education" => array(
                P_FILE => "projects/online-free-computer-science-education.php",
                P_TITL => "Computer Science Video Lecture Collection",
                P_METD => "A collection of computer science related videos from various universities.",
                P_METK => "computer science lecture, programming lecture, education, free education"
                ),
            "syntax-highlighting-in-php-with-vim" => array(      
                P_FILE => "projects/syntax-highlighting-in-php-with-vim.php",
                P_TITL => "Scripting Vim's HTML syntax highlighting in PHP",
                P_METD => "Using the Vim editor to highlight code syntax from PHP"
                ),
            "inn-private-newsgroup-server-setup" => array(
                P_FILE => "projects/inn-private-newsgroup-server-setup.php",
                P_TITL => "Configuring a Private (Local) News Server with INN2",
                P_METD => "How to configure a private local newsgroup server (non-UseNet) on Debian",
                P_METK => "inn2 configuration, news server configuration, local newsgroups, cecil-id, private newsgroups",
                ),
            "sockstress" => array(
                P_FILE => "software/sockstress.php",
                P_TITL => "Sockstress Denial of Service Tool & Source Code - Defuse Security",
                P_METD => "A C implementation of the sockstress attack from 2008.",
                P_METK => "sockstress, source code, denial of service, proof of concept, dos, ddos",
                ),          
            "php-hash-cracker" => array(
                P_FILE => "software/php-hash-cracker.php",
                P_TITL => "Salted Hash Cracking PHP Script - Defuse Security",
                P_METD => "Dictionary hash cracking PHP scripts (supports LOTS of hash types!!)",
                P_METK => "hash cracking, dictionary attack, php hash cracking script",
                ),          
            "wordlists" =>  array(      
                P_FILE => "projects/wordlists.html",
                P_TITL => "Password Cracking Wordlists - Defuse Security",
                P_METD => "The best password cracking wordlists and dictionaries on the internet.",
                P_METK => "password cracking, word list, wordlist, dictionary, md5, hash cracking",
                ),          
            "projects" =>   array(      
                P_FILE => "projects/projects.html",
                P_TITL => "Defuse Security's Projects",
                P_METD => "All of Defuse Security's projects.",
                P_METK => "security, computer security, projects",
                ),          
            "php-hit-counter" => array( 
                P_FILE => "projects/php-hit-counter.html",
                P_TITL => "PHP & MySQL Unique Hit Count Tracker - Defuse Security",
                P_METD => "A unique hit counter for PHP. Tracks unique hits without storing the IP address.",
                P_METK => "hit counter, php, secure, private, anonymous, unique hits, track",
                ),
            "php-pbkdf2" => array(
                P_FILE => "projects/php-pbkdf2.php",
                P_TITL => "PBKDF2 Password Hashing for PHP",
                P_METD => "Standards compliant PBKDF2 implementation for PHP.",
                P_METK => "pbkdf2 for php, key derivation, password hashing",
                ),
            "generating-random-passwords" => array(
                P_FILE => "projects/generating-random-passwords.php",
                P_TITL => "Generating Unbiased Random Passwords in PHP",
                P_METD => "Code for generating SECURE random passwords in PHP.",
                P_METK => "generating passwords with php, generating passwords, php, random passwords",
                ),
            "helloworld-cms" => array(
                P_FILE => "software/helloworld-cms.html",
                P_TITL => "Secure and Light CMS for PHP - Defuse Security",
                P_METD => "A lightweight, ultra-secure CMS for PHP",
                P_METK => "secure cms, php cms, light cms, small cms, tiny cms, cms",
                ),
            "passwordbolt" => array(
                P_FILE => "software/passwordbolt.html",
                P_TITL => "Password Bolt Password Manager - Defuse Security",
                P_METD => "Password bolt, an extremely secure web based password manager.",
                P_METK => "passwordbolt, password manager, password, online password manager, web password manager, open source",
                ),
            "bitcannon" =>  array(
                P_FILE => "software/bitcannon.html",
                P_TITL => "BitCannon - The cross-platform, fast, secure, encrypted internet file transfer program for Windows, Macintosh, and Linux - Defuse Security",
                P_METD => "Easily and quickly transfer large files over a direct encrypted connection.",
                P_METK => "encrypted file transfer, fast file transfer, secure file transfer, windows, mac, linux",
                ),
            "bitcannon-cryptography" => array(
                P_FILE => "software/bitcannoncrypto.html",
                P_TITL => "BitCannon - Cryptographic Protocol Description - Defuse Security",
                P_METD => "Detailed description of the encryption technology behind the BitCannon file transfer program.",
                P_METK => "secure, encryption, cryptography, file transfer",
                ),
            "backup-verify-script" =>  array(
                P_FILE => "software/backup-verify-script.php",
                P_TITL => "Script for Comparing Folders and Validating Backups",
                P_METD => "A command-line script for verifying backups by comparing two folders in Linux",
                P_METK => "backup validate, backup verify, compare folders, linux, ruby",
                ),
            "ip" => array(
                P_FILE => "services/ip.php",
                P_TITL => "Your IP Address",
                P_METD => "Your IP Address!",
                P_METK => "online IP address, what is my ip, ip address, ssl ip address",
                ),
            "statzzz" => array(
                P_FILE => "stats.php",
                P_TITL => "Server Statistics",
                P_METD => "Server Statistics",
                P_METK => "server statistics",
                ),
            "softwaredevelopment" => array(
                P_FILE => "services/softwaredevelopment.html",
                P_TITL => "Custom Software Development - Defuse Security",
                P_METD => "Custom secure software development to suit your needs.",
                P_METK => "software development, custom software, programming, security",
                ),
            "ip-to-location" => array(
                P_FILE => "services/ip-to-location.php",
                P_TITL => "IP Address To Location (Country, City) - Defuse Security",
                P_METD => "Find out where an IP address is located (FREE, UNLIMITED)",
                P_METK => "ip2location, ip address, locate ip, ip to location, where is ip",
                ),
            "online-x86-assembler" => array(
                P_FILE => "services/online-x86-assembler.php",
                P_TITL => "Online x86 and x64 Intel Instruction Assembler",
                P_METD => "Easily find out which bytes your x86 ASM instructions assemble to.",
                P_METK => "assembler, opcode finder, opcode lookup, online assembler, x86, intel",
                ),
            "web-server-scan" => array(
                P_FILE => "services/scan.php",
                P_TITL => "Web Server Security Scan - Defuse Security",
                P_METD => "Test your web server for common configuration problems such as open relay, zone transfer, and much more.",
                P_METK => "security scan, web server scan, zone transfer test, open relay test",
                ),
            "pdftribute" => array(
                P_FILE => "pdftribute.php",
                P_TITL => "#pdftribute",
                P_METD => "#pdftribute",
                P_METK => "#pdftribute",
                ),
            "webdevelopment" => array(
                P_FILE => "services/webdevelopment.html",
                P_TITL => "Custom Web Design and Development - Defuse Security",
                P_METD => "Custom web software development.",
                ),
            "services" => array(
                P_FILE => "services/services.html",
                P_TITL => "Defuse Security's Services",
                P_METD => "Defuse Security's Services.",
                ),
            "peerreview" => array(
                P_FILE => "services/peerreview.html",
                P_TITL => "Peer Review and Security Testing Service - Defuse Security",
                P_METD => "Free peer review and security testing service.",
                P_METK => "security, peer review, testing, software security",
                ),
            "trent" => array(
                P_RDIR => "trustedthirdparty",
                ),
            "trustedthirdparty" => array(
                P_FILE => "services/trustedthirdparty.php",
                P_TITL => "TRENT - FREE Third party Drawing Service - Defuse Security",
                P_METD => "TRENT, the trusted random number generator for contests and drawings.",
                P_METK => "contest drawing, third party, trusted, lottory, trent, random number generator",
                ),
            "trent-source" => array(
                P_FILE => "services/trent-source.php",
                P_TITL => "TRENT Source Code - Defuse Security",
                P_METD => "TRENT, the trusted random number generator for contests and drawings.",
                P_METK => "contest drawing, third party, trusted, lottory, trent, random number generator",
                ),
            "contact" => array(
                P_FILE => "contact.html",
                P_TITL => "Defuse Security's Contact Information",
                P_METD => "Defuse Security's contact informaion.",
                ),
            "website-key-stretching-feasibility" => array(
                P_FILE => "research/website-key-stretching-feasibility.php",
                P_TITL => "Feasibility of Using Key Stretching in Web Applications",
                P_METD => "How much does using key stretching to hash passwords cost? Is it reasonable?",
                P_METK => "web application, key stretching feasibility, password hashing, cpu intensive hash function",
                ),
            "truecrypt-plausible-deniability-useless-by-game-theory" => array(
                P_FILE => "research/truecrypt-plausible-deniability-useless-by-game-theory.php",
                P_TITL => "TrueCrypt's Plausible Deniability (Hidden Volumes) is Theoretically Useless",
                P_METD => "How game theory shows that TrueCrypt's hidden volume feature is provably useless in some scenarios.",
                P_METK => "game theory, truecrypt plausible deniability",
                ),
            "web-browser-javascript-cryptography" => array(
                P_FILE => "research/web-browser-javascript-cryptography.php",
                P_TITL => "Web Browser Cryptography is a Good Thing",
                P_METD => "Arguments for and against doing cryptography in the browser.",
                P_METK => "javascript cryptography, web browser cryptography, browser cryptography,
                encryption",
                ),
            "grc-passwords-not-secure" => array(
                P_FILE => "research/grc-passwords-not-secure.php",
                P_TITL => "GRC's Perfect Password Generator is Not Cryptographically Secure",
                P_METD => "Why GRC's Perfect Password Generator is not a CSPRNG",
                P_METK => "grc perfect passwords, security of grc perfect passwords, csprng, cryptographically secure, state compromise extension",
                ),
            "password-reset-key-escrow" => array(
                P_FILE => "research/password-reset-key-escrow.php",
                P_TITL => "Modernizing Password Reset & Key Escrow",
                P_METD => "Why our password reset methods suck and how we can make them better.",
                P_METK => "password reset security, key escrow, password recovery",
                ),
            "theoretical-attack-on-bitcoin" => array(
                P_FILE => "research/theoretical-attack-on-bitcoin.html",
                P_TITL => "Economic DDoS - A Theoretical Attack on Bitcoin - Defuse Security",
                P_METD => "State-sponsored attacks could secretly destroy the bitcoin economy.",
                ),
            "blockwise-factoring" => array(
                P_FILE => "research/blockwise-factoring.php",
                P_TITL => "Blockwise Factoring - An efficient and simple method for factoring small integers. ",
                P_METD => "Blockwise Factoring is a method for factoring the product of two primes that uses only addition, subtraction, and comparison.",
                ),
            "race-conditions-in-web-applications" => array(
                P_FILE => "research/race-conditions-in-web-applications.php",
                P_TITL => "Practical Race Condition (TOCTTOU) Vulnerabilities in Web Applications - Defuse Security",
                P_METD => "Query-level race conditions can lead to serious but hard to find vulnerabilities in web applications.",
                ),
            "choosing-good-passwords-longer-is-better" => array(
                P_FILE => "research/choosing-good-passwords-longer-is-better.html",
                P_TITL => "How to Pick a Good Password - Longer is Better - Defuse Security",
                P_METD => "The length of passwords is far more important than their character set or randomness.",
                ),
            "bitcoin-pool-ddos" => array(
                P_FILE => "research/bitcoin-pool-ddos.html",
                P_TITL => "BitCoin Centralization - DDoS Attacks on Pools & Mt. Gox Hacked - Defuse Security",
                P_METD => "Centralization is harmful to the BitCoin network and community.",
                P_METK => "bitcoin, ddos, denial of service, pool, mining, mtgox, centralization, hacked",
                ),
            "ssl-fundamental-flaw-fix" => array(
                P_FILE => "research/ssl-fundamental-flaw-fix.html",
                P_TITL => "Fixing the Flaw in the SSL Certificate Authority Architecture - Defuse Security",
                P_METD => "Dangers of the Internet, an explanation of the dangers you face online.",
                ),
            "internetdangers" => array(
                P_FILE => "research/internetdangers.html",
                P_TITL => "Dangers of the Internet - Defuse Security",
                P_METD => "Dangers of the Internet, an explanation of the dangers you face online.",
                ),
            "softwaresecurity" => array(
                P_FILE => "research/softwaresecurity.html",
                P_TITL => "Software Security - Bypassing KeyScrambler and Avira - Defuse Security",
                P_METD => "The fundamental flaw in the software security model. Bypassing KeyScrambler and Avira",
                P_METK => "software security, antivirus, keyscrambler, antivir",
                ),
            "web-application-security" => array(
                P_FILE => "research/web-application-security.html",
                P_TITL => "Web Application Security - Defuse Security",
                P_METD => "Why are websites so insecure? What design patterns will help solve these problems?",
                P_METK => "web application security, cross site scripting, sql injection, remote code execution, php, asp, scripting",
                ),
            "research" => array(
                P_FILE => "research/research.html",
                P_TITL => "Defuse Security's Research",
                P_METD => "Research projects by Defuse Security",
                ),
            "cryptographyunderattack" => array(
                P_FILE => "research/cryptographyunderattack.html",
                P_TITL => "Cryptography Under Attack - Defuse Security",
                P_METD => "Cryptography under attack essay.",
                ),
            "passwordinsecurity" => array(
                P_FILE => "research/passwordinsecurity.html",
                P_TITL => "Are Passwords Secure? - Defuse Security",
                P_METD => "Finding out if passwords are right way to be authenticating users.",
                ),
            "keyboarddefect" => array(
                P_RDIR => "asuskeyboarddefect",
                ),
            "asuskeyboarddefect" => array(
                P_FILE => "research/asuskeyboarddefect.html",
                P_TITL => "ASUS G50 G51 Keyboard Problem: Backspace, P, and 1 keys don't work. - Defuse Security",
                P_METD => "Solution to the keyboard problem for the ASUS G50, G51, and G51VX series laptops.",
                P_METK => "asus keyboard, g series, g51, g50, g51vx, backspace, p, q, keys, broken",
                ),
            "passwords" => array(
                P_RDIR => "passgen",
                ),
            "password" => array(
                P_RDIR => "passgen",
                ),
            "pass" => array(
                P_RDIR => "passgen",
                ),
            "passgen" => array(
                P_FILE => "software/passgen.html",
                P_TITL => "Secure Windows & Linux Password Generator - Defuse Security",
                P_METD => "A secure random password generator for Windows,  Linux and Macintosh. Generates ASCII and HEX.",
                P_METK => "password generator, secure, encryption, windows, linux, macintosh",
                ),
            "software" => array(
                P_FILE => "software/software.html",
                P_TITL => "Defuse Security's Software",
                P_METD => "Software created by Defuse Security",
                ),
            "winrrng" => array(
                P_FILE => "software/winrrng.html",
                P_TITL => "Real Random Number Generator for Windows - Defuse Security",
                P_METD => "A real random number generator for Windows",
                ),
            "textractor" => array(
                P_FILE => "software/textractor.html",
                P_TITL => "Textractor - A tool for extracting words from large binary files - Defuse Security",
                P_METD => "A Windows and Linux tool for extracting words or sequences of alphabetical characters from large binary files",
                P_METK => "extract, words, wordlist creator, extract words",
                ),
            "dotnetbenchmark" => array(
                P_FILE => "software/dotnetbenchmark.html",
                P_TITL => "Benchmark Tool for the .NET Framework - Defuse Security",
                P_METD => "A benchmark tool for the .NET Framework",
                P_METK => "dot net framework, benchmark",
                ),
            "eotp" => array(
                P_FILE => "research/eotp.html",
                P_TITL => "Encrypting One Time Passwords System - Defuse Security",
                P_METD => "A One Time Password protocol that can be used with encryption.",
                P_METK => "encrypting one time passwords, static key, one time password",
                ),
            "cbcmodeiv" => array(
                P_FILE => "research/cbcmodeiv.html",
                P_TITL => "Should CBC Mode Initialization Vector Be Secret - Defuse Security",
                P_METD => "Should the initialization vector used for CBC mode be kept secret?",
                P_METK => "cbc mode, encryption, initialization vector, iv, secret, secure",
                ),
            "passwordrestrictions" => array(
                P_FILE => "research/passwordrestrictions.html",
                P_TITL => "INSANE Password Restrictions",
                P_METD => "Why websites that enforce maximum password length and character set may not be safe to use",
                P_METK => "password restrictions, hashing, plaintext passwords, maximum length, special symbols",
                ),
            "password-policy-statistics" => array(
                P_FILE => "research/password-policy-statistics.php",
                P_TITL => "Password Policy Statistics - Defuse Security",
                P_METD => "Password length and character restriction statistics. Top 100 sites.",
                P_METK => "password, statistics, restrictions, maximum length, symbols, plain text, hashing, salt",
                ),
            "pphos" => array(
                P_RDIR => "password-policy-hall-of-shame",
                ),
            "password-policy-hall-of-shame" => array(
                P_FILE => "research/hallofshame.php",
                P_TITL => "Password Policy Hall of SHAME - Defuse Security",
                P_METD => "List of websites and services that impose password restrictions and may be storing passwords in plaintext.",
                P_METK => "hall of shame, password hall of shame, plaintext, password restrictions, maximum password length, restriction, insecure",
                ),
            "gpucrack" => array(
                P_FILE => "projects/gpucrack.html",
                P_TITL => "CUDA Salted MD5 GPU Cracker Source Code - Defuse Security",
                P_METD => "Source code for a salted MD5 hash cracker using nvidia graphics cards (CUDA).",
                P_METK => "salted md5, hash cracking, gpu, graphics cards, nvidia, cuda",
                ),
            "force-print-background" => array(
                P_FILE => "projects/force-print-background.php",
                P_TITL => "HTML/CSS Force Printing of Background Color - Defuse Security",
                P_METD => "HTML/CSS Hack to get browsers to print background colors.",
                P_METK => "print background color, html, css, force print background color, print stylesheet",
                ),
            "waterfall" => array(
                P_FILE => "projects/waterfall.html",
                P_TITL => "Waterfall Password Cracking Algorithm - Defuse Security",
                P_METD => "The Waterfall password cracking algorithm with implementation and source code.",
                P_METK => "password cracking, memorable passwords, keyspace reduction, fast dictionary attack",
                ),
            "csharpthreadlibrary" => array(
                P_FILE => "projects/csharpthreadlibrary.html",
                P_TITL => "PolyThread - C# Thread Library - Defuse Security",
                P_METD => "Source code for a job-based multi-threading library for the .NET framework. Written in C#.",
                P_METK => "multi-threading, threads, c#, library, job based, parallel",
                ),
            "simppsk" => array(
                P_FILE => "projects/simppsk.html",
                P_TITL => "SimpPSK - A simple pre-shared key authentication and session key exchange implementation - Defuse Security",
                P_METK => "pre-shared key, key exchange, library, c++, c plus plus",
                ),
            "checksums" => array(
                P_FILE => "services/checksums.php",
                P_TITL => "Online Text and File Hash Calculator - MD5, SHA1, SHA256, SHA512, WHIRLPOOL Hash Calculator - Defuse Security",
                P_METD => "Online Hash Tool. Calculate hash of file or text. MD5, SHA1, SHA256, SHA512 and more...",
                P_METK => "file hasher, online, hash, md5, sha256, sha1, text hash, checksum",
                ),
            "html-sanitize" => array(
                P_FILE => "services/html-sanitize.php",
                P_TITL => "Online HTML Sanitizer Tool - htmlspecialchars - Defuse Security",
                P_METD => "Convert text containing special characters into proper HTML.",
                P_METK => "html sanitizer, htmlspecialchars, htmlencode",
                ),
            "big-number-calculator" => array(
                P_FILE => "services/big-number-calculator.php",
                P_TITL => "Online Big Number Calculator",
                P_METD => "Calculate enormous mathematical equations from within your browser.",
                P_METK => "big number calculator, online calculator, bigint, galactic calculator",
                ),
            "pdfcleaner" => array(
                P_FILE => "services/pdfcleaner.php",
                P_TITL => "PDF Exploit Sanitizer and Cleaner - Remove exploits from PDF files - Defuse Security",
                P_METD => "PDF Exploit Remover. Remove ANY exploit from a PDF files. Even unknown exploits.",
                P_METK => "pdf exploit, exploit remover, unknown exploit, sanitizer",
                ),
            "tics" => array(
                P_FILE => "research/tics.html",
                P_TITL => "TICS - Time Intensive Computer Solvable CAPTCHA Alternative - Defuse Security",
                ),
            "browserui" => array(
                P_FILE => "research/browserui.html",
                P_TITL => "Redesigning the Web Browser GUI - Defuse Security",
                P_METD => "The web browser needs a redesign..",
                ),
            "flashsupercomputer" => array(
                P_FILE => "research/flashsupercomputer.html",
                P_TITL => "Building a Distributed Supercomputer with Flash - Defuse Security",
                P_METD => "Easy, super-scalable supercomputing.",
                ),
            "stopspying" => array(
                P_FILE => "research/stopspying.php",
                P_TITL => "Stop Spying! Bills C-50, C-51, C-52. Warrantless Wiretaps in Canada.",
                P_METD => "canada, warrant-less wiretap, isp, c51, c52, c50, spying, law, bill, internet privacy",
                ),
            "filesystem-events-ntfs-permissions" => array(
                P_FILE => "research/filesystemevents.html",
                P_TITL => "File System Events Disclose NTFS Protected Folder Contents - Defuse Security",
                P_METD => "Obtain list of files in folder protected with NTFS permissions via filesystem events",
                ),
            "pastebin" => array(
                P_FILE => "services/pastebin.html",
                P_TITL => "Encrypted Pastebin - Keep your data private and secure! - Defuse Security",
                P_METD => "An Encrypted, Anonymous, Secure, and PRIVATE Pastebin. Send large amounts of text to your friends without it being leaked onto the internet!",
                P_METK => "private pastebin, encrypted pastebin, secure pastebin, anonymous pastebin, privacy",
                ),
            "passwordblocks" => array(
                P_FILE => "services/passwordblocks.php",
                P_TITL => "Password Building Blocks - The HUMAN Password Generator",
                P_METD => "Design your own highly memorable high security password.",
                P_METK => "password generator, easy to remember password, secure password, password building blocks, human password",
                ),
            "onedetection" => array(
                P_FILE => "research/onedetection.html",
                P_TITL => "The PUP Confusion Antivirus Detection Evasion Technique - Defuse Security",
                P_METD => "The PUP Confusion Antivirus Detection Evasion Technique. Multiple detections per file.",
                P_METK => "antivirus, single detection, only one detection, can't detect more than one, multiple virus, two viruses in one file",
                ),
            "scratch" => array(
                P_FILE => "scratch.html",
                P_TITL => "",
                P_METD => "",
                ),
            "r&d/bitcannon-experimental" => array(
                P_FILE => "rd/bitcannon-exp.html",
                P_TITL => "",
                P_METD => "",
                ),
            "privacy-policy" => array(
                P_FILE => "privacy.html",
                P_TITL => "",
                P_METD => "",
                ),
            );

    // Page to be displayed for invalid URLs
    private static $FILE_NOT_FOUND = array(
                        P_FILE => "404.php",
                        P_TITL => "File Not Found",
                        );
                        

    private static $to_show;

    public static function ProcessURL()
    {
        // Check the host the request was made to, and redirect if necessary.
        self::checkHost(); 
        // Check the HTTPS status, redirect if necessary.
        self::checkHTTPS();

        $page_info_key = self::getPageArrayKey();
        if($page_info_key === false)
        {
            self::send404Headers();
            self::$to_show = self::$FILE_NOT_FOUND;
            return "404";
        }
        else
        {
            $page_array = self::$PAGE_INFO[$page_info_key];
            self::checkRedirectRequest($page_array);
            self::ensureHTMOrSlashExtension($page_array, $page_info_key);
            self::$to_show = $page_array;
            return $page_info_key;
        }
    }

    public static function getPageTitle($name)
    {
        if(array_key_exists($name, self::$PAGE_INFO))
        {
            $page_array = self::$PAGE_INFO[$name];
            if(array_key_exists(P_TITL, $page_array))
                return $page_array[P_TITL];
            else
                return self::$DEFAULT_TITLE;
        }
        else
            return self::$DEFAULT_TITLE;
    }

    public static function getPageMetaDescription($name)
    {
        if(array_key_exists($name, self::$PAGE_INFO))
        {
            $page_array = self::$PAGE_INFO[$name];
            if(array_key_exists(P_METD, $page_array))
                return $page_array[P_METD];
            else
                return self::$DEFAULT_META_DESC;
        }
        else
            return self::$DEFAULT_META_DESC;
    }

    public static function getPageMetaKeywords($name)
    {
        if(array_key_exists($name, self::$PAGE_INFO))
        {
            $page_array = self::$PAGE_INFO[$name];
            if(array_key_exists(P_METK, $page_array))
                return $page_array[P_METK];
            else
                return self::$DEFAULT_META_KEYWORDS;
        }
        else
            return self::$DEFAULT_META_KEYWORDS;
    }

    // Includes the page contents (ProcessURL must be called first). 
    // Returns the name of the included page.
    public static function IncludePageContents()
    {
        $included = "";
        if(isset(self::$to_show) && array_key_exists(P_FILE, self::$to_show) && 
                        file_exists(self::$ROOT_FOLDER . self::$to_show[P_FILE]))
        {
            $included = self::$ROOT_FOLDER . self::$to_show[P_FILE]; 
            include($included);
        }
        else
        {
            $included = self::$ROOT_FOLDER . self::$FILE_NOT_FOUND[P_FILE]; 
            include($included); 
        }
        return $included;
    }

    // Make sure the request is coming to one of the accepted hosts, and if not,
    // redirect to the master host.
    private static function checkHost()
    {
        $http_host = $_SERVER['HTTP_HOST'];
        if($http_host != self::$MASTER_HOST && !in_array($http_host, self::$ACCEPTED_HOSTS))
        {
            // We anticipate the HTTPS requirement here so that we can avoid a 
            // second redirect from checkHTTPS()
            // Use https:// protocol if:
            //          1. $FORCE_HTTPS is true
            //      or, 2. HTTPS is already in use.
            if(self::$FORCE_HTTPS || (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off'))
                $protocol = "https://";
            else
                $protocol = "http://";

            // TODO: Anticipate the need for .htm and "/" extension here

            // Redirect to the master host
            self::permRedirect($protocol . self::$MASTER_HOST . "/" . 
                            self::getUrlFile() . self::getUrlParams());
        }
    }

    // If $FORCE_HTTPS is true and HTTPS is not in use, redirect to an HTTPS URL
    private static function checkHTTPS()
    {
        if(self::$FORCE_HTTPS && (empty($_SERVER["HTTPS"]) || $_SERVER['HTTPS'] == 'off'))
        {
            self::permRedirect("https://" . $_SERVER['HTTP_HOST'] . "/" . 
                            self::getUrlFile() . self::getUrlParams());
        }
    }

    // Returns the page name ($PAGE_INFO key) for the currently requested page, or
    // false if the page requested is not present in $PAGE_INFO.
    // e.g. if the url is either:
    //    a) http://example.com/foo.htm
    // or b) http://example.com/foo
    // this method will return 'foo', if $PAGE_INFO['foo'] exists.
    private static function getPageArrayKey()
    {
        $page_name = strtolower(self::getUrlFile());
        $htm_removed = false;

        // Remove the .htm extension if present
        if(strpos($page_name, ".htm") === strlen($page_name) - 4)
        {
            $page_name = substr($page_name, 0, strlen($page_name) - 4);
            $htm_removed = true;

            // If the page name ends in a "/", it is not valid, e.g:
            // http://example.com/.htm
            // http://example.com/foo/.htm
            if(empty($page_name) || $page_name[strlen($page_name) - 1] == "/")
                return false;
        }

        // Return the page array if the page exists, otherwise boolean false.
        if(array_key_exists($page_name, self::$PAGE_INFO))
        {
            return $page_name;
        }
        elseif(array_key_exists($page_name . "/", self::$PAGE_INFO) && !$htm_removed)
        {
            return $page_name . "/";
        }
        else
        {
            return false;
        }
    }

    // Checks if the P_RDIR index exists in the page array and redirects to
    // the specified page if so.
    private static function checkRedirectRequest($page_array)
    {
        if(array_key_exists(P_RDIR, $page_array))
        {
            $redir = $page_array[P_RDIR];

            // Anticipate the need for .htm extension to avoid a second redirect
            // All pages that don't end in a / must end in .htm
            if(!empty($redir) && $redir[strlen($redir) - 1] != "/")
            {
                $redir .= ".htm";
            }

            // Redirect, keeping the URL parameters.
            self::permRedirect(self::getUrlFront() . $redir . self::getUrlParams());
        }
    }

    // Ensures that the current URL ends in .htm, if it is the URL of a normal
    // page, or ends in "/" if it is the URL of a virtual directory root.
    // http://example.com/?bar => http://example.com/?bar
    // http://example.com/foo/bar?baz => http://example.com/foo/bar.htm?baz
    // http://example.com/hello => http://example.com/hello/ (if $proper_name is "hello/")
    private static function ensureHTMOrSlashExtension($page_array, $proper_name)
    {
        $file = self::getUrlFile();

        // If the page is a directory (other than the root)...
        if(!empty($proper_name) && $proper_name[strlen($proper_name) - 1] == "/") 
        {
            if($file[strlen($file) - 1] != "/") // ... make sure it ends in "/"
            {
                // Redirect to the / version, preserving the parameters
                self::permRedirect(self::getUrlFront() . $file . "/" . self::getUrlParams()); 
            }
        }
        // Otherwise, if it's a normal page name, it should end in .htm
        elseif(!empty($file) && strpos($file, ".htm") !== strlen($file) - 4)
        {
            // Redirect to the .htm version, preserving the parameters
            self::permRedirect(self::getUrlFront() . $file . ".htm" . self::getUrlParams()); 
        }
    }

    // Returns the URL parameters, if any.
    // If there are URL parameters, a the parameter string (including "?")
    // will be returned. If there are none, the empty string is returned.
    private static function getUrlParams()
    {
        $url = $_SERVER['REQUEST_URI'];
        $question = strpos($url, "?");
        if($question !== FALSE)
            return substr($url, $question);
        else
            return "";
    }

    // Returns the file part of the URL. This is everything after (not including)
    // the first "/" after the host name and before (not including) the "?" in
    // front of the URL parameters.
    // e.g. http://example.com/foo/bar.htm?baz=foo returns "foo/bar.htm"
    private static function getUrlFile()
    {
        $url = $_SERVER['REQUEST_URI'];
        $first_slash = self::getFirstSlashIndex($url);
        $question = strpos($url, "?");
        if($question === false)
            return substr($url, $first_slash + 1);
        else
            return substr($url, $first_slash + 1, $question - $first_slash - 1);
    }

    // Returns the protocol and host part of the URL, including the "/" after
    // the host name.
    private static function getUrlFront()
    {
        $url = $_SERVER['REQUEST_URI'];
        $first_slash = self::getFirstSlashIndex($url);
        return substr($url, 0, $first_slash + 1);
    }

    // Returns the index of the slash after the hostname, or the index of the
    // last character in the string if there is none.
    private static function getFirstSlashIndex($url)
    {
        $prot_end = strpos($url, "://");
        if($prot_end === false)
            $prot_end = 0;
        else
            $prot_end += 3; // skip over the ://

        // find the first slash after the end of the protocol specifier
        $first_slash = strpos($url, "/", $prot_end);

        // If there is no slash after the protocol specifier, the entire URL
        // is considered the "front" so we return the index of the last element.
        if($first_slash === false)
            return strlen($url) - 1;
        else
            return $first_slash;
    }

    // Send a HTTP 301 Moved Permanently redirect and cease script execution.
    private static function permRedirect($newUrl)
    {
        header("HTTP/1.1 301 Moved Permanently");
        header("Location: $newUrl");
        die();
    }

    // Send the HTTP 404 Not Found header
    private static function send404Headers()
    {
        header("HTTP/1.0 404 Not Found");
        header("Status: 404 Not Found");
    }
}
?>
