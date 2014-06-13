Escaping String Literals (for JavaScript) in PHP
#################################################
:slug: escaping-string-literals-for-javascript-in-php
:author: Taylor Hornby
:date: 2012-07-01 00:00
:category: security
:tags: javascript, escaping

Use the following code to escape user-supplied input before inserting it into
a JavaScript string literal.

.. code:: php

    <?php

    function js_string_escape($data)
    {
        $safe = "";
        for($i = 0; $i < strlen($data); $i++)
        {
            if(ctype_alnum($data[$i]))
                $safe .= $data[$i];
            else
                $safe .= sprintf("\\x%02X", ord($data[$i]));
        }
        return $safe;
    }

Example:

.. code:: html

    <script>
    var foo = "<?php echo js_string_escape($bar); ?>";
    </script>

The standard htmlentities and htmlspecialchars functions don't work because
JavaScript will interpret the HTML entities as a part of the literal string (not
decode them). addslashes is insufficient because it does not escape other
special characters that might convince some browsers that the string literal or
script section has ended (for example, the string "]]>" to end a `CDATA`_). To
escape integers and floats, use these:

.. _`CDATA`: http://www.w3schools.com/xml/xml_cdata.asp

.. code:: php

    <?php

    // returns "0" when given something that isn't an integer.
    function js_integer_escape($number)
    {
        $cast = (string)(int)$number;
        if($cast != (string)$number)
            return "0";
        return $cast;
    }

.. code:: php

    <?php

    // returns "0" when given something that isn't a float
    function js_float_escape($number)
    {
        if(!is_float($number) && !is_numeric($number))
            return "0";
        return (string)(float)$number;
    }
