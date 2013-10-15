<?php
/*
KML tour builder 1.1 by Alex Farrant.
Makes a KML file of points around a given location.
Replay in Google earth to take a tour of the surrounding 
area and build up your disk cache for offline usage...
*/
function numbers($str){
$result = preg_replace("/[^0-9.-]/","", $str); 
if($str===""){
die("Missing input!");
}
return($result);
}

if($_POST["lat"]){

$lat=numbers($_POST["lat"]);
$lon=numbers($_POST["lon"]); 
$radius=numbers($_POST["rad"]); // km radius
$range=numbers($_POST["alt"]); //1km from ground

//VALIDATION
if($lat >  60 || $lon > 180){
die("Lat/Lon too high");
}
if($lat <  -60 || $lon < -180){
die("Lat/Lon too low");
}
if($radius > 200 || $radius <= 0){
die("Bad radius. Max=200km");
}
if($range < 0 || $range > 20){
die("Altitude out of range. Use 0 to 20.");
}

$degrange=$radius/111; //111km = 1deg at equator
$increment=$range/111; 

$minlat=$lat-$degrange; //S
$maxlat=$lat+$degrange; //N
$minlon=$lon-$degrange; //W
$maxlon=$lon+$degrange; //E

	if($maxlon>180){
	$maxlon=$maxlon-360; //188 = -172
	}
	if($minlon<-180){
	$minlon=$minlon+360; //-188 = 172
	}

//make range into meters
$range=$range*1000;
	
function makePlacemark($lat,$lon,$range){
$kml ="<Placemark>\n";
$kml.="				<LookAt>\n";
$kml.="					<longitude>$lon</longitude>\n";
$kml.="					<latitude>$lat</latitude>\n";
$kml.="					<altitude>0</altitude>\n";
$kml.="					<heading>0</heading>\n";
$kml.="					<tilt>0</tilt>\n";
$kml.="					<range>$range</range>\n";
$kml.="					<gx:altitudeMode>relativeToGround</gx:altitudeMode>\n";
$kml.="				</LookAt>\n";
$kml.="				<styleUrl>#m_ylw-pushpin</styleUrl>\n";
$kml.="				<Point>\n";
$kml.="					<gx:drawOrder>1</gx:drawOrder>\n";
$kml.="					<coordinates>$lon,$lat,0</coordinates>\n";
$kml.="				</Point>\n";
$kml.="</Placemark>\n";

return($kml);
}

//KML HEADER
$kml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
$kml .= "<kml xmlns=\"http://www.opengis.net/kml/2.2\" xmlns:gx=\"http://www.google.com/kml/ext/2.2\" xmlns:kml=\"http://www.opengis.net/kml/2.2\" xmlns:atom=\"http://www.w3.org/2005/Atom\">\n";
$kml .= "	<Folder>\n";
$kml .= "		<name>Play me</name>\n";
$kml .= "		<description><![CDATA[<h3>Quickstart</h3>Within options set the memory cache to 16MB and the disk cache to 2000MB, then select this folder and click the 'Play Tour' button below 'Places'. <h3>Optimisation</h3>To speed things up set your 'Touring' options to wait 2 seconds per feature for the imagery to download (speed varies by connection) and your 'Navigation' options to fly to as fast as possible.<br><br>]]></description>";
$kml .= "		<open>0</open>\n";
		
//LONGITUDE
for($x=$minlon;$x<$maxlon;$x=$x+$increment){
	//LATITUDE
	for($y=$minlat;$y<$maxlat;$y=$y+$increment){
	$kml .= makePlacemark(round($y,3),round($x,3),$range);
	}
}

$kml .= "</Folder>\n</kml>";
$fname=date("YmdHi")."_KML-tour";
$file = fopen("kmltours/$fname.kml","w");
fwrite($file,$kml);
fclose($file);
header("Location: kmltours/$fname.kml");
}//POST
?>
<html>
<form method="post" action="kmltourbuilder.php">
<table>
<tr><td>Latitude<td><input type="text" size="6" name="lat"> Dec degs
<tr><td>Longitude<td><input type="text" size="6" name="lon"> Dec degs
<tr><td>Area radius<td><input type="text" size="3" name="rad">KM
<tr><td>View altitude<td>
<select name="alt">
<option value="1">1000 (Street level)</option>
<option value="2">2000</option>
<option value="5">5000</option>
<option value="10">10000</option>
<option value="20">20000 (District level)</option>
</select>m ASL
<tr><td><input type="submit" value="Create KML tour">
</table>
</form>

</html>
