<div>
<center><h1 style="margin:0; padding:0; text-align:center;">PDFCleaner</h1><br />An Experimental PDF Exploit Sanitizer</center>
<br />
<div style="text-align:left; margin:">
<form action="pdfcleaner/sani.php" method="post" enctype="multipart/form-data">
<table style="margin: 0 auto">
<tr><td>PDF File:</td><td><input type="file" name="pdffile" checked="checked" /></td></tr>
<tr><td>Download:</td><td><input type="radio" name="want" value="pdf" checked="checked" />PDF <input type="radio" name="want" value="ps" />PostScript <input type="radio" name="want" value="text" />Plain Text</td></tr>
<tr><td></td><td><input type="submit" name="clean" value="Sanitize" style="width:300px"/></td></tr>
</table>
<br />
</form>
<h2>What is PDFCleaner?</h2>
<p>Lately, there have been many new exploits against Adobe Acrobat Reader. Before now, the only way to protect yourself was to use a different program to open PDF files, so I created PDFCleaner. </p>

<p>PDFCleaner is a service that lets you upload a potentially dangerous PDF file and download a safe, clean, sanitized version of the same file.</p>
<h2>How Does it Work?</h2>
<p>Simple. PDFCleaner converts your PDF file to PostScript format, and then converts it back into a PDF file. The process of interpreting the PDF file, converting it to a different format, and converting that back into PDF ensures that any PDF-specific exploits are not transfered to the new pdf file. 
PostScript is a file format can do almost everything that PDF can do, so in most cases, the resulting PDF file will look exactly the same.  </p>
<h2>Proof of Concept Demonstration</h2>
<p>Watch as I open a PDF file that contains an exploit for an old version of Foxit reader. The exploit is successfully executed and Foxit closes. After the PDF has gone through PDFCleaner, the exploit is no longer attached to the file and Foxit will open it properly. The specific exploit I used is available in the metasploit framework. It is called "windows/fileformat/foxit_title_bof" and is described as "Foxit PDF Reader v4.1.1 Title Stack Buffer Overflow".</p>
<center>
<iframe title="PDFCleaner Proof of Concept YouTube Video" class="youtube-player" width="480" height="390" src="http://www.youtube.com/embed/FJufDCDYRB8" frameborder="0" ></iframe>
</center>
<h2>Is My Data Safe?</h2>
<p>Yes, every file you upload is deleted right away.</p>
<h2>Why Online?</h2>
<p>If we were to make a program to do this on your computer, your computer still has to process the dangerous PDF file, and could be exploited. So it is safer to have our server do it for you.</p>
</div>
</div>
