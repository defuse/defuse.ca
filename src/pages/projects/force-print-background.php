<h1>How to Force HTML/CSS Background Color for Printing</h1>

<p>Web browsers, by default, remove background colors when printing. Unfortunately, this can't be overridden using HTML, CSS, or JavaScript. The user has to change a setting in the browser to print background colors. However, it is possible to fake the background color using an image, if you really need the background color to print by default.</p>

<p>The trick is to create a single pixel image of the desired color and expand it to fill the element, then put the element's content inside a DIV, and put that DIV on top of the image using absolute positioning. It works surprisingly well in most browsers. See the following examples; use your browser's "Print Preview" option to see the difference.</p>

<h2>Simple DIV</h2>

<p><strong>CSS Background version:</strong></p>

<div style="background-color: #00ffe4; height: 100px; width: 100px;">
Hello, world.
</div>

<p><strong>Code:</strong></p>
<div class="code" >
&lt;div&nbsp;style=&quot;background-color:&nbsp;#00ffe4;&nbsp;height:&nbsp;100px;&nbsp;width:&nbsp;100px;&quot;&gt;<br />
Hello,&nbsp;world.<br />
&lt;/div&gt;<br />
</div>

<p><strong>Printable version:</strong></p>

<div style="position: relative;">
    <img src="/images/blue.png" style="width: 100px; height: 100px;">
    <div style="position: absolute; top: 0px; left: 0px;">
        Hello, world.
    </div>
</div>

<p><strong>Code:</strong></p>

<div class="code" >
&lt;div&nbsp;style=&quot;position:&nbsp;relative;&quot;&gt;<br />
&nbsp;&nbsp;&nbsp;&nbsp;&lt;img&nbsp;src=&quot;/images/blue.png&quot;&nbsp;style=&quot;width:&nbsp;100px;&nbsp;height:&nbsp;100px;&quot;&gt;<br />
&nbsp;&nbsp;&nbsp;&nbsp;&lt;div&nbsp;style=&quot;position:&nbsp;absolute;&nbsp;top:&nbsp;0px;&nbsp;left:&nbsp;0px;&quot;&gt;<br />
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Hello,&nbsp;world.<br />
&nbsp;&nbsp;&nbsp;&nbsp;&lt;/div&gt;<br />
&lt;/div&gt;<br />
</div>

<h2>Table</h2>

<p><strong>CSS Background version:</strong></p>

<table style="border-collapse: collapse; float: left; margin-right: 30px;">
    <tr>
        <td style="background-color: #53ff7b; width: 2em; height: 2em; text-align: center;">
            a
        </td>
        <td style="background-color: #00ffe4; width: 2em; height: 2em; text-align: center;">
            B
        </td>
    </tr>
    <tr>
        <td style="background-color: #00ffe4; width: 2em; height: 2em; text-align: center;">
            C
        </td>
        <td style="background-color: #53ff7b; width: 2em; height: 2em; text-align: center;">
            d
        </td>
    </tr>
</table>

<table style="border-collapse: collapse; font-size: 20px;">
    <tr>
        <td style="background-color: #53ff7b; width: 2em; height: 2em; text-align: center;">
            a
        </td>
        <td style="background-color: #00ffe4; width: 2em; height: 2em; text-align: center;">
            B
        </td>
    </tr>
    <tr>
        <td style="background-color: #00ffe4; width: 2em; height: 2em; text-align: center;">
            C
        </td>
        <td style="background-color: #53ff7b; width: 2em; height: 2em; text-align: center;">
            d
        </td>
    </tr>
</table>

<p><strong>Code:</strong></p>
<div class="code" >
&lt;table style=&quot;border-collapse: collapse; float: left; margin-right: 30px;&quot;&gt;<br />
&nbsp;&nbsp;&nbsp;&nbsp;&lt;tr&gt;<br />
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;td style=&quot;background-color: #53ff7b; width: 2em; height: 2em; text-align: center;&quot;&gt;<br />
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;a<br />
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/td&gt;<br />
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;td style=&quot;background-color: #00ffe4; width: 2em; height: 2em; text-align: center;&quot;&gt;<br />
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;B<br />
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/td&gt;<br />
&nbsp;&nbsp;&nbsp;&nbsp;&lt;/tr&gt;<br />
&nbsp;&nbsp;&nbsp;&nbsp;&lt;tr&gt;<br />
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;td style=&quot;background-color: #00ffe4; width: 2em; height: 2em; text-align: center;&quot;&gt;<br />
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;C<br />
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/td&gt;<br />
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;td style=&quot;background-color: #53ff7b; width: 2em; height: 2em; text-align: center;&quot;&gt;<br />
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;d<br />
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/td&gt;<br />
&nbsp;&nbsp;&nbsp;&nbsp;&lt;/tr&gt;<br />
&lt;/table&gt;<br />
<br />
&lt;table style=&quot;border-collapse: collapse; font-size: 20px;&quot;&gt;<br />
&nbsp;&nbsp;&nbsp;&nbsp;&lt;tr&gt;<br />
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;td style=&quot;background-color: #53ff7b; width: 2em; height: 2em; text-align: center;&quot;&gt;<br />
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;a<br />
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/td&gt;<br />
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;td style=&quot;background-color: #00ffe4; width: 2em; height: 2em; text-align: center;&quot;&gt;<br />
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;B<br />
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/td&gt;<br />
&nbsp;&nbsp;&nbsp;&nbsp;&lt;/tr&gt;<br />
&nbsp;&nbsp;&nbsp;&nbsp;&lt;tr&gt;<br />
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;td style=&quot;background-color: #00ffe4; width: 2em; height: 2em; text-align: center;&quot;&gt;<br />
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;C<br />
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/td&gt;<br />
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;td style=&quot;background-color: #53ff7b; width: 2em; height: 2em; text-align: center;&quot;&gt;<br />
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;d<br />
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/td&gt;<br />
&nbsp;&nbsp;&nbsp;&nbsp;&lt;/tr&gt;<br />
&lt;/table&gt;<br />
</div>

<p><strong>Printable version:</strong></p>

<table style="border-collapse: collapse; float: left; margin-right: 30px;" cellspacing="0" cellpadding="0">
    <tr>
        <td>
            <!-- The key is to NOT set widths on the TD or outer DIV, and let the IMG push them open -->

            <div style="position: relative">
                <img src="images/green.png" style="width:2em; height:2em; border: 0; padding: 0" />
                <!-- Put the upper left corner of the character in the middle then move it (left+up)wards by "half" of the char width -->
                <span style="position: absolute; top: 50%; left: 50%; margin-top: -0.6em; margin-left: -0.3em">a</span> 
            </div>
        </td>
        <td>
            <div style="position: relative">
                <img src="images/blue.png" style="width:2em; height:2em; border: 0; padding: 0" />

                <span style="position: absolute; top: 50%; left: 50%; margin-top: -0.6em; margin-left: -0.3em">B</span> 
            </div>
        </td>
    </tr>
        <td>
            <div style="position: relative">
                <img src="images/blue.png" style="width:2em; height:2em; border: 0; padding: 0" />
                <span style="position: absolute; top: 50%; left: 50%; margin-top: -0.6em; margin-left: -0.3em">c</span> 
            </div>

        </td>
        <td>
            <div style="position: relative">
                <img src="images/green.png" style="width:2em; height:2em; border: 0; padding: 0" />
                <span style="position: absolute; top: 50%; left: 50%; margin-top: -0.6em; margin-left: -0.3em">D</span> 
            </div>
        </td>
    <tr>
    </tr>
</table>

<table style="border-collapse: collapse; font-size: 20px;" cellspacing="0" cellpadding="0">
    <tr>
        <td>
            <!-- The key is to NOT set widths on the TD or outer DIV, and let the IMG push them open -->

            <div style="position: relative">
                <img src="images/green.png" style="width:2em; height:2em; border: 0; padding: 0" />
                <!-- Put the upper left corner of the character in the middle then move it (left+up)wards by "half" of the char width -->
                <span style="position: absolute; top: 50%; left: 50%; margin-top: -0.6em; margin-left: -0.3em">a</span> 
            </div>
        </td>
        <td>
            <div style="position: relative">
                <img src="images/blue.png" style="width:2em; height:2em; border: 0; padding: 0" />

                <span style="position: absolute; top: 50%; left: 50%; margin-top: -0.6em; margin-left: -0.3em">B</span> 
            </div>
        </td>
    </tr>
        <td>
            <div style="position: relative">
                <img src="images/blue.png" style="width:2em; height:2em; border: 0; padding: 0" />
                <span style="position: absolute; top: 50%; left: 50%; margin-top: -0.6em; margin-left: -0.3em">c</span> 
            </div>

        </td>
        <td>
            <div style="position: relative">
                <img src="images/green.png" style="width:2em; height:2em; border: 0; padding: 0" />
                <span style="position: absolute; top: 50%; left: 50%; margin-top: -0.6em; margin-left: -0.3em">D</span> 
            </div>
        </td>
    <tr>
    </tr>
</table>

<p><strong>Code:</strong></p>

<div class="code" >
&lt;table style=&quot;border-collapse: collapse; float: left; margin-right: 30px;&quot; cellspacing=&quot;0&quot; cellpadding=&quot;0&quot;&gt;<br />
&nbsp;&nbsp;&nbsp;&nbsp;&lt;tr&gt;<br />
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;td&gt;<br />
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;!-- The key is to NOT set widths on the TD or outer DIV, and let the IMG push them open --&gt;<br />
<br />
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;div style=&quot;position: relative&quot;&gt;<br />
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;img src=&quot;images/green.png&quot; style=&quot;width:2em; height:2em; border: 0; padding: 0&quot; /&gt;<br />
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;!-- Put the upper left corner of the character in the middle then move it (left+up)wards by &quot;half&quot; of the char width --&gt;<br />
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;span style=&quot;position: absolute; top: 50%; left: 50%; margin-top: -0.6em; margin-left: -0.3em&quot;&gt;a&lt;/span&gt; <br />
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/div&gt;<br />
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/td&gt;<br />
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;td&gt;<br />
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;div style=&quot;position: relative&quot;&gt;<br />
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;img src=&quot;images/blue.png&quot; style=&quot;width:2em; height:2em; border: 0; padding: 0&quot; /&gt;<br />
<br />
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;span style=&quot;position: absolute; top: 50%; left: 50%; margin-top: -0.6em; margin-left: -0.3em&quot;&gt;B&lt;/span&gt; <br />
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/div&gt;<br />
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/td&gt;<br />
&nbsp;&nbsp;&nbsp;&nbsp;&lt;/tr&gt;<br />
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;td&gt;<br />
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;div style=&quot;position: relative&quot;&gt;<br />
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;img src=&quot;images/blue.png&quot; style=&quot;width:2em; height:2em; border: 0; padding: 0&quot; /&gt;<br />
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;span style=&quot;position: absolute; top: 50%; left: 50%; margin-top: -0.6em; margin-left: -0.3em&quot;&gt;c&lt;/span&gt; <br />
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/div&gt;<br />
<br />
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/td&gt;<br />
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;td&gt;<br />
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;div style=&quot;position: relative&quot;&gt;<br />
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;img src=&quot;images/green.png&quot; style=&quot;width:2em; height:2em; border: 0; padding: 0&quot; /&gt;<br />
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;span style=&quot;position: absolute; top: 50%; left: 50%; margin-top: -0.6em; margin-left: -0.3em&quot;&gt;D&lt;/span&gt; <br />
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/div&gt;<br />
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/td&gt;<br />
&nbsp;&nbsp;&nbsp;&nbsp;&lt;tr&gt;<br />
&nbsp;&nbsp;&nbsp;&nbsp;&lt;/tr&gt;<br />
&lt;/table&gt;<br />
<br />
&lt;table style=&quot;border-collapse: collapse; font-size: 20px;&quot; cellspacing=&quot;0&quot; cellpadding=&quot;0&quot;&gt;<br />
&nbsp;&nbsp;&nbsp;&nbsp;&lt;tr&gt;<br />
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;td&gt;<br />
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;!-- The key is to NOT set widths on the TD or outer DIV, and let the IMG push them open --&gt;<br />
<br />
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;div style=&quot;position: relative&quot;&gt;<br />
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;img src=&quot;images/green.png&quot; style=&quot;width:2em; height:2em; border: 0; padding: 0&quot; /&gt;<br />
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;!-- Put the upper left corner of the character in the middle then move it (left+up)wards by &quot;half&quot; of the char width --&gt;<br />
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;span style=&quot;position: absolute; top: 50%; left: 50%; margin-top: -0.6em; margin-left: -0.3em&quot;&gt;a&lt;/span&gt; <br />
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/div&gt;<br />
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/td&gt;<br />
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;td&gt;<br />
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;div style=&quot;position: relative&quot;&gt;<br />
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;img src=&quot;images/blue.png&quot; style=&quot;width:2em; height:2em; border: 0; padding: 0&quot; /&gt;<br />
<br />
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;span style=&quot;position: absolute; top: 50%; left: 50%; margin-top: -0.6em; margin-left: -0.3em&quot;&gt;B&lt;/span&gt; <br />
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/div&gt;<br />
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/td&gt;<br />
&nbsp;&nbsp;&nbsp;&nbsp;&lt;/tr&gt;<br />
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;td&gt;<br />
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;div style=&quot;position: relative&quot;&gt;<br />
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;img src=&quot;images/blue.png&quot; style=&quot;width:2em; height:2em; border: 0; padding: 0&quot; /&gt;<br />
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;span style=&quot;position: absolute; top: 50%; left: 50%; margin-top: -0.6em; margin-left: -0.3em&quot;&gt;c&lt;/span&gt; <br />
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/div&gt;<br />
<br />
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/td&gt;<br />
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;td&gt;<br />
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;div style=&quot;position: relative&quot;&gt;<br />
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;img src=&quot;images/green.png&quot; style=&quot;width:2em; height:2em; border: 0; padding: 0&quot; /&gt;<br />
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;span style=&quot;position: absolute; top: 50%; left: 50%; margin-top: -0.6em; margin-left: -0.3em&quot;&gt;D&lt;/span&gt; <br />
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/div&gt;<br />
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/td&gt;<br />
&nbsp;&nbsp;&nbsp;&nbsp;&lt;tr&gt;<br />
&nbsp;&nbsp;&nbsp;&nbsp;&lt;/tr&gt;<br />
&lt;/table&gt;<br />
</div>
