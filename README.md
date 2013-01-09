getMusic
========

Author: Ryan Cummins <ryan@zenmedia.co.uk>

Generate list of music in a standard format using xml.

How to use:
========

When calling this script you need to send an XML request of:

	<?xml version="1.0" encoding="ISO-8859-1"?>
	<playlist>
		<request action="listMusic" directory="music" />
	</playlist>

** you can add multiple requests inside the playlist tags if you want to scan more than one directory.

There is currently only the listMusic method, if you would like another method added feel free to submit a request and ill consider it. :)