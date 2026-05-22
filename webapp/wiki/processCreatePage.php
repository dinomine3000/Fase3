<?php
include("../../Lib/lib.php");
    $method = $_SERVER[ 'REQUEST_METHOD' ];
  
    if ( $method=='POST') {
      $_INPUT_METHOD = INPUT_POST;
	  $_ARGS = $_POST;
    } elseif ( $method=='GET' ) {
      $_INPUT_METHOD = INPUT_GET;
	  $_ARGS = $_GET;
    }
    else {
      echo "Invalid HTTP method (" . $method . ")";
      exit();
    }

    $refreshtime = 5;
    
    $flags[] = FILTER_NULL_ON_FAILURE;
    
    //para recriar a validação de regex no servidor, uso regex igual ao utilizado em forms.js
    $primary = filter_input( $_INPUT_METHOD, 'primaryCategory', FILTER_UNSAFE_RAW, $flags);
    $secondary = filter_input( $_INPUT_METHOD, 'secondaryCategory', FILTER_UNSAFE_RAW, $flags);
    $title = filter_input( $_INPUT_METHOD, 'pageTitle', FILTER_UNSAFE_RAW, $flags);
    $content = filter_input( $_INPUT_METHOD, 'content', FILTER_UNSAFE_RAW, $FILTER_FLAG_NO_ENCODE_QUOTES);

    writeWikiPage($primary, $secondary, $title, $content);

    header("Location: ../index.php");
    
?>