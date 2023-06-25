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
      'width'     => 1092,
      'height'    => 448,
      'bg_color'  => '',
      'fg_color'  => '#FFFFFF',
      'fg_font'   => '/System/Library/Fonts/Supplemental/Arial Bold.ttf',
      'fg_size'   => 20,
      'fg_angle'  => 0,
      'degrees'   => '°',
      'percent'   => '%',
      'heat_unit' => 'f',     # f or c
      'speed_unit'=> 'mph',   # mph or kph
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
            'days'      => 3,
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

// Review current day
$CURRENT_DAY      = $RAW_RESPONSE['forecast']['forecastday'][0];
$CURRENT_HOUR     = $CURRENT_DAY['hour'][ date( 'G' ) ];
$NEXT_HOUR        = $CURRENT_DAY['hour'][ date( 'G' ) + 1 ];

// Overview 
$CONDITION        = [
      'tz_id'     => 0,
      'location'  => '',
      'img'       => '',
      'desc'      => '',
      'temp'      => 0,
      'humidty'   => 0,
      'cloud'     => 0,
      'wind'      => 0,
];

if( $NEXT_HOUR['temp_f'] > $CURRENT_HOUR['temp_f'] ) {

      $direction  = 'increasing';

} else $direction = 'decreasing';

$CONDITION_INFO         = $CONDITIONS[ $RAW_RESPONSE['current']['condition']['code'] ];

$CONDITION['code']      = $CONDITION_INFO['icon'];

if( $RAW_RESPONSE['current']['is_day'] ) {

      $CONDITION['desc']      = $CONDITION_INFO['day'] . ', feels like ' . $RAW_RESPONSE['current']['feelslike_' . $OPTIONS['heat_unit'] ] . ' ' . $OPTIONS['degrees'];

} else $CONDITION['desc']     = $CONDITION_INFO['night'] . ', feels like ' . $RAW_RESPONSE['current']['feelslike_' . $OPTIONS['heat_unit'] ] . ' ' . $OPTIONS['degrees'];

$CONDITION['tz_id']     = $RAW_RESPONSE['location']['tz_id'];
$CONDITION['location']  = $RAW_RESPONSE['location']['name'];
$CONDITION['raw_temp']  = $RAW_RESPONSE['current']['temp_' . $OPTIONS['heat_unit'] ];
$CONDITION['img']       = $IMAGES[ $RAW_RESPONSE['current']['is_day'] ][ $CONDITION['code'] ];
$CONDITION['temp']      = $RAW_RESPONSE['current']['temp_' . $OPTIONS['heat_unit'] ] . '' . $OPTIONS['degrees'];
$CONDITION['humidty']   = $RAW_RESPONSE['current']['humidity'] . '' . $OPTIONS['percent'];
$CONDITION['cloud']     = $RAW_RESPONSE['current']['cloud'] . '' . $OPTIONS['percent'];
$CONDITION['wind']      = $RAW_RESPONSE['current']['wind_dir'] . ', ' . $RAW_RESPONSE['current']['wind_' . $OPTIONS['speed_unit'] ] . ' ' . $OPTIONS['speed_unit'];

// Response
$RESPONSE_DATA = [
      'heading'   => "Weather for {$CONDITION['location']}",
      'current'   => [
            'img'       => $CONDITION['img'],
            'raw_temp'  => $CONDITION['raw_temp'],
            'temp'      => $CONDITION['temp'],
            'desc'      => $CONDITION['desc'],
            'wind'      => "Wind {$CONDITION['wind']}",
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

                  $RESPONSE_DATA['forecast'][ $i ]['high']  = $HOUR_FORECAST['temp_' . $OPTIONS['heat_unit'] ];

            } else $RESPONSE_DATA['forecast'][ $i ]['low']  = $HOUR_FORECAST['temp_' . $OPTIONS['heat_unit'] ];

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
            'desc'      => 'Partly cloudy, feels like 76 °',
            'wind'      => 'Wind W, 5 mph',
      ],
      'forecast'  => [
            [
                  'dayofweek' => 'Sun',
                  'img'       => './images/64x64/night/116.png',
                  'high'      => 86.8,
                  'low'       => 59.8,
            ],
            [
                  'dayofweek' => 'Mon',
                  'img'       => './images/64x64/night/113.png',
                  'high'      => 78.2,
                  'low'       => 42.5,
            ],
            [
                  'dayofweek' => 'Tue',
                  'img'       => './images/64x64/night/116.png',
                  'high'      => 83.4,
                  'low'       => 58,
            ],
      ],
];
*/

header ('Content-Type: image/png');
$canvas = @imagecreatetruecolor( $OPTIONS['width'],$OPTIONS['height'] );

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
$X2   = ( $OPTIONS['width'] - 20 );
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
$X    = $X - 10;
$Y    = $Y + 40;
imagettftext( $canvas,$OPTIONS['fg_size'],$OPTIONS['fg_angle'],$X,$Y,$text_color,$OPTIONS['fg_font'],$RESPONSE_DATA['current']['desc'] );
$X    = $X;
$Y    = $Y + 40;
imagettftext( $canvas,$OPTIONS['fg_size'],$OPTIONS['fg_angle'],$X,$Y,$text_color,$OPTIONS['fg_font'],$RESPONSE_DATA['current']['wind'] );





# Today heading
$X    = 600;
$Y    = 118;
imagettftext( $canvas,$OPTIONS['fg_size'],$OPTIONS['fg_angle'],$X,$Y,$text_color,$OPTIONS['fg_font'],'Today' );

# Today image
$X                = $X - 10;
$Y                = $Y + 10;
$current_img      = imagecreatefrompng( $RESPONSE_DATA['forecast'][0]['img'] );
$src_x            = 0;
$src_y            = 0;
$dst_width        = 64;
$dst_height       = 64;
imagecopy( $canvas,$current_img,$X,$Y,$src_x,$src_y,$dst_width,$dst_height );

# Today barometer
$X    = 600 + 4;
$Y    = $Y + $dst_height + 20;
imagettftext( $canvas,$OPTIONS['fg_size'],$OPTIONS['fg_angle'],$X,$Y,$text_color,$OPTIONS['fg_font'],$RESPONSE_DATA['forecast'][0]['high'] );

$X                = 600 + 14;
$Y                = $Y + 20;
$bar_height       = $RESPONSE_DATA['forecast'][0]['high'];
$today_bar        = imagecreatetruecolor( 22,90 );
$white            = imagecolorallocate( $today_bar,255,255,255 );
imagefill( $today_bar,0,0,$white );
imagecopy( $canvas,$today_bar,$X,$Y,$src_x,$src_y,22,90 );

$X    = 600 + 6;
$Y    = $Y + 120;
imagettftext( $canvas,$OPTIONS['fg_size'],$OPTIONS['fg_angle'],$X,$Y,$text_color,$OPTIONS['fg_font'],$RESPONSE_DATA['forecast'][0]['low'] );





# 2nd day heading
$X    = 700;
$Y    = 118;
imagettftext( $canvas,$OPTIONS['fg_size'],$OPTIONS['fg_angle'],$X,$Y,$text_color,$OPTIONS['fg_font'],$RESPONSE_DATA['forecast'][1]['dayofweek'] );

# 2nd day image
$X                = $X - 10;
$Y                = $Y + 10;
$current_img      = imagecreatefrompng( $RESPONSE_DATA['forecast'][1]['img'] );
$src_x            = 0;
$src_y            = 0;
$dst_width        = 64;
$dst_height       = 64;
imagecopy( $canvas,$current_img,$X,$Y,$src_x,$src_y,$dst_width,$dst_height );

# 2nd day barometer
$X    = 700 + 4;
$Y    = $Y + $dst_height + 20;
imagettftext( $canvas,$OPTIONS['fg_size'],$OPTIONS['fg_angle'],$X,$Y,$text_color,$OPTIONS['fg_font'],$RESPONSE_DATA['forecast'][1]['high'] );

$X                = 700 + 14;
$Y                = $Y + 20;
$bar_height       = $RESPONSE_DATA['forecast'][1]['high'];
$today_bar        = imagecreatetruecolor( 22,90 );
$white            = imagecolorallocate( $today_bar,255,255,255 );
imagefill( $today_bar,0,0,$white );
imagecopy( $canvas,$today_bar,$X,$Y,$src_x,$src_y,22,90 );

$X    = 700 + 6;
$Y    = $Y + 120;
imagettftext( $canvas,$OPTIONS['fg_size'],$OPTIONS['fg_angle'],$X,$Y,$text_color,$OPTIONS['fg_font'],$RESPONSE_DATA['forecast'][1]['low'] );





#  3rd day heading
$X    = 800;
$Y    = 118;
imagettftext( $canvas,$OPTIONS['fg_size'],$OPTIONS['fg_angle'],$X,$Y,$text_color,$OPTIONS['fg_font'],$RESPONSE_DATA['forecast'][2]['dayofweek'] );

# 3rd day image
$X                = $X - 10;
$Y                = $Y + 10;
$current_img      = imagecreatefrompng( $RESPONSE_DATA['forecast'][2]['img'] );
$src_x            = 0;
$src_y            = 0;
$dst_width        = 64;
$dst_height       = 64;
imagecopy( $canvas,$current_img,$X,$Y,$src_x,$src_y,$dst_width,$dst_height );

# 3rd day barometer
$X    = 800 + 4;
$Y    = $Y + $dst_height + 20;
imagettftext( $canvas,$OPTIONS['fg_size'],$OPTIONS['fg_angle'],$X,$Y,$text_color,$OPTIONS['fg_font'],$RESPONSE_DATA['forecast'][2]['high'] );

$X                = 800 + 14;
$Y                = $Y + 20;
$bar_height       = $RESPONSE_DATA['forecast'][2]['high'];
$today_bar        = imagecreatetruecolor( 22,90 );
$white            = imagecolorallocate( $today_bar,255,255,255 );
imagefill( $today_bar,0,0,$white );
imagecopy( $canvas,$today_bar,$X,$Y,$src_x,$src_y,22,90 );

$X    = 800 + 6;
$Y    = $Y + 120;
imagettftext( $canvas,$OPTIONS['fg_size'],$OPTIONS['fg_angle'],$X,$Y,$text_color,$OPTIONS['fg_font'],$RESPONSE_DATA['forecast'][2]['low'] );



# Output
imagepng( $canvas,'out.png');
imagedestroy( $canvas );

