<div id='what-is'>
	<div id='what-is-head'>
		<h1 class='left'>Get Started with RentSquare Now!</h1>
		<?php echo $this->Html->link('CLICK TO GET STARTED', array('controller' => 'Users', 'action' => 'register'), array('class' => 'button green-grad bold'));?>
		<div class='clear'></div>
	</div>

	<div class='row'>
		<div class='left two-thirds'>
			<h2>Let RentSquare Make Your Life Easier</h2>
			<p>
				RentSquare is an online property management solution, designed to simplify and enhance the rental experience for residents, property managers and apartment owners.  
			</p>
		</div>
		<div class='left'>
			<?php echo $this->Html->image('imacLarge.png'); ?>
		</div>
		<div class='clear'></div>
	</div>
	<div class='row'>
		<div class='left' style='margin-right: '>
			<?php echo $this->Html->image('stopWatch.jpg'); ?>
		</div>
		<div class='left two-thirds'>
			<h2>Save Time</h2>
			<p>
				RentSquare saves you time on mundane administrative tasks and allows you to focus on more important things, like executing your business plan. Don’t waste any more time manually processing rent payments and work orders, or making unnecessary trips to the bank. Let RentSquare work for you.  
			</p>
		</div>
		<div class='clear'></div>
	</div>
	<div class='row'>
		<div class='left two-thirds'>
			<h2>Reduce Costs</h2>
			<p>
				Aside from saving you money on paper and printer ink, RentSquare enables you to work much for efficiently, allowing you to handle much more with fewer personnel.
			</p>
		</div>
		<div class='left'>
			<?php echo $this->Html->image('piggy.jpg'); ?>
		</div>
		<div class='clear'></div>
	</div>
	<div class='row'>
		<div class='left'>
			<?php echo $this->Html->image('sink.jpg'); ?>
		</div>
		<div class='left two-thirds'>
			<h2>Improve Your Amenities</h2>
			<p>
				RentSquare makes your building more desirable, offering your residents the additional amenities of our online resident portal. Residents can pay their rent and order maintenance requests online. RentSquare also offers residents unique services such as  
			</p>
		</div>
		<div class='clear'></div>
	</div>
	<div class='row'>
		<div class='left two-thirds'>
			<h2>Go Green</h2>
			<p>
				Reduce your carbon footprint and eliminate paper waste with RentSquare. We will help your building become environmentally responsible.
			</p>
		</div>
		<div class='left'>
			<?php echo $this->Html->image('green.jpg'); ?>
		</div>
		<div class='clear'></div>
	</div>
	<div class='row'>
		<div class='left'>
			<?php echo $this->Html->image('phoneLarge.jpg'); ?>
		</div>
		<div class='left two-thirds'>
			<h2>Mobile Access</h2>
			<p>
				RentSquare makes your building more desirable, offering your residents the additional amenities of our online resident portal. Residents can pay their rent and order maintenance requests online. RentSquare also offers residents unique services such as group mes
			</p>
		</div>
		<div class='clear'></div>
	</div>
	<div class='row'>
		<div class='left two-thirds'>
			<h2>Make Your Life Easier</h2>
			<p>
				RentSquare makes your life easier, eliminating unnecessary phone calls, trips to the bank, or other mundane administrative tasks. RentSquare is a one-stop-shop for all your property management needs. 
			</p>
		</div>
		<div class='left'>
			<?php echo $this->Html->image('puzzle.jpg'); ?>
		</div>
		<div class='clear'></div>
	</div>
</div>

<pre>
<?php
function geocode_lookup($string){
   $string = str_replace (" ", "+", urlencode($string));
   $details_url = "https://maps.googleapis.com/maps/api/geocode/json?address=".$string."&sensor=false&key=AIzaSyCxGUJAu6jz7ACqPkPih8K7h2D6H7eycEA";
 
   $ch = curl_init();
   curl_setopt($ch, CURLOPT_URL, $details_url);
   curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
   $response = json_decode(curl_exec($ch), true);
    
   // If Status Code is ZERO_RESULTS, OVER_QUERY_LIMIT, REQUEST_DENIED or INVALID_REQUEST
   if ($response['status'] != 'OK') {
    return null;
   }

    $geometry = $response['results'][0]['geometry'];
    $longitude = $geometry['location']['lat'];
    $latitude = $geometry['location']['lng'];
 
    curl_close($ch);
       
    $lat_long = array(
        'latitude' => $geometry['location']['lat'],
        'longitude' => $geometry['location']['lng'],
        'location_type' => $geometry['location_type'],
    );
    
    return $lat_long;
 
}
function timezone_lookup($lat_long){

   $details_url = "https://maps.googleapis.com/maps/api/timezone/json?location=".$lat_long['latitude'] . "," .  $lat_long['longitude'] .  "&timestamp=" . time() ."&sensor=false&key=AIzaSyCxGUJAu6jz7ACqPkPih8K7h2D6H7eycEA";

   $ch = curl_init();
   curl_setopt($ch, CURLOPT_URL, $details_url);
   curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
   $response = json_decode(curl_exec($ch), true);
    
   // If Status Code is ZERO_RESULTS, OVER_QUERY_LIMIT, REQUEST_DENIED or INVALID_REQUEST
   if ($response['status'] != 'OK') {
    return null;
   }
    
    
    curl_close($ch);
       
    return $response;
 
}
 
$city = 'New York, NY';
 

$timezone = timezone_lookup(geocode_lookup($city));
$timezoneid = $timezone['timeZoneId'];
$utc_offset = $timezone['rawOffset'];
$daylight_offset = $timezone['dstOffset'];

echo $timezoneid;

?>
</pre>