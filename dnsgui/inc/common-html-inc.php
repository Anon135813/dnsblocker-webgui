<?php

function TopNavHtml($pg){

	$s  = '<div class="TopNav01"><ul><li><p>Navigation:</p></li>';

	$s .= '<li><a';
	if($pg==1) $s .= ' class="sel"';
	$s .= ' href="index.php">System Infomation</a></li>';

	$s .= '<li><a';
	if($pg==2) $s .= ' class="sel"';
	$s .= '  href="viewlog.php">View DNS Logs</a></li>';

	$s .= '<li><a';
	if($pg==3) $s .= ' class="sel"';
	$s .= ' href="viewlist.php">View Block Lists</a></li>';

	$s .= '</ul></div>';

	return $s;

}

function KeyValTblHtml($k, $v, $valTdCss='vl' ,$d=0){

	//$imx = min(count($k), count($v));
	$imx = count($k);

	$b = '<table class="t2">';

	for($i=0; $i<$imx; $i++){

		$b .= '<tr><td class="lb">';
		$b .= $k[$i];
		$b .= ':</td>';

		if($d>0){

			for($j=0; $j<$d; $j++){
				$b .= '<td class="';
				$b .= $valTdCss;
				$b .= '">';
				$b .= $v[$i][$j];
				$b .= '</td>';
			}
		}
		else{
			$b .= '<td class="';
			$b .= $valTdCss;
			$b .= '">';
			$b .= $v[$i];
			$b .= '</td>';
		}

		$b .= '</tr>';
		$b .= "\n";
	}

	$b .= '</table>';

	return $b;

}

?>
