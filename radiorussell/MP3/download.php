<?php
$filename = $_GET['file'];
$ext = strtolower( substr( strrchr( $filename,"." ),1 ) );
   if ( ! file_exists( $filename ) )
      {
        echo "File does not exist";
        exit;
      };
    switch( $ext )
      {
		case "mp3": $mimetype="audio/mp3"; break;
		case "mp4": $mimetype="audio/mp4"; break;
      }
      header( "Pragma: public" );
      header( "Expires: 0" );
      header( "Cache-Control: must-revalidate, post-check=0, pre-check=0" );
      header( "Cache-Control: private",false );
      header( "Content-Type: $mimetype" );
      header( "Content-Disposition: attachment; filename=\"".basename( $filename )."\";" );
      header( "Content-Transfer-Encoding: binary" );
      header( "Content-Length: ".filesize( $filename ) );
      readfile( "$filename" );
      ?> 