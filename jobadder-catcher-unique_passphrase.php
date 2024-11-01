<?php

/*if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly*/

header('Content-type: text/xml');

$response = "<?xml version='1.0' encoding='UTF-8'?>
  <cXML>
    <Response>
      <Status code='400' text='Bad Request'>Bad Request</Status>
    </Response>
  </cXML>";

if ( $_SERVER['REQUEST_METHOD'] == 'POST' ) {

  if ( file_exists(getcwd() . '/yakadanda-jobadder/xml/index.php') ) {
    $postText = file_get_contents("php://input");
    $xmlfile = "yakadanda-jobadder/xml/jobadder_data.xml";
    $FileHandle = fopen($xmlfile, "w") or die("can't open file");

    fwrite( $FileHandle, str_replace("xml=", "", urldecode($postText)) );

    fclose($FileHandle);

    $response = "<?xml version='1.0' encoding='UTF-8'?>
      <cXML>
        <Response>
          <Status code='200' text='OK'>OK</Status>
        </Response>
      </cXML>";
  }

}

echo $response;
