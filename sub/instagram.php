<?php

/* www/instagram.php */

 

include_once('./_common.php');

 
function curl($url) {

	$ch = curl_init();

	curl_setopt($ch, CURLOPT_URL, $url);

	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);

	$g = curl_exec($ch);

	curl_close($ch);

	return $g;

}


 

function scrape_insta_hash($tag) {

	$insta_source = curl('https://www.instagram.com/explore/tags/'.$tag.'/'); // instagrame tag url
	//echo $insta_source;

	$shards = explode('window._sharedData = ', $insta_source);

	$insta_json = explode(';</script>', $shards[1]); 

	$insta_array = json_decode($insta_json[0], TRUE);

	//print_r2($insta_array);

	return $insta_array; // this return a lot things print it and see what else you need

}

$tag = 'alphacolorkorea'; // tag for which ou want images 

$results_array = scrape_insta_hash($tag);

$limit = 6; // provide the limit thats important because one page only give some images then load more have to be clicked

$image_array= array(); // array to store images.
?>
<ul class="insta_atc">
<?
	for ($i=0; $i < $limit; $i++) { 

		
	 	//$image_data  = '<a href="https://www.instagram.com/p/'.$results_array['entry_data']['TagPage'][0]['tag']['media']['nodes'][$i]['code'].'/" target="_blank"><img src="'.$latest_array['thumbnail_src'].'"></a>'; // thumbnail and same sizes 
		//$image_data  = '<li><a href="https://www.instagram.com/p/'.$results_array['entry_data']['TagPage'][0]['graphql']['hashtag']['edge_hashtag_to_media']['edges'][$i]['node']['shortcode'].'/" target="_blank"><img src="'.$results_array['entry_data']['TagPage'][0]['graphql']['hashtag']['edge_hashtag_to_media']['edges'][$i]['node']['thumbnail_src'].'"></a></li>'; 
		$image_data  = '<li><a href="https://www.instagram.com/p/'.$results_array['entry_data']['TagPage'][0]['graphql']['hashtag']['edge_hashtag_to_media']['edges'][$i]['node']['shortcode'].'/" target="_blank"><img src="'.$results_array['entry_data']['TagPage'][0]['graphql']['hashtag']['edge_hashtag_to_media']['edges'][$i]['node']['thumbnail_src'].'"></a></li>'; 

	 	

		array_push($image_array, $image_data);

	}

	foreach ($image_array as $image) {

		echo $image;// this will echo the images wrap it in div or ul li what ever html structure 

	}

	// for getting all images have to loop function for more pages 

	// for confirmation  you are getting correct images view 

	//https://www.instagram.com/explore/tags/your-tag-name/

?>
</ul>
