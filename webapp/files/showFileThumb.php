<?php
    require_once( "../../Lib/lib.php" );
    require_once( "../../Lib/db.php" );

    // TODO validate input data
    $id = $_GET['id'];

    // Read from the data base details about the file
    $fileDetails = getFileDetails( $id );

    $thumbFileNameAux = $fileDetails[ 'thumbFileName' ];
    $thumbMimeFileName = $fileDetails[ 'thumbMimeFileName' ];
    $thumbTypeFileName = $fileDetails[ 'thumbTypeFileName' ];

    header( "Content-type: $thumbMimeFileName/$thumbTypeFileName");
    header( "Content-Length: " . filesize($thumbFileNameAux) );

    $thumbFileHandler = fopen( $thumbFileNameAux, 'rb' );
    fpassthru( $thumbFileHandler );

    fclose( $thumbFileHandler );
?>