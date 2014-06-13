How to install mod_ruby on Debian Squeeze
##########################################
:slug: how-to-install-mod_ruby-on-debian-squeeze
:author: Taylor Hornby
:date: 2012-07-22 00:00
:category: linux
:tags: mod_ruby

If you're a PHP developer that's starting to fall in love with ruby (like me),
you'll want to know how to write "PHP-style" web applications in ruby, for small
web apps that don't need the complexity of Ruby on Rails. You want:

1. No per-app configuration necessary. Installing the app is as simple as
   uploading the script files.
2. Each ruby file you upload is an independent web page, e.g.
   http://example.com/foo.rhtml.
3. Scripts are HTML with ruby code inside delimiters (like php's <?php and ?>).
4. Scripts have easy access to GET and POST data, as well as HTTP headers and
   cookies.
5. Scripts can easily send headers, set cookies, etc.

Unfortunately, to get this working on Debian Squeeze, some initial configuration
required, but it's a one time thing and quick in comparison to the per-app
configuration required by RoR.

First, install mod_ruby:

.. code:: bash

    apt-get install libapache2-mod-ruby

Second, add the following to your site's configuration and read the embedded
comments. Note that in order to lower the ruby safe level with the RubySafeLevel
directive (default is 1), it must appear **outside any "files", "directory", and
"virtualhost" blocks.** If not, you will get an error like: ``mod_ruby: can't
decrease RubySafeLevel``.

.. code:: text

    <IfModule mod_ruby.c>
        RubyRequire apache/ruby-run
        RubyRequire apache/eruby-run
    
        # Safe level 1 does taint checking, but also won't let you run .rb scripts 
        # from a world-writable folder. I highly recommend keeping this at 1, but
        # in a development environment, you can change it to 0 to make scripts
        # run from world-writable folders (OTOH, you really DO want to develop with
        # taint checking turned on). 
        RubySafeLevel 1
    
        # Override the mime types in /etc/mime.types so the output of the script
        # is displayed in the browser, not sent as a download. Some guides will
        # tell you to comment out the corresponding entries in /etc/mime.types, but
        # don't do that as it will affect the entire system, not just apache.
        AddType text/html .rb
        AddType text/html .rbx
        AddType text/html .rhtml
    
        # NOTE: To make .rb files execute, you will need to add:
        #  Options +ExecCGI
        # to the <Directory> they are in. You DON'T need to do this for .rhtml.
        <Files *.rb>
            SetHandler ruby-object
            RubyHandler Apache::RubyRun.instance
        </Files>
        <Files *.rbx>
            SetHandler ruby-object
            RubyHandler Apache::RubyRun.instance
        </Files>
        <Files *.rhtml>
            SetHandler ruby-object
            RubyHandler Apache::ERubyRun.instance
        </Files>
    </IfModule>


Now, .rhtml files will work like PHP files:

.. code:: erb

    <html>
      <head>
        <title>Hello, world!</title>
      </head>
      <body>
        Even numbers less than 100: 
        <ul>
        <%
          1.upto(99) do |x|
            puts "<li>#{x}</li>" if x % 2 == 0
          end
        %>
        </ul>
        <%
          1.upto(50) do |x|
        %>
          This text repeats <b>50</b> times! <br />
        <%
          end
        %>
      </body>
    </html>

And .rb files will work like CGIs:

.. code:: ruby

    #!/usr/bin/ruby
    puts "<ul>"
    300.times do 
      puts "<li>Some text!</li>"
    end
    puts "</ul>"

In both types of script, you can use `Apache.request`_ to read GET and POST
data, set cookies, etc.

.. _`Apache.request`: http://modruby.net/en/doc/?Apache%3A%3ARequest

.. code:: erb

    <html>
      <head>
        <title>Hello, world!</title>
      </head>
      <body>
       GET data in rhtml mod_ruby!
       <%
       Apache.request.paramtable['count'].to_i.times do |n|
         puts n
       end
       %>
      </body>
    </html>


Why?
=====

Writing this post made me realize I'm trying to make ruby do something it
doesn't (yet) do well. PHP has so many built-in features for doing web stuff
like htmlentities, built-in mysql access, and easy access to GET and POST data
through $_GET and $_POST (note that Apache.request.paramtable includes both GET
and POST data, which is bad for security), so for now, I think I'll stick with
PHP until there's a better zero-config lightweight ruby web framework. Sinatra
is the best I have seen so far, but configuring it with Apache is still far too
complicated, in my opinion.
