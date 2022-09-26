<?php

/*
Version 2022.09.26.3.01

This is free and unencumbered software released into the public domain.

Anyone is free to copy, modify, publish, use, compile, sell, or
distribute this software, either in source code form or as a compiled
binary, for any purpose, commercial or non-commercial, and by any
means.

In jurisdictions that recognize copyright laws, the author or authors
of this software dedicate any and all copyright interest in the
software to the public domain. We make this dedication for the benefit
of the public at large and to the detriment of our heirs and
successors. We intend this dedication to be an overt act of
relinquishment in perpetuity of all present and future rights to this
software under copyright law.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT.
IN NO EVENT SHALL THE AUTHORS BE LIABLE FOR ANY CLAIM, DAMAGES OR
OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE,
ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR
OTHER DEALINGS IN THE SOFTWARE.

For more information, please refer to <https://unlicense.org>
*/

$stream = ""; // The URL of the livestream
$recording_seconds = 60*60; // 60 * 60 seconds = 1 hour

/*
The timing is somewhat inaccurate, as it depends on the chunksize and kbps of the stream.
Please record more time than needed to prevent a too short recording.
My test recording of 60 Minutes was saved into a ~59:56 Track.

Information: a file named ".lock" is locking the script while recording, to prevent to be opened by multiple users or at multiple times. If an error might occur and the script is locked, you can delete .lock or you wait $recording_seconds to reaccess the script.
*/

////////// There is no need to change anything below this line of code ////////// 

// Turns off all error reporting
error_reporting(0);

if(empty($stream)){exit('<p>Error: Please define an URL in <b>$stream</b>.</p>');}
if(is_file(".lock"))
{
	$time = file_get_contents(".lock");
	if(time()<$time)
	{
		echo '<p>Please wait, recording in progress. Estimated end of recording: <b>'.date("d.m.Y @ H:i:s",$time).'</b>';
		exit;
	} 
	else 
	{
		unlink(".lock");
	}
}

file_put_contents("iswritable.txt","1");
if(!is_file("iswritable.txt"))
{
	echo '<p>Error: Correct writing permissions must be granted for the script. The script must be allowed to write and read files.</p>';
	exit;
} else 
{
	unlink("iswritable.txt");
}

$dir = "recordings/";
if(!is_dir($dir)){mkdir($dir);}

set_time_limit($recording_seconds+60); // The execution time for the script is hereby adjusted
file_put_contents(".lock",time()+$recording_seconds+5);

$filename = $dir.date("Y-m-d_H-i-s",time()).".mp3";

$fp = fopen($stream, 'rb');
if(!$fp){echo "<p>Error: The stream could not be opened.</p>";exit;}

$kb = 30000; // obsolete at the moment; depends on the individual chunksize of the data

$starttime = time();

Repeat:
$read = fread($fp,$kb);
$music[] = $read;

file_put_contents($filename, $read, FILE_APPEND);

$now = time();
if($now-$starttime < $recording_seconds){GOTO Repeat;}

fclose($fp);
unlink(".lock");

if(!is_file($filename))
{
	echo '<p>Error: your file could not be saved.</p>';
	exit;
}
else 
{
	echo '<p>The file was successfully saved to <a href="'.$filename.'"><b>'.$filename.'</b></a>.</p>';
}

?>