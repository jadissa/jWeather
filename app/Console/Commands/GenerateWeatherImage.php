<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
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

        try {
            $weatherData = $this->fetchWeatherData();
            //print json_encode( $weatherData );exit;
            $this->createWeatherImage( $weatherData );
            $this->info( 'Image generated successfully at public/images/out.png' );
        } catch( Exception $e ) {
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
            'lang' => config('services.weatherapi.lang'),
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

    private function createWeatherImage($data)
    {

        // Define image
        $width              = 1092;
        $height             = 448;
        $image              = imagecreatetruecolor($width, $height);

        // Define weather Colors
        $this->grey         = imagecolorallocate( $image,147,148,150 );
        $this->dark_grey    = imagecolorallocate( $image,100,100,100 );
        $this->light_grey   = imagecolorallocate( $image,175,178,182 );
        $this->blue         = imagecolorallocate( $image,0,128,255 );
        $this->white        = imagecolorallocate( $image,255,255,255 );
        $this->yellow       = imagecolorallocate( $image,255,223,0 );
        $this->pale         = imagecolorallocate($image, 230, 230, 230);

        // Define fonts
        $this->font_color   = $this->hexToRgb( config('services.weatherapi.font_color') ) ?? [255, 255, 255];
        $this->font_color   = imagecolorallocate($image, $this->font_color['r'], $this->font_color['g'], $this->font_color['b']);
        $this->font_family  = public_path( config('services.weatherapi.font_family') );
        $this->font_size    = config('services.weatherapi.font_size');

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

        $conditionText      = strtolower( $this->current['condition']['text'] );

        // Make transparent
        imagesavealpha($image, true);
        $transparent = imagecolorallocatealpha($image, 0, 0, 0, 127);
        imagefill($image, 0, 0, $transparent);

        // Draw heading
        $locationName = $data['location']['name'];
        $headingText = "Weather for $locationName (hourly)";
        imagettftext($image, $this->font_size+5, 0, 20, 40, $this->font_color, $this->font_family, $headingText);

        // Draw horizontal line
        imageline($image, 0, 60, $width, 60, $this->font_color);

        // --- Left side: Current conditions ---
        $currentY = 150;
        $leftMargin = 20;
        
        // Draw current image
        $this->drawCurrentIcon( $image,$leftMargin + 32,$currentY-43 );

        // Current temperature
        $text = $this->current["temp_{$this->heat_unit}"]. '°';
        imagettftext($image, $this->font_size+8, 0, $leftMargin + 80, $currentY-22, $this->font_color, $this->font_family, $text);

        // Alert data
        $currentY += 40;
        if (isset($data['alerts']['alert'][0])) {
            $text = "Alert: " . $data['alerts']['alert'][0]['event'];
            imagettftext($image, $this->font_size, 0, $leftMargin, $currentY, $this->font_color, $this->font_family, $text);
        }

        // Chances
        foreach( $this->forecast as $index => $day ) {

            if( $index == 0 ) {

                $this->rain_chance  = number_format( $this->determineRaininess( $day ), 1 );
                $this->snow_chance  = number_format( $this->determineSnowiness( $day ), 1 );

            }

        }
        $currentY += 40;
        $text = "Chance of Rain: {$this->rain_chance}%, Chance of Snow: {$this->snow_chance}%";
        imagettftext($image, $this->font_size-5, 0, $leftMargin, $currentY, $this->font_color, $this->font_family, $text);


        // Wind
        $currentY += 40;
        $text = "Wind: " . $this->current["wind_{$this->speed_unit}"] . " $this->speed_unit " . $this->current['wind_dir'];
        if( $this->current["gust_{$this->speed_unit}"] > 0 ) {

            $text .= ", Gusts: " . $this->current["gust_{$this->speed_unit}"] . " {$this->speed_unit}";

        }
        imagettftext($image, $this->font_size-5, 0, $leftMargin, $currentY, $this->font_color, $this->font_family, $text);

        // Clouds
        $currentY += 40;
        $text = "Cloud Coverage: " . $this->current['cloud'] . "%, $conditionText";
        imagettftext($image, $this->font_size-5, 0, $leftMargin, $currentY, $this->font_color, $this->font_family, $text);

        // Humidity
        $currentY += 40;
        $humidityText = "Humidity: " . $this->current['humidity'] . "%";
        imagettftext($image, $this->font_size-5, 0, $leftMargin, $currentY, $this->font_color, $this->font_family, $humidityText);

        // --- Right side: 3-day forecast ---
        $rightMargin = 650;
        $columnWidth = 150;

        foreach( $this->forecast as $index => $day ) {

            $x = $rightMargin + ($index * $columnWidth) + 50;
            $y = 100;

            // Day name (e.g., Mon, Tues)
            $dayName = date('D', strtotime($day['date']));
            imagettftext($image, $this->font_size, 0, $x, $y, $this->font_color, $this->font_family, $dayName);

            $y += 20;

            $this->current['condition'] = $day['day']['condition'];

            $this->current['cloud']     = $this->determineCloudiness( $day );

            $this->drawForecastIcon( $image,$x,$y,$index );

            $y += 95;

            // High temperature
            $highTempText = $day['day']["maxtemp_{$this->heat_unit}"] . "°";
            imagettftext($image, $this->font_size-5, 0, $x, $y, $this->font_color, $this->font_family, $highTempText);

            $y += 30;

            // Vertical temperature line (20x100 pixel, filled dynamically)
            $tempLineHeight = 100;
            $tempLineWidth = 20;
            $lowTemp = $day['day']["mintemp_{$this->heat_unit}"];
            $highTemp = $day['day']["maxtemp_{$this->heat_unit}"];
            $tempRange = 40; // Assuming a 40 degree range for the line
            $fillHeight = min($tempLineHeight, max(0, ($highTemp / $tempRange) * $tempLineHeight));

            // Top
            imagefilledarc(
                $image,
                $x+15 + $tempLineWidth / 2, // X-coordinate of the center of the arc
                $y, // Y-coordinate of the center of the arc
                $tempLineWidth, // Width of the ellipse
                20, // Height of the ellipse
                180, // Start angle (top half of a circle)
                0, // End angle
                $this->font_color,
                IMG_ARC_PIE
            );

            // Middle
            imagefilledrectangle(
                $image,
                $x+15 + ($tempLineWidth / 2) - 10,
                $y,
                $x+15 + ($tempLineWidth / 2) + 10,
                $y + $fillHeight,
                $this->font_color
            );

            // Bottom
            imagefilledarc(
                $image,
                $x+15 + $tempLineWidth / 2, // X-coordinate of the center of the arc
                $y + $fillHeight, // Y-coordinate of the center of the arc
                $tempLineWidth, // Width of the ellipse
                20, // Height of the ellipse
                0, // Start angle (bottom half of a circle)
                180, // End angle
                $this->font_color,
                IMG_ARC_PIE
            );

            $y += $tempLineHeight + 55;

            // Low temperature
            $lowTempText = $day['day']["mintemp_{$this->heat_unit}"] . "°";
            imagettftext($image, $this->font_size-5, 0, $x, $y, $this->font_color, $this->font_family, $lowTempText);
        }

        // Save the image
        $imagePath = public_path('images/out.png');
        imagepng($image, $imagePath);
        imagedestroy($image);
    }

    private function determineSnowiness( $day )
    {
        $sum_snowy      = 0;

        foreach( $day['hour'] as $hour => $data ) {

            if( $data['chance_of_snow'] ) {

                $sum_snowy += $data['chance_of_snow'];

            }

        }

        return $sum_snowy / 24;

    }

    private function determineRaininess( $day )
    {
        $sum_rainy      = 0;

        foreach( $day['hour'] as $hour => $data ) {

            if( $data['chance_of_rain'] ) {

                $sum_rainy += $data['chance_of_rain'];

            }

        }

        return $sum_rainy / 24;

    }

    private function determineCloudiness( $day ) 
    {

        $sum_cloudy     = 0;

        foreach( $day['hour'] as $hour => $data ) {

            if( $data['cloud'] ) {

                $sum_cloudy += $data['cloud'];

            }

        }

        return $sum_cloudy / 24;

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
        if( $cloudy_pct >= 50 or str_contains( $conditionText, 'overcast' ) ) {

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
        // /$conditionText = 'sun, lightning, rain, snow, fog';
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
        if( $cloudy_pct >= 50 or str_contains($conditionText, 'overcast' ) ) {

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

        $shape_size = ( 32 );

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
        $radius = 32;

        $shape_size = ( $radius );

        // Draw a filled ellipse to represent the moon. Since the height and width are the same, it will be a perfect circle.
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

        for( $i = 0;$i < 5;$i++ ) {

            imageline($image, $x, $y + $i * 10,( $x + 64 ),( $y + $i * 10 ), $this->grey);

        }

    }

    private function drawLightning( $image,$x,$y ) {

        imageline( $image, $x + 32, $y + 15,( $x + 22 ),( $y + 30 ), $this->white );
        imageline( $image, $x + 33, $y + 15,( $x + 23 ),( $y + 30 ), $this->white );

        imageline( $image, $x + 22, $y + 30,( $x + 42 ),( $y + 30 ), $this->white );
        imageline( $image, $x + 23, $y + 30,( $x + 43 ),( $y + 30 ), $this->white );

        imageline( $image, $x + 42, $y + 30,( $x + 32 ),( $y + 45 ), $this->white );
        imageline( $image, $x + 43, $y + 30,( $x + 33 ),( $y + 45 ), $this->white );

    }

    private function drawLightClouds( $image,$x,$y ) {

        imagefilledellipse( $image,$x-20,$y + 10,15,10,$this->light_grey );
        imagefilledellipse( $image,$x,$y + 10,38,22,$this->grey );
        imagefilledellipse( $image,$x+20,$y + 14,30,10,$this->light_grey );

    }

    private function drawDarkClouds( $image,$x,$y ) {

        imagefilledellipse( $image,$x+10,$y + 8,42,30,$this->dark_grey );

    }

}