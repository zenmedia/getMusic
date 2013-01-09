<?php
/**
 * ID3 XML output.
 *
 * @author Ryan Cummins <ryan@zenmedia.co.uk>
 *
 * How to use:
 *
 * When calling this script you need to send an XML request of:
 *
 * <?xml version="1.0" encoding="ISO-8859-1"?>
 * <playlist>
 *      <request action="listMusic" directory="music" /> ** you can add multiple requests here if you want to scan more than one directory.
 * </playlist>
 *
 */

// include pear mp3_id
require_once("Id.php");

// Get current XML Input Stream
$xmlREQ = file_get_contents('php://input');

// declare and instantiate the DomDocument calls.
$xml_in = new DomDocument('1.0', 'UTF-8');
$xml_in->formatOutput = true;
$xml_in->preserveWhiteSpace = false;

$xml = new DomDocument('1.0', 'UTF-8');
$xml->formatOutput = true;
$xml->preserveWhiteSpace = false;
$root = $xml->createElement('playlist');
$root = $xml->appendChild($root);

if(strlen($xmlREQ) > 0) {
        if($xml_in->LoadXML($xmlREQ)) {
                $playlist = $xml_in->getElementsByTagName('playlist');
                foreach($playlist as $item) {
                        if($item->childNodes->length) {
                                $request = $xml_in->getElementsByTagName('request');
                                for($i=0;$i<$request->length;$i++) {
                                        $xmlIn[$i]['action'] = $request->item($i)->getAttribute('action');
                                        $xmlIn[$i]['directory'] = $request->item($i)->getAttribute('directory');
                                }
                        }
                }
                foreach($xmlIn as $request) {
                        switch($request['action']) {
                                case 'listMusic':
                                        $dir = getcwd().DIRECTORY_SEPARATOR.$request['directory'].DIRECTORY_SEPARATOR;
                                        if (is_dir($dir)) {
                                                if ($dh = opendir($dir)) {
                                                        while (($filename = readdir($dh)) !== false){
                                                                $php_check = explode('.', $filename);
                                                                if($php_check[1] == 'mp3') {
                                                                        if(!is_dir($filename)) {
                                                                            $id3 = &new MP3_Id();
                                                                            $id3->read($dir.$filename);
                                                                            $track = $xml->createElement('track');
                                                                            $track = $root->appendChild($track);
                                                                            foreach($id3 as $k=>$v) {
                                                                                if(!in_array($k, array('debug','debugbeg','debugend','file'))) {
                                                                                        $track->setAttribute($k, urlencode($id3->getTag($k)));
                                                                                }
                                                                            }
                                                                                $track->setAttribute('url',(($_SERVER["HTTPS"] != "on") ? "http" : "https" )."://".$_SERVER["SERVER_NAME"].DIRECTORY_SEPARATOR.$request['directory'].DIRECTORY_SEPARATOR.$filename);
                                                                        }
                                                                }
                                                        }
                                        closedir($dh);
                                                }
                                        }
                                break;
                        }
                }
        }
} else {
    $ErrorRS = $xml->createElement('ErrorRS');
    $ErrorRS = $root->appendChild($ErrorRS);
    $ErrorRS->setAttribute('TimeStamp', date("Y-m-d\TH:i:s"));
    $ErrorRS->setAttribute('ErrorMessage', 'Request expected.');

}

header("Content-Type: text/xml");
print $xml->saveXML();
