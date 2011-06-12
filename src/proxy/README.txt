PHProxy Source Code README
_____________________________________________________________________

Source Code Version 0.4 - September 7th 2005
Latest Version: http://www.sourceforge.net/projects/poxy/

Copyright 2005 ultimategamer00 (Abdullah A.)


Contact
_____________________________________________________________________

Email: abdullah.a@gmail.com
Website: http://www.whitefyre.com/poxy/


Support
_____________________________________________________________________

http://www.sourceforge.net/projects/poxy/
Look for the forums


Table of Contents
_____________________________________________________________________

1. License
2. What is PHProxy?
3. How it Works
4. Requirements
5. Installation
6. Configurable Script Variables
7. Available Options
8. Legal Disclaimer
9. ChangeLog, FAQ, TODO, LICENSE, Bugs, Limitations
10. Credits


1. License
_____________________________________________________________________

This source code is released under the GPL.
A copy of the license in provided in this package in the file
 named LICENSE.txt


2. What is PHProxy?
_____________________________________________________________________


PHProxy is a web HTTP (for now; FTP is not supprted yet) proxy 
designed to bypass proxy restrictions through
a web interface very similar to the popular CGIProxy 
(http://www.jmarshall.com/tools/cgiproxy/). For example, in my 
university, the IT department blocks a lot of harmless websites 
simply because of their popularity. So I use this porgram to access 
those websites. The only thing that PHProxy needs is a web server 
with PHP installed (see Requirements below).
Be aware though, that the sever has to be able to access those 
resources to deliver them to you.



3. How it Works
_____________________________________________________________________

You simply supply a URL to the form and click Browse. The script then 
accesses that URL, and if it has  any HTML contents, it modifies 
any URLs so that they point back to the script. Of course, there is more
to it than this, but if you would like to know more in
detail, view the source code. 
Comments have yet to be added.


4. Requirements
_____________________________________________________________________

- PHP version >= 4.2.0 with safe_mode turned Off
- file_uploads turned On for HTTP file uploads.
- JavaScript turned on for the browser. Setting the flags and 
  encrypting the supplied URL initially requires the use of 
  JavaScript.


5. Installation
_____________________________________________________________________

Simply upload these files to a directory of your liking:
- PHProxy.class.php
- index.php
- url_form.inc
- javascript.js
- style.css

All you need to do now is to access index.php and start browsing!


6. Configurable Script Variables
_____________________________________________________________________

The $config is available at the beginning of index.php:

url_var_name:              name of the variable the contains the url 
                           to be passed to the script. default: 'q'
flags_var_name*:           name of the variables the contains the flags
                           to be passed to the script. default: 'hl'
get_form_name:             name of the GET forms in case they were 
                           passed through the proxy.
                           default: '__script_get_form'
proxy_url_form_name*:      name of the form that you supply the URL to.
                           default: 'poxy_url_form'
proxy_settings_form_name*: name of the form the contains the flags. 
                           default: 'poxy_settings_form'
max_file_size:             maximum file size that can be downloaded
                           through the proxy. Use -1 for unlimited.
                           default: -1

* the variables also have to be edited at the beginning of javascript.js

---

These variables are available at the beginning of the PHProxy class.

$flags: this array contains the default values for the browsing
        options which are explained in section 7.

$allowed_hosts: this array contains entries for the domain names that
                the script is allowed to browse. For example, setting 
                this variable to array('www.yahoo.com') will restrict 
                the script to browse _only_ www.yahoo.com. If you'd 
                like the script browse all subdomains within yahoo.com
                then instead of www.yahoo.com, set it to 
                array('.yahoo.com'). The dot in the beginning tells 
                the script to match all subdomains. To add more 
                entries, simply seperate them with a comma. Example: 
                array('.yahoo.com', '.fark.com');

$banned_hosts: this is the same as $allowed_hosts. But instead of
               defining an allow list, you could simply define 
               the domain names that you do not wish the script to 
               browse. This overrides $allowed_hosts. Use either one
               of them.




7. Available Options
_____________________________________________________________________

These options are available to you through the web interface. 
You can also edit the default values in the class variable $flags.
Values can either be 1 (true) or 0 (false). 
All values are defaulted to 1.

+-------------------------------------------------------------------+
| Option         | Explanation                                      |
+-------------------------------------------------------------------+
| Include Form   | Includes a mini URL-form on every HTML page for  |
|                | easier browsing.                                 |
| Remove Scripts | Remove all sorts of client-side scripting        |
|                | (i.e. JavaScript). Removal is not perfect. Some  |
|                | scripts might slip by here and there.            |
| Accept Cookies | Accept HTTP cookies                              |
| Show Images    | Show images. You might want to turn this off if  |
|                | you want to save your server's bandwith.         |
| Show Referer   | Show referring website in HTTP headers. This     |
|                | will show the base URL for the website you're    |
|                | currently viewing. Because many website disable  |
|                | HotLinking, this can be quite useful.            |
| Rotate13       | Use rotate13 encoding on the URL. *              | 
| Base64         | Use base64 encoding on the URL. *                |
| Strip Meta     | Strip meta HTML tags                             |
| Strip Title    | Strip Website title                              |
| Session Cookies| Store cookies for this current session only      |
+-------------------------------------------------------------------+

* only one type of encryption will be used even if both are selected


8. Legal Disclaimer
_____________________________________________________________________

Since this script basically bypasses restrictions that were imposed
on you, using it might be illegal in your country, school, office, 
or whatever. Even your host might not allow you to run it. Use it at
your own risk. I will not be responsible for any damages done or any
 harm that might result from using this script.


9. ChangeLog, FAQ, TODO, LICENSE, Bugs, Limitations
_____________________________________________________________________

Refer to the accompanying files.
You can infer the limitations and bugs from the TODO file.


10. Credits
______________________________________________________________________

James Marshall (http://www.jmarshall.com/) for his excellent CGIProxy
script which was a high inspiration and guide for me. The HTML
modification section is based off his script

