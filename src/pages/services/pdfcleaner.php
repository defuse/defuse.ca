<h1>PDFCleaner</h1>
<div style="text-align:center;">An <b>Experimental</b> PDF Exploit Sanitizer</div>
<br />
<form action="pdfcleaner/sani.php" method="post" enctype="multipart/form-data">
<table style="margin: 0 auto">
<tr><td>PDF File:</td><td><input type="file" name="pdffile" checked="checked" /></td></tr>
<tr><td>Download:</td><td><input type="radio" name="want" value="pdf" checked="checked" />PDF <input type="radio" name="want" value="ps" />PostScript <input type="radio" name="want" value="text" />Plain Text</td></tr>
<tr><td></td><td><input type="submit" name="clean" value="Sanitize" style="width:300px"/></td></tr>
</table>
<br />
</form>
<h2>What is PDFCleaner?</h2>
<p>PDF files are dangerous. We regularly see new Adobe Acrobat PDF vulnerabilities being exploited in the wild. Adobe usually takes a while to patch these flaws, and during that time, all Acrobat users are vulnerable. PDFCleaner is designed to <b>remove unknown exploits</b> from PDF files. After the exploit has been removed, opening the file in an unpatched PDF reader <em>should</em> be safe. Note that PDFCleaner is <b>experimental</b>. It is probably possible to design an exploit that would survive PDFCleaner's removal process, so please don't rely on it for absolute security.</p>
<h2>How Does it Work?</h2>
<p>PDFCleaner converts your PDF file to PostScript format, and then converts it back into a PDF file. The process of interpreting the PDF file, converting it to a different format, and converting that back into PDF ensures that any PDF-specific exploits are not transferred to the new PDF file. 
Postscript is a file format can do everything that PDF can do, so in most cases, the resulting PDF file will look exactly the same.  </p>
<h2>Proof of Concept Demonstration</h2>
<p>Watch as I open a PDF file that contains an exploit for an old version of Foxit reader. The malicious PDF file causes Foxit to crash, but after the PDF has gone through PDFCleaner, the exploit is no longer attached to the file and Foxit will open it without crashing. The specific exploit I used is available in the metasploit framework. It is called "windows/fileformat/foxit_title_bof" and is described as "Foxit PDF Reader v4.1.1 Title Stack Buffer Overflow".</p>
<center>
<iframe title="PDFCleaner Proof of Concept YouTube Video" class="youtube-player" width="480" height="390" src="http://www.youtube.com/embed/FJufDCDYRB8" frameborder="0" ></iframe>
</center>
<h2>Is My Data Safe?</h2>
<p>Yes. Every file you upload is sent through an encrypted SSL/TLS connection and is deleted as soon as the exploit removal process is complete.</p>
<h2>Why Online?</h2>
<p>If we were to make a program to do this on your computer, your computer still has to process the dangerous PDF file, and could be exploited. So it is safer to have our server do it for you.</p>
