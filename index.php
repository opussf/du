<!DOCTYPE html>
<html lang='en'>
<head>
<title>Disk Usage</title>
<meta charset="utf-8"/>
<link href='rss' rel='alternate' type='application/rss+xml' title='DU RSS Feed'/>
<link href="du.css" rel="stylesheet" type="text/css"/>
</head>
<body>
<h3>Disk Free Report
<a href="rss.php">RSS</a> </h3>
<img src="duGraph.php" border="1"/>

<?php
require_once( 'bytesToString.php' );
$dataFile = "du.json";
$data = json_decode( file_get_contents( $dataFile ), $assoc = True );

$timeStamps = array_keys( $data );
rsort( $timeStamps );

$newestTS = $timeStamps[0];
$nowFree = $data[$newestTS]['free'];

$change = $nowFree - $data[$timeStamps[1]]['free'];
$changeStr = bytesToString( $change );

$maxDFTS = time();
$maxDF = 0;
$minDFTS = time();
$minDF = $nowFree;
$timeBack = 86400;
$dayCutoff = $newestTS - $timeBack;
$count = 0;
$last24Stats = array( 'sum'=>0, 'count'=>0, 'max'=>0, 'min'=> $nowFree, 'data' => array() );

foreach( $data as $ts => $struct ) {
	$count ++;
	# adjust cutoff TS 
	if( $dayCutoff - 60 < $ts and $dayCutoff + 60 > $ts ) {
		$dayCutoff = $ts;
	}
	if( $ts >= $dayCutoff ) {
		$last24Stats['sum'] += $struct['free'];
		$last24Stats['count'] += 1;
		$last24Stats['min'] = min( $last24Stats['min'], $struct['free'] );
		$last24Stats['max'] = max( $last24Stats['max'], $struct['free'] );
		$last24Stats['data'][$ts] = $struct;
	}
	if( $struct['free'] > $maxDF ) {
		$maxDFTS = $ts;
		$maxDF = $struct['free'];
	}
	if( $struct['free'] < $minDF ) {
		$minDFTS = $ts;
		$minDF = $struct['free'];
	}
}

$last24Stats['ave'] = $last24Stats['sum'] / $last24Stats['count'];
$last24Stats['change'] = bytesToString( $data[$newestTS]['free'] - $data[$dayCutoff]['free'] );
// calc SD for the last 24 hours
$sum = 0;
foreach( $last24Stats['data'] as $ts => $struct ) {
	$vm = $struct['free'] - $last24Stats['ave'];
	$sum = $sum + ( $vm * $vm );
}
$last24Stats['sd'] = $last24Stats['count'] > 1 ? sqrt( $sum / ($last24Stats['count'] - 1) ) : 0;

function formatLine( $caption, $date, $payload, $showDateDiff = false ) {
	global $newestTS;
	$date1 = date_create( "@$date" );
	$date2 = date_create( "@$newestTS" );

	$diff = date_diff( $date1, $date2 );
	$diffStr = sprintf( " (%s)", $diff->format( "%dD %hh%im" ) );

	$outStr = sprintf( "<tr><td>%s</td><td>%s</td><td>%s</td><td>%s</td></tr>",
			$caption, strftime( "%c", $date ), 
			$showDateDiff ? $diffStr : "",
			$payload );
	return $outStr;
}

print( "<div class='freeMaxMin box'>\n" );
print( "<table border=1>\n" );
print( formatLine( "Current:", $newestTS, 
		sprintf( "%s (%s)", bytesToString( $data[$newestTS]['free'] ), $changeStr ) ) );
print( formatLine( "Max:", $maxDFTS, bytesToString( $maxDF ), true ) );
print( formatLine( "Min:", $minDFTS, bytesToString( $minDF ), true ) );
print( "</table>" );
print( "</div>\n" );

print( "<div class='currentStats box'>\n" );
print( "<table border=1>\n" );
print( sprintf( "<tr><td>24H ago</td><td>%s</td><td>%s</td><td>(%s)</td></tr>", 
		strftime( "%c", $dayCutoff ),
		bytesToString( $data[$dayCutoff]['free'] ),
		$last24Stats['change'] ) );
print( sprintf( "<tr><td>Max:</td><td>%s</td><td>Ave:</td><td align='right'>%s</td></tr>",
		bytesToString( $last24Stats['max'] ),
		bytesToString( $last24Stats['ave'] ) ) );
print( sprintf( "<tr><td>Min:</td><td>%s</td><td>SD: (%s)</td><td align='right'>%s</td></tr>",
		bytesToString( $last24Stats['min'] ),
		$last24Stats['count'],
		bytesToString( $last24Stats['sd'] ) ) );
print( "</table>" );
print( "</div>\n" );
?>

</body>
</html>
