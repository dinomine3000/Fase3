<!DOCTYPE html>
<?php
	ini_set('display_errors', 'on');
	
	error_reporting(E_ALL);
	
	$title = "Available Examples";
	
	$ano1 = "2024";
	
	$ano2 = "2025";
	
	$semester = "$ano1/$ano2";
?>

<html>

  <head>
    <meta http-equiv='Content-Type' content='text/html; charset=utf-8'>
    
    <title>SMI <?php echo $semester;?></title>
	
	  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>

    <link rel="stylesheet" type="text/css" href="./Styles/GlobalStyle.css">
  </head>
  
  <body onload="window.open('', '_self', '');">
    <h1 align="center">SMI-LEIM-DEETC Examples Main Page</h1>
    
	  <h2 align="center">Summer Semester <?php echo $semester;?></h2>
           
    <h3><?php echo $title;?></h3>

<?php
  $files = array();
  if ( $handle=opendir( "." ) ) {
    while ( false !== ( $entry = readdir( $handle ) ) ) {
      if ( 
          $entry=="." || 
          $entry==".." || 
          $entry=="index.php" || 
          $entry=="index.html" || 
          $entry=="Lib" || 
          $entry=="Styles" || 
          $entry=="Config" || 
		  $entry=="bin" ||
		  $entry=="java" ||
		  $entry=="DataCodigosPostais" ||
		  $entry=="external" ||
		  $entry==".DS_Store" ||
          preg_match('/zip$/i', $entry) ||
          preg_match('/txt$/i', $entry) 
          ) {
        continue;
      }      
      $files[] = $entry;
    }
    closedir( $handle );
	
	natsort( $files );
	
	foreach( $files as $file ) {
		echo "    <span class='mainPageEntry'><a  href=\"$file\">$file</a></span><br>\r\n";
	}
	
  }
?>
    
    <br>
    
    <span class='mainPageEntry'><a href="javascript:window.close();">Close Page</a></span>

  </body>
  
</html>
