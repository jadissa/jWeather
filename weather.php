<?php

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

require dirname(__FILE__).'/vendor/autoload.php';

/**
 *    Register App for use
 *    - https://www.weatherapi.com/
 * 
 *    Check out the API docs if needed
 *    - https://www.weatherapi.com/docs/
 * 
 *    Finally, make any changes to the below options array
 */
$OPTIONS = [
      # see above
      'api_key'               => '',

      # forecast days
      'days_to_fetch'         => 3,

      # text color
      # note that true black will not work. try 020002
      'fg_color'              => '#020002',

      # text font
      'fg_font'               => '/System/Library/Fonts/Supplemental/Brush Script.ttf',

      # text size
      'fg_size'               => 35,

      # text horizontal spacing
      'fg_horiz_spacing'      => 45,

      # forecast spacing
      'fc_horiz_spacing'      => 150,

      # text angle
      'fg_angle'              => 0,

      # f or c
      'heat_unit'             => 'f',

      # mph or kph
      'speed_unit'            => 'mph',

      # rounding specific
      'precision'             => 1,

      # language
      'lang'                  => 'en',

      # X,Y coordinates for location
      'long'                  => '31.846848732688155',
      'lat'                   => '-106.5798192153439',
];

$BITCH_ASS_CODES = [];
$WEATHER_CODES = [
      'sunny'                 => [1000,],
      'cloudy'                => [1003,1006,1009,],
      'fog'                   => [1030,1135,1147,],
      'rain'                  => [1063,1150,1153,1180,1183,1186,1189,1192,1195,1240,1243,1246,1255,1258,1273,1276,],
      'snow'                  => [1066,1114,1117,1210,1213,1216,1219,1222,1225,1255,1258,1279,1282,],
      'sleet'                 => [1069,1168,1171,1198,1201,1204,1207,1249,1252,1072,1264,1261,1237,],
      'lightning'             => [1087,1273,1276,1279,1282,],
];

foreach( $WEATHER_CODES as $weather_desc => $CODES ) {

      foreach ( $CODES as $weather_code ) {

            if( empty( $BITCH_ASS_CODES[ $weather_code ] ) ) {

                  $BITCH_ASS_CODES[ $weather_code ] = [];

            }

            array_push( $BITCH_ASS_CODES[ $weather_code ], $weather_desc );

      }

}
$WEATHER_CODES = $BITCH_ASS_CODES;

$DRAW_FUNCTIONS = [
    'sunny'                   => 'draw_sunny',
    'cloudy'                  => 'draw_cloudy',
    'fog'                     => 'draw_fog',
    'rain'                    => 'draw_rain',
    'snow'                    => 'draw_snow',
    'sleet'                   => 'draw_sleet',
    'lightning'               => 'draw_lightning',
];

// Conditions
$RESPONSE = file_get_contents( dirname(__FILE__).'/weather_conditions.json' );

if( $RESPONSE === false ) {

      exit;
}

$RAW_RESPONSE     = $RESPONSE;

$RAW_RESPONSE     = json_decode( $RAW_RESPONSE, true );

if( empty( $RAW_RESPONSE ) ) {

      exit;
}

foreach( $RAW_RESPONSE as $RESPONSE ) {

      $CONDITIONS[ $RESPONSE['code'] ]    = [
            'code'      => $RESPONSE['code'],
            'day'       => $RESPONSE['day'],
            'night'     => $RESPONSE['night'],
            'icon'      => $RESPONSE['icon'],
      ];

}

// Weather
// This should really be set in php.ini
ini_set( 'date.timezone','America/Los_Angeles' );

# jWeather/vendor/guzzlehttp/guzzle/src/RequestOptions.php
$CLIENT = new GuzzleHttp\Client;
$RESPONSE = $CLIENT->request( 'GET', 'http://api.weatherapi.com/v1/forecast.json', [
      'query' => [
            'key'       => $OPTIONS['api_key'],
            'q'         => $OPTIONS['long'].','.$OPTIONS['lat'],
            'days'      => $OPTIONS['days_to_fetch'],
            'aqi'       => 'yes',
            'alerts'    => 'yes',
            'lang'      => $OPTIONS['lang'],
      ],
] );

if( $RESPONSE->getStatusCode() <> 200 ) {

      exit;
}

$RAW_RESPONSE     = $RESPONSE->getBody();

$RAW_RESPONSE     = json_decode( $RAW_RESPONSE, true );

if( empty( $RAW_RESPONSE ) ) {

      exit;
}

// Language
if( is_file( dirname(__FILE__)."/lang/{$OPTIONS['lang']}/index.php" ) ) {

      require_once dirname(__FILE__)."/lang/{$OPTIONS['lang']}/index.php";
      $LANG = $LANG[ $OPTIONS['lang'] ];

} else {

      require_once dirname(__FILE__).'/lang/en/index.php';
      $LANG = $LANG['en'];

}

#print'<pre>';print_r( $RAW_RESPONSE );print'</pre>';exit;
// Review current day
$CURRENT_DAY      = $RAW_RESPONSE['forecast']['forecastday'][0];
$CURRENT_HOUR     = $CURRENT_DAY['hour'][ date( 'G' ) ];

if( isset( $CURRENT_DAY['hour'][ date( 'G' ) + 1 ] ) ) {

      $NEXT_HOUR  = $CURRENT_DAY['hour'][ date( 'G' ) + 1 ];

} else {

      $NEXT_DAY   = $RAW_RESPONSE['forecast']['forecastday'][1];
      $NEXT_HOUR  = $NEXT_DAY['hour'][0];

}

// Overview 
$CONDITION        = [
      'tz_id'     => 0,
      'location'  => '',
      'raw_temp'  => 0,
      'temp'      => '',
      'img'       => '',
      'desc'      => '',
      'humidity'   => 0,
      'cloud'     => 0,
      'wind'      => 0,
      'alert'     => '',
];

if( $NEXT_HOUR['temp_f'] > $CURRENT_HOUR['temp_f'] ) {

      $direction  = $LANG['increasing'];

} else $direction = $LANG['decreasing'];

$CONDITION_INFO         = $CONDITIONS[ $RAW_RESPONSE['current']['condition']['code'] ];

$CONDITION['code']      = $CONDITION_INFO['icon'];

if( $RAW_RESPONSE['current']['is_day'] ) {

      $CONDITION['desc']      = $CONDITION_INFO['day'].", {$LANG['feels_like']} ".$RAW_RESPONSE['current']['feelslike_'.$OPTIONS['heat_unit'] ] . '°';

} else $CONDITION['desc']     = $CONDITION_INFO['night'].", {$LANG['feels_like']} ". $RAW_RESPONSE['current']['feelslike_'.$OPTIONS['heat_unit'] ] . '°';

$CONDITION['tz_id']     = $RAW_RESPONSE['location']['tz_id'];
$CONDITION['location']  = $RAW_RESPONSE['location']['name'];
$CONDITION['raw_temp']  = $RAW_RESPONSE['current']['temp_'.$OPTIONS['heat_unit'] ];
$CONDITION['temp']      = $RAW_RESPONSE['current']['temp_'.$OPTIONS['heat_unit'] ].'°';
$CONDITION['humidity']  = $RAW_RESPONSE['current']['humidity'].'%'." {$LANG['humidity']} ";
$CONDITION['cloud']     = $RAW_RESPONSE['current']['cloud'].'%'." {$LANG['cloud_coverage']} ";
$CONDITION['wind']      = $RAW_RESPONSE['current']['wind_dir']. ', '.$RAW_RESPONSE['current']['wind_'.$OPTIONS['speed_unit'] ].' '.$OPTIONS['speed_unit'];
$CONDITION['direction'] = $direction;

if( $RAW_RESPONSE['current']['gust_' . $OPTIONS['speed_unit'] ] > 0 ) {

      $CONDITION['wind'] = "{$CONDITION['wind']}, {$LANG['gusts_at']} ".$RAW_RESPONSE['current']['gust_'.$OPTIONS['speed_unit'] ]. ' '.$OPTIONS['speed_unit'];

}

// Alert
foreach( $RAW_RESPONSE['alerts'] as $ALERT ) {

      foreach( $ALERT  as $ALERT_DATA ) {

            $CONDITION['alert'] = $LANG['alert'].': '.$ALERT_DATA['event'];
      }
}

// Response
$RESPONSE_DATA = [
      'heading'   => ucfirst( "{$LANG['weather_for']}" )." {$CONDITION['location']}",
      'alert'     => $CONDITION['alert'],
      'current'   => [
            'raw_temp'  => $CONDITION['raw_temp'],
            'temp'      => $CONDITION['temp'],
            'desc'      => $CONDITION['desc'],
            'wind'      => ucfirst( "{$LANG['wind']}" )." {$CONDITION['wind']}",
            'cloud'     => $CONDITION['cloud'],
            'humidity'  => $CONDITION['humidity'],
            'direction' => $CONDITION['direction'],
      ],
      'forecast'  => [

      ],
];

// Highs/Lows/images
foreach( $RAW_RESPONSE['forecast']['forecastday'] as $i => $DAY_FORECAST ) {

      $RESPONSE_DATA['forecast'][ $i ] = [
            'dayofweek' => date( 'D',strtotime( $DAY_FORECAST['date'] ) ),
            'img'       => '',
            'high'      => 0,
            'low'       => 0,
      ];

      $CURRENT_WEATHER_CODITIONS    = $WEATHER_CODES[ $DAY_FORECAST['day']['condition']['code'] ];
      array_push($CURRENT_WEATHER_CODITIONS, 'sunny' );

      // Create image for each day
      $image = imagecreatetruecolor( 64,64 );
      imagealphablending($image, false); // Disable alpha blending
      $transparent = imagecolorallocatealpha($image, 0, 0, 0, 127); // Allocate a transparent color
      imagefill($image, 0, 0, $transparent); // Fill the background with transparency
      imagesavealpha($image, true); // Save the alpha channel

      foreach( $DRAW_FUNCTIONS as $condition => $function ) {

          if( in_array( $condition,$CURRENT_WEATHER_CODITIONS ) ) {

                  $image = $function( $image );

                  $image_path = dirname(__FILE__).'/images/'.$condition.'.png';
                  imagepng( $image,$image_path );

                  $RESPONSE_DATA['forecast'][ $i ]['img'] = $image_path;

            }

            imagedestroy($image);

      }

      foreach( $DAY_FORECAST['hour'] as $HOUR_FORECAST ) {

            if( $HOUR_FORECAST['temp_' . $OPTIONS['heat_unit'] ] > $RESPONSE_DATA['forecast'][ $i ]['high'] ) {

                  $RESPONSE_DATA['forecast'][ $i ]['high']  = number_format( $HOUR_FORECAST['temp_'.$OPTIONS['heat_unit'] ],$OPTIONS['precision'] );

            } else $RESPONSE_DATA['forecast'][ $i ]['low']  = number_format( $HOUR_FORECAST['temp_'.$OPTIONS['heat_unit'] ],$OPTIONS['precision'] );

      }

}

header ('Content-Type: image/png');
$width      = 1092;
$height     = 448;
$canvas     = @imagecreatetruecolor( $width,$height );

// Canvas foreground
list( $text_r,$text_g,$text_b ) = sscanf( $OPTIONS['fg_color'],"#%02x%02x%02x" );
$text_color = imagecolorallocate( $canvas,$text_r,$text_g,$text_b );
$text_angle = 0;

// Canvas background
imagesavealpha( $canvas,true );
$color = imagecolorallocatealpha( $canvas,0,0,0,127 );
imagefill( $canvas,0,0,$color );

// Heading text
$X    = 45;
$Y    = 60;
imagettftext( $canvas,$OPTIONS['fg_size']+ 5,$OPTIONS['fg_angle'],$X,$Y,$text_color,$OPTIONS['fg_font'],$RESPONSE_DATA['heading'] );

// Heading underline
$X1   = 20;
$X2   = ( $width - 20 );
$Y1   = $Y + 10;
$Y2   = $Y1;
imagesetthickness( $canvas,2 );
imageline($canvas,$X1,$Y1,$X2,$Y2,$text_color );
imagesetthickness( $canvas,1 );

# Current temp
$X    = $X;
$Y    = $Y + 100;
imagettftext( $canvas,$OPTIONS['fg_size']+45,$OPTIONS['fg_angle'],$X,$Y,$text_color,$OPTIONS['fg_font'],$RESPONSE_DATA['current']['temp'] );
$X    = $X;
$Y    = $Y + $OPTIONS['fg_horiz_spacing'];
imagettftext( $canvas,$OPTIONS['fg_size'],$OPTIONS['fg_angle'],$X,$Y,$text_color,$OPTIONS['fg_font'],$RESPONSE_DATA['current']['direction'] );

# Current desc
$X    = $X;
$Y    = $Y + $OPTIONS['fg_horiz_spacing'];
imagettftext( $canvas,$OPTIONS['fg_size']-5,$OPTIONS['fg_angle'],$X,$Y,$text_color,$OPTIONS['fg_font'],$RESPONSE_DATA['current']['desc'] );
$X    = $X;
$Y    = $Y + $OPTIONS['fg_horiz_spacing'];
imagettftext( $canvas,$OPTIONS['fg_size']-5,$OPTIONS['fg_angle'],$X,$Y,$text_color,$OPTIONS['fg_font'],$RESPONSE_DATA['current']['wind'] );
$X    = $X;
$Y    = $Y + $OPTIONS['fg_horiz_spacing'];
imagettftext( $canvas,$OPTIONS['fg_size']-5,$OPTIONS['fg_angle'],$X,$Y,$text_color,$OPTIONS['fg_font'],$RESPONSE_DATA['current']['cloud'] );
$X    = $X;
$Y    = $Y + $OPTIONS['fg_horiz_spacing'];
imagettftext( $canvas,$OPTIONS['fg_size']-5,$OPTIONS['fg_angle'],$X,$Y,$text_color,$OPTIONS['fg_font'],$RESPONSE_DATA['current']['humidity'] );
$X    = $X;
$Y    = $Y + $OPTIONS['fg_horiz_spacing'];
imagettftext( $canvas,$OPTIONS['fg_size']-5,$OPTIONS['fg_angle'],$X,$Y,$text_color,$OPTIONS['fg_font'],$RESPONSE_DATA['alert'] );

# Today heading
$X    = $width / 2;
$Y    = 118;

foreach( $RESPONSE_DATA['forecast'] as $index => $WEATHER_DATA ) {

      GenNextDayWeather( $WEATHER_DATA,$X,$Y,$OPTIONS,$canvas,$text_r,$text_g,$text_b );

}

# Output
imagepng( $canvas,dirname(__FILE__).'/out.png' );
imagedestroy( $canvas );

function GenNextDayWeather( $WEATHER_DATA,&$X,$Y,$OPTIONS,$canvas,$r,$g,$b ) {
      $X                = $X + $OPTIONS['fc_horiz_spacing'];
      $Y                = $Y + 10;

      # text color
      $text_color = imagecolorallocate( $canvas,$r,$g,$b );

      # day text
      imagettftext( $canvas,$OPTIONS['fg_size'],$OPTIONS['fg_angle'],$X,$Y,$text_color,$OPTIONS['fg_font'],$WEATHER_DATA['dayofweek'] );

      # Draw temp image
      $current_img      = imagecreatefrompng( $WEATHER_DATA['img'] );

      $src_x            = 0;
      $src_y            = 0;
      $dst_width        = 64;
      $img_height       = 64;
      imagecopy( $canvas,$current_img,$X-5,$Y,$src_x,$src_y,$dst_width,$img_height );

      # Draw high temperature
      $Y    = $Y + $img_height + 40;
      imagettftext( $canvas,$OPTIONS['fg_size']-5,$OPTIONS['fg_angle'],$X,$Y,$text_color,$OPTIONS['fg_font'],$WEATHER_DATA['high'].' °' );

      $ORIG_X           = $X;
      $X                = $X + 14;
      $Y                = $Y + 28;
      $bar_height       = 100;
      $bar_width        = 22;
      $today_bar        = imagecreatetruecolor( $bar_width,$bar_height );
      $background_color = imagecolorallocate( $today_bar,$r,$g,$b );
      $black            = imagecolorallocate( $today_bar,0,0,0 );
      imagecolortransparent( $today_bar,$black );

      # Draw temperature bar
      ImageRectangleWithRoundedCorners( $today_bar,0,0,$bar_width,round( $WEATHER_DATA['high'] ),10,$background_color );
      imagecopy( $canvas,$today_bar,$X,$Y,$src_x,$src_y,$bar_width,$bar_height );

      # Draw low temperature
      $X    = $ORIG_X;
      $Y    = $Y + 150;
      imagettftext( $canvas,$OPTIONS['fg_size']-5,$OPTIONS['fg_angle'],$X,$Y,$text_color,$OPTIONS['fg_font'],$WEATHER_DATA['low'].' °' );
}

function ImageRectangleWithRoundedCorners( &$im,$x1,$y1,$x2,$y2,$radius,$color ) {
      // draw rectangle without corners
      imagefilledrectangle( $im,$x1+$radius,$y1,$x2-$radius,$y2,$color );
      imagefilledrectangle( $im,$x1,$y1+$radius,$x2,$y2-$radius,$color );

      // draw circled corners
      imagefilledellipse( $im,$x1+$radius,$y1+$radius,$radius*2,$radius*2,$color );
      imagefilledellipse( $im,$x2-$radius,$y1+$radius,$radius*2,$radius*2,$color );
      imagefilledellipse( $im,$x1+$radius,$y2-$radius,$radius*2,$radius*2,$color );
      imagefilledellipse( $im,$x2-$radius,$y2-$radius,$radius*2,$radius*2,$color );
}

function draw_sunny( $image ) {
      // Create a true-color image with a transparent background
      $image = imagecreatetruecolor( 64,64 );
      imagealphablending($image, false); // Disable alpha blending
      $transparent = imagecolorallocatealpha($image, 0, 0, 0, 127); // Allocate a transparent color
      imagefill($image, 0, 0, $transparent); // Fill the background with transparency
      imagesavealpha($image, true); // Save the alpha channel

      $yellow = imagecolorallocate($image, 255, 223, 0);
      imagefilledellipse( $image, 32, 32, 40, 40, $yellow );

      return $image;
}


function draw_cloudy( $image ) {
      // Create a true-color image with a transparent background
      $image = imagecreatetruecolor( 64,64 );
      imagealphablending($image, false); // Disable alpha blending
      $transparent = imagecolorallocatealpha($image, 0, 0, 0, 127); // Allocate a transparent color
      imagefill($image, 0, 0, $transparent); // Fill the background with transparency
      imagesavealpha($image, true); // Save the alpha channel

      $gray = imagecolorallocate($image, 150, 150, 150);
      $dark_gray = imagecolorallocate($image, 100, 100, 100);

      // Draw multiple overlapping circles for a cloud shape
      imagefilledellipse($image, 22, 36, 30, 20, $gray);
      imagefilledellipse($image, 42, 34, 30, 24, $gray);
      imagefilledellipse($image, 32, 28, 36, 26, $gray);

      // Give it a slightly darker bottom for definition
      imagefilledellipse($image, 22, 40, 30, 20, $dark_gray);
      imagefilledellipse($image, 42, 38, 30, 24, $dark_gray);

      return $image;
}

function draw_rain( $image ) {
      // Create a true-color image with a transparent background
      $image = imagecreatetruecolor( 64,64 );
      imagealphablending($image, false); // Disable alpha blending
      $transparent = imagecolorallocatealpha($image, 0, 0, 0, 127); // Allocate a transparent color
      imagefill($image, 0, 0, $transparent); // Fill the background with transparency
      imagesavealpha($image, true); // Save the alpha channel

      $blue = imagecolorallocate($image, 0, 128, 255);

      // Draw multiple diagonal lines for raindrops
      $raindrop_count = 10;
      for ($i = 0; $i < $raindrop_count; $i++) {
            $x = mt_rand(10, 54);
            $y = mt_rand(40, 60);
            imageline($image, $x, $y, $x + 5, $y - 10, $blue);
      }

      return $image;
}

function draw_snow( $image ) {
      // Create a true-color image with a transparent background
      $image = imagecreatetruecolor( 64,64 );
      imagealphablending($image, false); // Disable alpha blending
      $transparent = imagecolorallocatealpha($image, 0, 0, 0, 127); // Allocate a transparent color
      imagefill($image, 0, 0, $transparent); // Fill the background with transparency
      imagesavealpha($image, true); // Save the alpha channel

      $white = imagecolorallocate($image, 255, 255, 255);

      // Draw snowflakes as small circles and lines
      $flake_count = 15;
      for ($i = 0; $i < $flake_count; $i++) {
            $x = mt_rand(10, 54);
            $y = mt_rand(40, 60);
            imagefilledellipse($image, $x, $y, 3, 3, $white);
      }

      return $image;
}

function draw_sleet( $image ) {
      // Draw a mix of snow (white dots) and rain (blue lines)
      draw_snow($image);
      draw_rain($image);

      return $image;
}

function draw_fog( $image ) {
    // Draw horizontal, translucent gray bands for fog
    $fog_color = imagecolorallocatealpha($image, 200, 200, 200, 60); // Semi-transparent
    for ($y = 45; $y < 64; $y += 5) {
        imagefilledrectangle($image, 0, $y, $width, $y + 2, $fog_color);
    }

    return $image;
}

function draw_lightning( $image ) {

      $white = imagecolorallocate($image, 255, 255, 255);

      // Draw a zigzag line for lightning
      $points = [
            32, 25,
            40, 40,
            30, 40,
            38, 55,
      ];
      imageline($image, $points[0], $points[1], $points[2], $points[3], $white);
      imageline($image, $points[2], $points[3], $points[4], $points[5], $white);
      imageline($image, $points[4], $points[5], $points[6], $points[7], $white);

      return $image;
}