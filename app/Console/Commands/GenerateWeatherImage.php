<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\App;
use Exception;

class GenerateWeatherImage extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'weather:generate-image';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generates a weather image for a given lat/lon';

    /**
     * Execute the console command.
     */
    public function handle()
    {

        try
        {
            // Define zone
            date_default_timezone_set( config( 'services.weatherapi.time_zone' ) );
            App::setLocale(config('services.weatherapi.app_locale'));

            $this->createClockImage();

            $this->createWeatherImage( $this->fetchWeatherData() );
        }
        catch( Exception $e )
        {
            $this->error('An error occurred: ' . $e->getMessage());
        }

    }

    private function fetchWeatherData()
    {

        $response = Http::get("http://api.weatherapi.com/v1/forecast.json", [
            'key' => config('services.weatherapi.weatherapi_key'),
            'q' => config('services.weatherapi.latitude').','.config('services.weatherapi.longtitude'),
            'days' => config('services.weatherapi.days_to_fetch'),
            'alerts' => 'yes',
            'lang' => config('services.weatherapi.app_locale'),
        ]);

        if ($response->successful()) {
            // print json_encode( $response->json() );exit;
            return $response->json();
        }

        throw new Exception("Failed to fetch weather data: " . $response->body());

    }

    private function hexToRgb($hexColor) {
        // Remove '#' if present
        $hexColor = ltrim($hexColor, '#');

        // Handle 3-character shorthand hex codes (e.g., #F00 becomes #FF0000)
        if (strlen($hexColor) == 3) {
            $r = hexdec(substr($hexColor, 0, 1) . substr($hexColor, 0, 1));
            $g = hexdec(substr($hexColor, 1, 1) . substr($hexColor, 1, 1));
            $b = hexdec(substr($hexColor, 2, 1) . substr($hexColor, 2, 1));
        } 
        // Handle 6-character hex codes (e.g., #FF0000)
        elseif (strlen($hexColor) == 6) {
            $r = hexdec(substr($hexColor, 0, 2));
            $g = hexdec(substr($hexColor, 2, 2));
            $b = hexdec(substr($hexColor, 4, 2));
        } 
        // Return false or handle invalid input
        else {
            return false; 
        }

        return ['r' => $r, 'g' => $g, 'b' => $b];
    }

    private function allocateTypography( $image )
    {

        // Define colors
        $this->grey             = imagecolorallocate( $image,147,148,150 );
        $this->dark_grey        = imagecolorallocate( $image,100,100,100 );
        $this->light_grey       = imagecolorallocate( $image,175,178,182 );
        $this->blue             = imagecolorallocate( $image,0,128,255 );
        $this->white            = imagecolorallocate( $image,255,255,255 );
        $this->yellow           = imagecolorallocate( $image,255,223,0 );
        $this->pale             = imagecolorallocate( $image,230,230,230 );
        $this->black            = imagecolorallocate( $image,0,0,0 );

        // Define fonts
        $this->font_fg_color    = $this->hexToRgb( config('services.weatherapi.font_fg_color') ) ?? [255, 255, 255];
        $this->font_fg_color    = imagecolorallocate( $image,$this->font_fg_color['r'],$this->font_fg_color['g'],$this->font_fg_color['b'] );

        $this->font_bg_color    = $this->hexToRgb( config('services.weatherapi.font_bg_color') ) ?? [0, 0, 0];
        $this->font_bg_color    = imagecolorallocate( $image,$this->font_bg_color['r'],$this->font_bg_color['g'],$this->font_bg_color['b'] );

        $this->font_family      = public_path( config('services.weatherapi.font_family') );
        $this->font_size        = config('services.weatherapi.font_size');
        $this->font_shadow_size = 1;
    }

    private function createClockImage()
    {

        // Define image
        $width              = 1200;
        $height             = 300;
        $image              = imagecreatetruecolor( $width,$height );

        // Define fonts and colors
        $this->allocateTypography( $image );

        // Make transparent
        imagesavealpha( $image,true );
        $transparent    = imagecolorallocatealpha( $image,0,0,0,127 );
        $currentY       = 250;
        $leftMargin     = 20;
        $topLeft        = $currentY - $leftMargin;

        imagefill( $image,0,0,$transparent );

        // Draw clock
        $this->drawClock( $image,( $leftMargin )*-1,$currentY,$this->font_size * 9 );

        // Save the image
        $imagePath = public_path( 'images/clock.png' );
        imagepng( $image,$imagePath );
        imagedestroy( $image );
        
        $this->info( 'Image generated successfully at public/images/clock.png' );

    }

    private function createWeatherImage($data)
    {

        // Define image
        $width              = 1200;
        $height             = 670;
        $image              = imagecreatetruecolor( $width,$height );

        // Define fonts and colors
        $this->allocateTypography( $image );

        // Define data constraints
        $this->precision    = config('services.weatherapi.precision');
        $this->heat_unit    = config('services.weatherapi.heat_unit');
        $this->speed_unit   = config('services.weatherapi.speed_unit');

        // Each day's data
        $this->forecast     = $data['forecast']['forecastday'];

        // Today's data
        $this->current      = $data['current'];
        $this->day_time     = $this->current['is_day'];
        $this->rain_chance  = $this->forecast[0]['day']['daily_chance_of_rain'];
        $this->snow_chance  = $this->forecast[0]['day']['daily_chance_of_snow'];

        $conditionText      = $this->current['condition']['text'];
        //print'<pre>';print_r( $this->current );print'</pre>';
        // Daily data
        foreach( $this->forecast as $index => $day ) {

            if( $index == 0 ) {

                $this->rain_chance      = number_format( $this->determineRaininess( $day ), 1 );
                $this->snow_chance      = number_format( $this->determineSnowiness( $day ), 1 );

            }

            //$this->temp_direction       = $this->determineHeatDirection( $day );

        }

        // Make transparent
        imagesavealpha( $image,true );
        $transparent    = imagecolorallocatealpha( $image,0,0,0,127 );
        $currentY       = 200;
        $leftMargin     = 20;
        $topLeft        = $currentY - $leftMargin;

        imagefill($image, 0, 0, $transparent);

        // Draw heading
        $top_y          = $currentY;
        $headingText    = "{$data['location']['name']}, {$data['location']['region']}";
        imagettftext($image, $this->font_size+5, 0, 20, $top_y, $this->font_bg_color, $this->font_family, $headingText);
        imagettftext($image, $this->font_size+5, 0, 20+$this->font_shadow_size, $top_y+$this->font_shadow_size, $this->font_fg_color, $this->font_family, $headingText);

        // Draw horizontal line
        $currentY += 25;
        imageline( $image,$leftMargin,$currentY,$width,$currentY,$this->font_fg_color );

        // --- Left side: Current conditions ---
        $currentY += 100;
        
        // Draw current image
        $column_y = $currentY-43;
        $this->drawCurrentIcon( $image,$leftMargin + 32,$column_y );
        $currentY = $column_y+40;

        // Current temperature
        $currentY+=50;
        $text = $this->current["temp_{$this->heat_unit}"]. '°';
        imagettftext($image, $this->font_size*4, 0, $leftMargin + 60 + $this->font_shadow_size, $currentY + $this->font_shadow_size, $this->font_bg_color, $this->font_family, $text);
        imagettftext($image, $this->font_size*4, 0, $leftMargin + 60, $currentY, $this->font_fg_color, $this->font_family, $text);

        // Increasing/decreasing
        //$currentY += 40;
        //imagettftext($image, max( $this->font_size-12, 10 ), 0, $leftMargin + 80, $currentY, $this->font_fg_color, $this->font_family, $this->temp_direction );

        // Snow/Rain
        $currentY += 50;
        $text = __('messages.chance_of_rain').": {$this->rain_chance}%, ".__('messages.chance_of_snow').": {$this->snow_chance}%";
        imagettftext($image, $this->font_size-5, 0, $leftMargin+$this->font_shadow_size, $currentY+$this->font_shadow_size, $this->font_bg_color, $this->font_family, $text);
        imagettftext($image, $this->font_size-5, 0, $leftMargin, $currentY, $this->font_fg_color, $this->font_family, $text);

        // Humidity
        $currentY += 40;
        $humidityText = __('messages.cloudy').": " . $this->current['cloud'] . "% , ".__('messages.humidity').": " . $this->current['humidity'] . "%";
        imagettftext($image, $this->font_size-5, 0, $leftMargin+$this->font_shadow_size, $currentY+$this->font_shadow_size, $this->font_bg_color, $this->font_family, $humidityText);
        imagettftext($image, $this->font_size-5, 0, $leftMargin, $currentY, $this->font_fg_color, $this->font_family, $humidityText);

        // Wind
        $currentY += 40;
        $text = __('messages.wind').": " . $this->current["wind_{$this->speed_unit}"] . " $this->speed_unit " . $this->current['wind_dir'];
        if( $this->current["gust_{$this->speed_unit}"] > 0 ) {

            $text .= ", ".__('messages.gusts').": " . $this->current["gust_{$this->speed_unit}"] . " {$this->speed_unit}";

        }
        imagettftext($image, max( $this->font_size/2, 10 ), 0, $leftMargin+45+$this->font_shadow_size, $currentY+$this->font_shadow_size, $this->font_bg_color, $this->font_family, $text);
        imagettftext($image, max( $this->font_size/2, 10 ), 0, $leftMargin+45, $currentY, $this->font_fg_color, $this->font_family, $text);

        // Alert data
        $currentY += 60;
        if (isset($data['alerts']['alert'][0])) {
            // Alert heading
            $text = __('messages.alert').": " . $data['alerts']['alert'][0]['event'];
            imagettftext($image, $this->font_size, 0, $leftMargin+$this->font_shadow_size, $currentY+$this->font_shadow_size, $this->font_bg_color, $this->font_family, $text);
            imagettftext($image, $this->font_size, 0, $leftMargin, $currentY, $this->font_fg_color, $this->font_family, $text);

            // Alert message
            $currentY += 40;
            $text = __('messages.alert').": " . $data['alerts']['alert'][0]['desc'];
            $text = str_replace(array("\n", "\r"), '', $text);
            $text = wordwrap( $text,80,"\n",false );
            $truncate = strpos( $text,'WHERE' );
            if ($truncate !== false) {

                $text = substr( $text,0,$truncate );
            }

            imagettftext($image, $this->font_size/2, 0, $leftMargin+$this->font_shadow_size, $currentY+$this->font_shadow_size, $this->font_bg_color, $this->font_family, $text);
            imagettftext($image, $this->font_size/2, 0, $leftMargin, $currentY, $this->font_fg_color, $this->font_family, $text);
        }

        // Timestamp the image generated
        $currentY += 40;
        $gen_size = $this->font_size/2;
        imagettftext( $image,$gen_size,0,$leftMargin+$this->font_shadow_size,$currentY+$this->font_shadow_size,$this->font_bg_color,$this->font_family,'Generated at...' );
        imagettftext( $image,$gen_size,0,$leftMargin,$currentY,$this->font_fg_color,$this->font_family,'Generated at...' );
        $this->drawClock( $image,$leftMargin+125,$currentY,$gen_size,'m-d h:i' );

        // Condition/Clouds
        /*
        $currentY += 40;
        $text = "$conditionText, Cloud Coverage: " . $this->current['cloud'] . "%";
        imagettftext($image, $this->font_size-5, 0, $leftMargin+$this->font_shadow_size, $currentY+$this->font_shadow_size, $this->font_bg_color, $this->font_family, $text);
        imagettftext($image, $this->font_size-5, 0, $leftMargin, $currentY, $this->font_fg_color, $this->font_family, $text);
        */

        // --- Right side: 3-day forecast ---
        $rightMargin = 650;
        $columnWidth = 150;

        foreach( $this->forecast as $index => $day ) {

            $x = $rightMargin + ($index * $columnWidth) + 50;
            $currentY = $top_y;

            // Day name (e.g., Mon, Tues)
            $dayName = date('D', strtotime($day['date']));
            imagettftext($image, $this->font_size, 0, $x+$this->font_shadow_size, $top_y+$this->font_shadow_size, $this->font_bg_color, $this->font_family, $dayName);
            imagettftext($image, $this->font_size, 0, $x, $top_y, $this->font_fg_color, $this->font_family, $dayName);

            $currentY += 55;

            $this->current['condition'] = $day['day']['condition'];
            $this->current['cloud']     = $this->determineCloudiness( $day );


            // Third day demo
            if( $index >= 2 and config('services.weatherapi.demo_sesh') ) {

                $this->current['condition']['text'] = 'overcast,rain';
                $this->current['cloud']             = 100;

                $day['day']['maxtemp_f']            = 60;
                $day['day']['mintemp_f']            = 20;
                $day['day']['avgtemp_f']            = ( $day['day']['maxtemp_f'] + $day['day']['mintemp_f'] ) / 2;

                $day['day']['maxtemp_c']            = $this->fahrenheitToCelsius( $day['day']['maxtemp_f'] );
                $day['day']['mintemp_c']            = $this->fahrenheitToCelsius( $day['day']['mintemp_f'] );;
                $day['day']['avgtemp_c']            = $this->fahrenheitToCelsius( $day['day']['avgtemp_f'] );

            // Second day demo
            } elseif( $index >= 1 and config('services.weatherapi.demo_sesh') ) {

                $this->current['condition']['text'] = 'overcast,heavy rain';
                $this->current['cloud']             = 50;

                $day['day']['maxtemp_f']            = 80;
                $day['day']['mintemp_f']            = 49;
                $day['day']['avgtemp_f']            = ( $day['day']['maxtemp_f'] + $day['day']['mintemp_f'] ) / 2;

                $day['day']['maxtemp_c']            = $this->fahrenheitToCelsius( $day['day']['maxtemp_f'] );
                $day['day']['mintemp_c']            = $this->fahrenheitToCelsius( $day['day']['mintemp_f'] );;
                $day['day']['avgtemp_c']            = $this->fahrenheitToCelsius( $day['day']['avgtemp_f'] );

            }

            $currentY = $column_y;
            $this->drawForecastIcon( $image,$x,$column_y,$index );

            $currentY += 95;

            // High temperature
            $highTempText = $day['day']["maxtemp_{$this->heat_unit}"] . "°";
            imagettftext($image, $this->font_size-5, 0, $x+$this->font_shadow_size, $currentY+$this->font_shadow_size, $this->font_bg_color, $this->font_family, $highTempText);
            imagettftext($image, $this->font_size-5, 0, $x, $currentY, $this->font_fg_color, $this->font_family, $highTempText);

            $currentY += 45;

            // Vertical temperature line (20x100 pixel, filled dynamically)
            $tempLineHeight = 100;
            $tempLineWidth = 20;
            $highTemp = $day['day']["maxtemp_{$this->heat_unit}"];
            $fillHeight = min( $tempLineHeight,$highTemp );

            // Top
            imagefilledarc(
                $image,
                $x+15 + $tempLineWidth / 2+$this->font_shadow_size, // X-coordinate of the center of the arc
                $currentY+$this->font_shadow_size, // Y-coordinate of the center of the arc
                $tempLineWidth, // Width of the ellipse
                20, // Height of the ellipse
                180, // Start angle (top half of a circle)
                0, // End angle
                $this->font_bg_color,
                IMG_ARC_PIE
            );
            imagefilledarc(
                $image,
                $x+15 + $tempLineWidth / 2, // X-coordinate of the center of the arc
                $currentY, // Y-coordinate of the center of the arc
                $tempLineWidth, // Width of the ellipse
                20, // Height of the ellipse
                180, // Start angle (top half of a circle)
                0, // End angle
                $this->font_fg_color,
                IMG_ARC_PIE
            );

            // Average high temperature
            $avgTemp[ $index ]  = number_format( $day['day']["avgtemp_{$this->heat_unit}"], 1 );
            $avgTempText = $avgTemp[ $index ];
            /*
            imageline( 
                $image,
                $x+15 + ($tempLineWidth / 2) - 10,
                $currentY-15,
                $x+15 + ($tempLineWidth / 2) + 15,
                ( $currentY + $fillHeight ) / 2,
                $this->font_fg_color );
            */
            // Assuming the variables for the rectangle are already defined.
            // The image resource $image, and the color $this->font_fg_color are also defined.

            // Define the coordinates of the rectangle to make the calculation clearer
            $rect_x1 = $x + 15 + ($tempLineWidth / 2) - 10;
            $rect_y1 = $currentY;
            $rect_x2 = $x + 15 + ($tempLineWidth / 2) + 10;
            $rect_y2 = $currentY + $fillHeight;

            // Calculate the center y-coordinate of the rectangle
            $line_y = $rect_y1 + (($rect_y2 - $rect_y1) / 2);

            // Calculate the center x-coordinate of the rectangle
            $center_x = $rect_x1 + (($rect_x2 - $rect_x1) / 2);

            // Calculate the line start and end x-coordinates (26 pixels wide)
            $line_width = 46;
            $line_x1 = $center_x - ($line_width / 2);
            $line_x2 = $center_x + ($line_width / 2) - 1; // Subtract 1 for inclusive pixel count

            // Draw the 1-pixel horizontal line
            imageline($image, $line_x1, $line_y, $line_x2, $line_y, $this->font_fg_color);
            imagettftext($image, max( $this->font_size-20, 10 ), 0, $line_x2+7+$this->font_shadow_size, $line_y+5+$this->font_shadow_size, $this->font_bg_color, $this->font_family, $avgTempText);
            imagettftext($image, max( $this->font_size-20, 10 ), 0, $line_x2+7, $line_y+5, $this->font_fg_color, $this->font_family, $avgTempText);

            // Middle
            imagefilledrectangle(
                $image,
                $x+15 + ($tempLineWidth / 2) - 10,
                $currentY,
                $x+15 + ($tempLineWidth / 2) + 10+$this->font_shadow_size,
                $currentY + $fillHeight+$this->font_shadow_size,
                $this->font_bg_color
            );
            imagefilledrectangle(
                $image,
                $x+15 + ($tempLineWidth / 2) - 10,
                $currentY,
                $x+15 + ($tempLineWidth / 2) + 10,
                $currentY + $fillHeight,
                $this->font_fg_color
            );

            // Bottom
            imagefilledarc(
                $image,
                $x+15 + $tempLineWidth / 2+$this->font_shadow_size, // X-coordinate of the center of the arc
                $currentY + $fillHeight+$this->font_shadow_size, // Y-coordinate of the center of the arc
                $tempLineWidth, // Width of the ellipse
                20, // Height of the ellipse
                0, // Start angle (bottom half of a circle)
                180, // End angle
                $this->font_bg_color,
                IMG_ARC_PIE
            );
            imagefilledarc(
                $image,
                $x+15 + $tempLineWidth / 2, // X-coordinate of the center of the arc
                $currentY + $fillHeight, // Y-coordinate of the center of the arc
                $tempLineWidth, // Width of the ellipse
                20, // Height of the ellipse
                0, // Start angle (bottom half of a circle)
                180, // End angle
                $this->font_fg_color,
                IMG_ARC_PIE
            );

            $currentY = $currentY + ( $tempLineHeight ) + 45;

            // Low temperature
            $lowTempText = $day['day']["mintemp_{$this->heat_unit}"] . "°";
            imagettftext($image, $this->font_size-5, 0, $x+$this->font_shadow_size, $currentY+$this->font_shadow_size, $this->font_bg_color, $this->font_family, $lowTempText);
            imagettftext($image, $this->font_size-5, 0, $x, $currentY, $this->font_fg_color, $this->font_family, $lowTempText);
        }

        // Save the image
        $imagePath = public_path('images/weather.png');
        imagepng( $image,$imagePath );
        imagedestroy( $image );
        $this->info( 'Image generated successfully at public/images/weather.png' );
    }

    private function fahrenheitToCelsius(float $fahrenheit): float {
        return ($fahrenheit - 32) * 5 / 9;
    }

    private function determineHeatDirection( $day )
    {

        $futureHour         = date('h', strtotime('+3 hour'));

        $currentHour        = date('h');

        $futureIndex        = $day['hour'][$futureHour] ?? 0;

        $currentIndex       = $day['hour'][$currentHour] ?? 0;

        if( $futureIndex == 0 or $currentIndex == 0 ) {

            return;
            
        }

        if( $futureIndex["temp_{$this->heat_unit}"] > $currentIndex["temp_{$this->heat_unit}"] ) {

            return __('messages.increasing');

        } else {

            return __('messages.decreasing');
        }

    }

    private function determineSnowiness( $day )
    {
        $sum      = 0;

        foreach( $day['hour'] as $hour => $data ) {

            if( $data['chance_of_snow'] ) {

                $sum += $data['chance_of_snow'];

            }

        }

        return $sum / 24;

    }

    private function determineRaininess( $day )
    {
        $sum      = 0;

        foreach( $day['hour'] as $hour => $data ) {

            if( $data['chance_of_rain'] ) {

                $sum += $data['chance_of_rain'];

            }

        }

        return $sum / 24;

    }

    private function determineCloudiness( $day ) 
    {

        $sum     = 0;

        foreach( $day['hour'] as $hour => $data ) {

            if( $data['cloud'] ) {

                $sum += $data['cloud'];

            }

        }

        return $sum / 24;

    }

    private function drawCurrentIcon( $image,$x,$y )
    {
        $conditionText  = strtolower( $this->current['condition']['text'] );
        $cloudy_pct     = $this->current['cloud'];

        //$conditionText = 'sun, lightning, rain, snow, fog';

        if( $this->day_time ) {

            $this->drawSun( $image,$x,$y );

        } else {

            $this->drawMoon( $image,$x,$y );

        }

        // Reset coords for weather
        $x = $x - 30;
        $y = $y - 10;

        // Rain
        if( str_contains( $conditionText,'rain' ) ) {

            $this->drawRain( $image,$x,$y );

        }

        // Snow
        if( str_contains( $conditionText,'snow' ) or str_contains( $conditionText,'blizzard' ) ) {

            $this->drawSnow( $image,$x,$y );

        }

        // Sleet
        if( str_contains( $conditionText,'sleet' ) ) {

            $this->drawSleet( $image,$x,$y );

        }

        // Clouds
        if( str_contains( $conditionText, 'overcast' ) ) {

            $this->drawDarkClouds( $image,$x+30,$y-5+10 );

        }
        $this->drawLightClouds( $image,$x+30,$y-5+10 );

        // Fog
        if( str_contains( $conditionText,'fog' ) or str_contains( $conditionText,'mist' ) ) {

            $this->drawFog( $image,$x,$y );

        }

        // Thunder
        if ( str_contains($conditionText, 'lightning') ) {

            $this->drawLightning( $image,$x,$y );

        }

    }

    private function drawForecastIcon( $image,$x,$y )
    {
        $conditionText      = strtolower( $this->current['condition']['text'] );
        $cloudy_pct         = $this->current['cloud'];
        //$conditionText = 'sun, lightning, rain, snow, fog';
        //var_dump( $conditionText,$cloudy_pct );

        // Sun
        $this->drawSun( $image,$x + 25,$y + 15 );

        // Rain        
        if( str_contains( $conditionText,'rain' ) ) {

            $this->drawRain( $image,$x,$y );

        }

        // Snow
        if( str_contains( $conditionText,'snow' ) or str_contains( $conditionText,'blizzard' ) ) {

            $this->drawSnow( $image,$x,$y );

        }

        // Sleet
        if( str_contains( $conditionText,'sleet' ) ) {

            $this->drawSleet( $image,$x,$y );

        }

        // Clouds
        if( str_contains($conditionText, 'overcast' ) ) {

            $this->drawDarkClouds( $image,$x + 25,$y + 12 );

        }
        $this->drawLightClouds( $image,$x + 25,$y + 10 );

        // Fog
        if( str_contains( $conditionText,'fog' ) or str_contains( $conditionText,'mist' ) ) {

            $this->drawFog( $image,$x,$y );

        }

        // Thunder
        if ( str_contains($conditionText, 'lightning') ) {

            $this->drawLightning( $image,$x,$y );

        }

    }

    private function drawSun( $image,$x,$y ) {

        $shape_size = ( 36 );

        imagefilledellipse( $image,$x+$this->font_shadow_size,$y+$this->font_shadow_size,$shape_size,$shape_size,$this->font_bg_color );
        imagefilledellipse( $image,$x,$y,$shape_size,$shape_size,$this->yellow );

        return $shape_size;
    }

    private function drawMoon( $image,$x,$y ) {

        // Turn off alpha blending to ensure the alpha channel is preserved when drawing.
        imagealphablending($image, false);

        // Allocate a transparent color with a 127 alpha value (fully transparent).
        $transparent_color = imagecolorallocatealpha($image, 0, 0, 0, 127);

        // Fill the entire image with the transparent color.
        imagefill($image, 0, 0, $transparent_color);

        // Enable the saving of the full alpha channel information for the PNG.
        imagesavealpha($image, true);

        // Calculate the center and radius for the circle.
        $center_x = 64 / 2;
        $center_y = 64 / 2;
        $radius = 36;

        $shape_size = ( $radius );

        // Draw a filled ellipse to represent the moon. Since the height and width are the same, it will be a perfect circle.
        imagefilledellipse( $image,$x+$this->font_shadow_size,$y+$this->font_shadow_size,$shape_size,$shape_size,$this->font_bg_color );
        imagefilledellipse( $image,$x,$y,$shape_size,$shape_size,$this->pale );

        return $shape_size;

    }

    private function drawRain( $image,$x,$y ) {

        $conditionText  = strtolower( $this->current['condition']['text'] );

        if( str_contains( $conditionText,'light' ) ) {

            $rain_count = 5;

        } elseif( str_contains( $conditionText,'heavy' ) ) {

            $rain_count = 25;

        } else {

            $rain_count = 10;

        }

        $y = $y + 15;
        for( $i = 0;$i < $rain_count;$i++ ) {

            $new_x = mt_rand( 10,50 );
            $new_y = mt_rand( 30,40 );
            imageline( $image,$x + $new_x + 15,$y + $new_y,( $x + $new_x ),( $y + 10 ),$this->blue );

        }

    }

    private function drawSnow( $image,$x,$y ) {

        $conditionText  = strtolower( $this->current['condition']['text'] );

        if( str_contains( $conditionText,'light' ) ) {

            $snow_count = 5;

        } elseif( str_contains( $conditionText,'heavy' ) ) {

            $snow_count = 25;

        } else {

            $snow_count = 10;

        }

        for( $i =0;$i < $snow_count;$i++ ) {

            imagefilledellipse( $image, $x + rand(5, 64),$y + rand(30, 55),5,5,$this->white );

        }

    }

    private function drawSleet( $image,$x,$y ) {

        $this->drawRain( $image,$x,$y );
        $this->drawSnow( $image,$x,$y );

    }

    private function drawFog( $image,$x,$y ) {

        $conditionText  = strtolower( $this->current['condition']['text'] );
        $thickness      = 1;

        if( str_contains( $conditionText,'light' ) ) {

            $thickness = 3;

        } elseif( str_contains( $conditionText,'heavy' ) ) {

            $thickness = 6;

        }

        imagesetthickness( $image,$thickness );

        for( $i = 0;$i < 5;$i++ ) {

            imageline($image, $x, $y + $i * 10,( $x + 64 ),( $y + $i * 10 ), $this->grey);

        }

    }

    private function drawLightning( $image,$x,$y ) {

        imageline( $image,$x + 32, $y + 15,( $x + 22 ),( $y + 30 ),$this->white );
        imageline( $image,$x + 33, $y + 15,( $x + 23 ),( $y + 30 ),$this->white );

        imageline( $image,$x + 22, $y + 30,( $x + 42 ),( $y + 30 ),$this->white );
        imageline( $image,$x + 23, $y + 30,( $x + 43 ),( $y + 30 ),$this->white );

        imageline( $image,$x + 42, $y + 30,( $x + 32 ),( $y + 45 ),$this->white );
        imageline( $image,$x + 43, $y + 30,( $x + 33 ),( $y + 45 ),$this->white );

    }

    private function drawLightClouds( $image,$x,$y ) {

        $cloudy_pct = $this->current['cloud'];

        if( $cloudy_pct > 0 ) {

            imagefilledellipse( $image,$x-20,$y + 10,15,10,$this->light_grey );
        
        }
        if( $cloudy_pct > 25 ) {

            imagefilledellipse( $image,$x,$y + 10,38,22,$this->grey );

        }
        if( $cloudy_pct > 0 ) {
            
            imagefilledellipse( $image,$x+20,$y + 14,30,10,$this->light_grey );

        }

    }

    private function drawDarkClouds( $image,$x,$y ) {

        imagefilledellipse( $image,$x+10,$y + 8,42,30,$this->dark_grey );

    }

    private function drawClock( $image,$x,$y,$font_size,$format='h:i' ) {

        $currentTime = date( $format ); 

        imagettftext(
            $image,
            $font_size,
            0,
            $x+$this->font_shadow_size,
            $y+$this->font_shadow_size,
            $this->font_bg_color,
            $this->font_family,
            $currentTime,
            []
        );

        imagettftext(
            $image,
            $font_size,
            0,
            $x,
            $y,
            $this->font_fg_color,
            $this->font_family,
            $currentTime,
            []
        );

    }

}