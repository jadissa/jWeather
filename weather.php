<?php
require './vendor/autoload.php';

/**
 *    Register App for use
 *    - Visit weatherapi.com
 * 
 *    Generate your API key
 *    Make your environment aware of your API key
 *    - echo 'export WEATHER_KEY="API_KEY"' >>~/.bash_profile
 *    - re-launch your terminal
 *    
 *    ZSH users will need to follow an additional step
 *    - echo 'source ~/.bash_profile' >>~/.zshrc
 * 
 *    Finally, make any changes to the below options array
 *    - optional step
 */
$OPTIONS = [
      # number days to forecast
      'days_to_fetch'   => 7,

      # text color
      'fg_color'        => '#c62714',

      # text font
      'fg_font'         => '/System/Library/Fonts/Supplemental/Arial Bold.ttf',

      # text size
      'fg_size'         => 20,

      # text angle
      'fg_angle'        => 0,

      # f or c
      'heat_unit'       => 'f',

      # mph or kph
      'speed_unit'      => 'mph',

      # rounding specific
      'precision'       => 1,
];

$CLIENT = new GuzzleHttp\Client;

// Conditions
$RESPONSE = file_get_contents( './weather_conditions.json' );

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

// Images
$IMAGES     = [
      'day'       => [],
      'night'     => [],
];

// Day images
$FILES      = scandir( './images/64x64/day',SCANDIR_SORT_ASCENDING );

if( empty( $FILES ) ) {

      exit;
}

foreach( $FILES as $i => $file ) {

      if( $file == '.' or $file == '..' ) {

            continue;

      }

      $IMAGES[1][ pathinfo( $file,PATHINFO_FILENAME ) ]     = "./images/64x64/day/{$file}";

}

// Night images
$FILES      = scandir( './images/64x64/night',SCANDIR_SORT_ASCENDING );

if( empty( $FILES ) ) {

      exit;
}

foreach( $FILES as $i => $file ) {

      if( $file == '.' or $file == '..' ) {

            continue;

      }

      $IMAGES[0][ pathinfo( $file,PATHINFO_FILENAME ) ]     = "./images/64x64/night/{$file}";

}

// Weather
# jWeather/vendor/guzzlehttp/guzzle/src/RequestOptions.php
$RESPONSE = $CLIENT->request( 'GET', 'http://api.weatherapi.com/v1/forecast.json', [
      'query' => [
            'key'       => $_SERVER['WEATHER_KEY'],
            'q'         => 'auto:ip',
            'days'      => $OPTIONS['days_to_fetch'],
            'aqi'       => 'yes',
            'alerts'    => 'yes',
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
//print'<pre>';print_r( $RAW_RESPONSE );print'</pre>';exit;
// Review current day
$CURRENT_DAY      = $RAW_RESPONSE['forecast']['forecastday'][0];
$CURRENT_HOUR     = $CURRENT_DAY['hour'][ date( 'G' ) ];
$NEXT_HOUR        = $CURRENT_DAY['hour'][ date( 'G' ) + 1 ];

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
];

if( $NEXT_HOUR['temp_f'] > $CURRENT_HOUR['temp_f'] ) {

      $direction  = 'increasing';

} else $direction = 'decreasing';

$CONDITION_INFO         = $CONDITIONS[ $RAW_RESPONSE['current']['condition']['code'] ];

$CONDITION['code']      = $CONDITION_INFO['icon'];

if( $RAW_RESPONSE['current']['is_day'] ) {

      $CONDITION['desc']      = $CONDITION_INFO['day'] . ', feels like ' . $RAW_RESPONSE['current']['feelslike_' . $OPTIONS['heat_unit'] ] . '°';

} else $CONDITION['desc']     = $CONDITION_INFO['night'] . ', feels like ' . $RAW_RESPONSE['current']['feelslike_' . $OPTIONS['heat_unit'] ] . '°';

$CONDITION['tz_id']     = $RAW_RESPONSE['location']['tz_id'];
$CONDITION['location']  = $RAW_RESPONSE['location']['name'];
$CONDITION['raw_temp']  = $RAW_RESPONSE['current']['temp_' . $OPTIONS['heat_unit'] ];
$CONDITION['img']       = $IMAGES[ $RAW_RESPONSE['current']['is_day'] ][ $CONDITION['code'] ];
$CONDITION['temp']      = $RAW_RESPONSE['current']['temp_' . $OPTIONS['heat_unit'] ] . '°';
$CONDITION['humidity']  = $RAW_RESPONSE['current']['humidity'] . '%' . ' humidity';
$CONDITION['cloud']     = $RAW_RESPONSE['current']['cloud'] . '%' . ' cloud coverage';
$CONDITION['wind']      = $RAW_RESPONSE['current']['wind_dir'] . ', ' . $RAW_RESPONSE['current']['wind_' . $OPTIONS['speed_unit'] ] . ' ' . $OPTIONS['speed_unit'];

if( $RAW_RESPONSE['current']['gust_' . $OPTIONS['speed_unit'] ] > 0 ) {

      $CONDITION['wind'] = $CONDITION['wind'] . ' with gusts at ' . $RAW_RESPONSE['current']['gust_' . $OPTIONS['speed_unit'] ] . ' ' . $OPTIONS['speed_unit'];
}

// Response
$RESPONSE_DATA = [
      'heading'   => "Weather for {$CONDITION['location']}",
      'current'   => [
            'img'       => $CONDITION['img'],
            'raw_temp'  => $CONDITION['raw_temp'],
            'temp'      => $CONDITION['temp'],
            'desc'      => $CONDITION['desc'],
            'wind'      => "Wind {$CONDITION['wind']}",
            'cloud'     => $CONDITION['cloud'],
            'humidity'  => $CONDITION['humidity'],
      ],
      'forecast'  => [

      ],
];

// Highs/Lows
foreach( $RAW_RESPONSE['forecast']['forecastday'] as $i => $DAY_FORECAST ) {

      $RESPONSE_DATA['forecast'][ $i ] = [
            'dayofweek' => date( 'D',strtotime( $DAY_FORECAST['date'] ) ),
            'img'       => '',
            'high'      => 0,
            'low'       => 0,
      ];

      if( !( $i > 0 ) ) {

            $RESPONSE_DATA['forecast'][ $i ]['img']   = $IMAGES[ $RAW_RESPONSE['current']['is_day'] ][ $CONDITIONS[ $DAY_FORECAST['day']['condition']['code'] ]['icon'] ];

      } else {

            $RESPONSE_DATA['forecast'][ $i ]['img']   = $IMAGES[1][ $CONDITIONS[ $DAY_FORECAST['day']['condition']['code'] ]['icon'] ];
      }

      foreach( $DAY_FORECAST['hour'] as $HOUR_FORECAST ) {

            if( $HOUR_FORECAST['temp_' . $OPTIONS['heat_unit'] ] > $RESPONSE_DATA['forecast'][ $i ]['high'] ) {

                  $RESPONSE_DATA['forecast'][ $i ]['high']  = number_format( $HOUR_FORECAST['temp_' . $OPTIONS['heat_unit'] ],$OPTIONS['precision'] );

            } else $RESPONSE_DATA['forecast'][ $i ]['low']  = number_format( $HOUR_FORECAST['temp_' . $OPTIONS['heat_unit'] ],$OPTIONS['precision'] );

      }

}

/*
// Test data
$RESPONSE_DATA = [
      'heading'   => "Weather for some cool place",
      'current'   => [
            'img'       => './images/64x64/night/116.png',
            'raw_temp'  => '84',
            'temp'      => '84°',
            'desc'      => 'Partly cloudy, feels like 76°',
            'wind'      => 'Wind W, 5 mph',
            'cloud'     => '10% cloud coverage',
            'humidity'  => '6% humidity',
      ],
      'forecast'  => [
            [
                  'dayofweek' => 'Sun',
                  'img'       => './images/64x64/night/116.png',
                  'high'      => number_format( 86.8,$OPTIONS['precision'] ),
                  'low'       => number_format( 59.8,$OPTIONS['precision'] ),
            ],
            [
                  'dayofweek' => 'Mon',
                  'img'       => './images/64x64/night/113.png',
                  'high'      => number_format( 78.2,$OPTIONS['precision'] ),
                  'low'       => number_format( 42.5,$OPTIONS['precision'] ),
            ],
            [
                  'dayofweek' => 'Tue',
                  'img'       => './images/64x64/night/116.png',
                  'high'      => number_format( 83.4,$OPTIONS['precision'] ),
                  'low'       => number_format( 58,$OPTIONS['precision'] ),
            ],
      ],
];
*/

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

# Current desc
$X    = $X;
$Y    = $Y + 35;
imagettftext( $canvas,$OPTIONS['fg_size']-5,$OPTIONS['fg_angle'],$X,$Y,$text_color,$OPTIONS['fg_font'],$RESPONSE_DATA['current']['desc'] );
$X    = $X;
$Y    = $Y + 22;
imagettftext( $canvas,$OPTIONS['fg_size']-5,$OPTIONS['fg_angle'],$X,$Y,$text_color,$OPTIONS['fg_font'],$RESPONSE_DATA['current']['wind'] );
$X    = $X;
$Y    = $Y + 22;
imagettftext( $canvas,$OPTIONS['fg_size']-5,$OPTIONS['fg_angle'],$X,$Y,$text_color,$OPTIONS['fg_font'],$RESPONSE_DATA['current']['cloud'] );
$X    = $X;
$Y    = $Y + 22;
imagettftext( $canvas,$OPTIONS['fg_size']-5,$OPTIONS['fg_angle'],$X,$Y,$text_color,$OPTIONS['fg_font'],$RESPONSE_DATA['current']['humidity'] );

# Today heading
$X    = 400;
$Y    = 118;
foreach( $RESPONSE_DATA['forecast'] as $index => $WEATHER_DATA ) {

      GenNextDayWeather( $WEATHER_DATA,$X,$Y,$OPTIONS,$canvas,$text_r,$text_g,$text_b );
}

# Output
imagepng( $canvas,'out.png');
imagedestroy( $canvas );

function GenNextDayWeather( $WEATHER_DATA,&$X,$Y,$OPTIONS,$canvas,$r,$g,$b ) {
      $X                = $X + 100;
      $Y                = $Y + 10;

      # text color
      $text_color = imagecolorallocate( $canvas,$r,$g,$b );

      # day text
      imagettftext( $canvas,$OPTIONS['fg_size'],$OPTIONS['fg_angle'],$X,$Y,$text_color,$OPTIONS['fg_font'],$WEATHER_DATA['dayofweek'] );

      # day image
      $current_img      = imagecreatefrompng( $WEATHER_DATA['img'] );
      $src_x            = 0;
      $src_y            = 0;
      $dst_width        = 64;
      $img_height       = 64;
      imagecopy( $canvas,$current_img,$X-5,$Y,$src_x,$src_y,$dst_width,$img_height );

      # day barometer;
      $Y    = $Y + $img_height + 20;
      imagettftext( $canvas,$OPTIONS['fg_size']-5,$OPTIONS['fg_angle'],$X,$Y,$text_color,$OPTIONS['fg_font'],$WEATHER_DATA['high'] );

      $ORIG_X           = $X;
      $X                = $X + 14;
      $Y                = $Y + 28;
      $bar_height       = 130;
      $bar_width        = 22;
      $today_bar        = imagecreatetruecolor( $bar_width,$bar_height );
      $background_color = imagecolorallocate( $today_bar,$r,$g,$b );
      $black            = imagecolorallocate( $today_bar,0,0,0 );
      imagecolortransparent( $today_bar,$black );
      ImageRectangleWithRoundedCorners( $today_bar,0,0,$bar_width,round( $WEATHER_DATA['high'] ),10,$background_color );
      imagecopy( $canvas,$today_bar,$X,$Y,$src_x,$src_y,$bar_width,$bar_height );

      $X    = $ORIG_X;
      $Y    = $Y + $bar_height + 28;
      imagettftext( $canvas,$OPTIONS['fg_size']-5,$OPTIONS['fg_angle'],$X,$Y,$text_color,$OPTIONS['fg_font'],$WEATHER_DATA['low'] );
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