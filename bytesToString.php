<?php

function bytesToString( $bytes ) {
	$prefix = ( $bytes < 0 ? "-" : "" );
	$bytes = floatval( abs( $bytes ) );
	$Units = array( "", "Ki", "Mi", "Gi", "Ti", "Pi", "Ei", "Zi", "Yi" );
	foreach( $Units as $u ) {
		#print( sprintf( "%s %sB\n", $bytes, $u) );
		$b = $bytes / 1024;
		if ($b > 1) {
			$bytes = $b;
		} else { break; }
	}
	return( sprintf( "%s%s %sB", $prefix, round( $bytes, 3 ), $u ) );
}
?>
