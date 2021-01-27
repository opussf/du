<?php
require_once( 'jpgraph/jpgraph.php' );
require_once( 'jpgraph/jpgraph_line.php' );
require_once( 'jpgraph/jpgraph_date.php' );
require_once( 'jpgraph/jpgraph_utils.inc.php' );
require_once( 'jpgraph/jpgraph_log.php' );

require( 'bytesToString.php' );

# colors:   http://jpgraph.net/download/manuals/chunkhtml/apd.html

ini_set('memory_limit', '256M');

$dataDir = ".";
$dataFile = "du.json";

$data = json_decode( file_get_contents( $dataFile ), $assoc = True );
ksort( $data );

$total = end($data);
$total = $total['total'];

# break up data by week
$weekSeconds = 86400 * 7;
$now = time();

$dataByWeek = array();
foreach( $data as $ts => $struct ) {
	$weekBack = intval( ( $now - $ts ) / $weekSeconds );  # how many weeks back is this data?
	$dataByWeek[$weekBack][$ts + ($weekSeconds * $weekBack)] = $struct;
	#print( "$now - $ts = ".($now - $ts)." / $weekSeconds = ". intval(($now - $ts) / $weekSeconds)."\n" );
}

$graphDataByWeek = array();
foreach( $dataByWeek as $weekBack => $weekData ) {
	$graphDataByWeek[ $weekBack ]['xData'] = array_keys( $weekData );
	$graphDataByWeek[ $weekBack ]['free'] = 
			array_map( function( $a ) { return $a['free']; }, array_values( $weekData ) );
	$graphDataByWeek[ $weekBack ]['used'] = 
			array_map( function( $a ) { global $total; return $a['used'] / $total * 100; }, array_values( $weekData ) );
	$graphDataByWeek[ $weekBack ]['5m'] = 
			array_map( function( $a ) { return $a['5m']; }, array_values( $weekData ) );
	$graphDataByWeek[ $weekBack ]['15m'] = 
			array_map( function( $a ) { return $a['15m']; }, array_values( $weekData ) );
}

list( $tickPositions, $minTickPositions) = DateScaleUtils::GetTicks( $graphDataByWeek[0]['xData'], $aType = DSUTILS_HOUR1);

$graph = new Graph( 1400, 800 );
$graph->SetScale( "datlin" );
$graph->SetY2Scale( "lin", 0, 0);

$graph->xaxis->SetPos('min');
//$graph->xaxis->SetTickPositions( $tickPositions, $minTickPositions );
$graph->xaxis->SetLabelFormatString( 'D m-d H:i', True );
$graph->xgrid->Show();

ksort( $graphDataByWeek );

foreach( $graphDataByWeek as $weekBack => $weekData ) {
	#print( "<br/>$weekBack: ".sizeof( $weekData['free'] ) );
	$lineFree = new LinePlot( $weekData['free'], $weekData['xData'] );
	$lineFree->SetLegend( 'Free -'.$weekBack );
	$graph->Add( $lineFree );
	if( $weekBack == 0) {
		$lineLoad = new LinePlot( $weekData['5m'], $weekData['xData'] );
		$lineLoad->SetLegend( 'Load (5m)' );
		$graph->AddY2( $lineLoad );
		$lineLoad->SetColor('darkorange@0.75');

		$lineLoad2 = new LinePlot( $weekData['15m'], $weekData['xData'] );
		$lineLoad2->SetLegend( 'Load (15m)' );
		$graph->AddY2( $lineLoad2 );
		$lineLoad2->SetColor('darkorchid@0.75');
	}
}

$graph->legend->SetLayout( LEGEND_HOR );
// SetPos ( $aX, $aY, $aHAlign, $aVAlign )
$graph->legend->SetPos( 0.5, 0.99, 'center', 'bottom' );

$graph->yaxis->SetLabelFormatCallback('bytesToString');
$graph->xaxis->SetLabelAngle( 90 );

$graph->SetMargin( 75, 50, 25, 75 );
$graph->Stroke();
?>
