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

        // Make transparent
        imagesavealpha($image, true);
        $transparent = imagecolorallocatealpha($image, 0, 0, 0, 127);
        imagefill($image, 0, 0, $transparent);

        // Draw heading
        $locationName = $data['location']['name'];
        $headingText = "Weather for $locationName";
        imagettftext($image, $this->font_size+5, 0, 20, 40, $this->font_color, $this->font_family, $headingText);

        // Draw horizontal line
        imageline($image, 0, 60, $width, 60, $this->font_color);

        // --- Left side: Current conditions ---
        $currentY = 150;
        $leftMargin = 20;
        
        // Draw current image
        $this->drawCurrentIcon( $image,$leftMargin + 32,$currentY-32 );

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
        $currentY += 40;
        $text = "Chance of Rain: " . $this->rain_chance . "%, Chance of Snow: " . $this->snow_chance . "%";
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
        $text = "Cloud Coverage: " . $this->current['cloud'] . "%";
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

            // Weather icon (dynamic 64x64 transparent image)
            $conditionText = $day['day']['condition']['text'];
            $this->drawForecastIcon( $image,$x,$y,$index );

            $y += 80;

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

    private function drawCurrentIcon( $image,$x,$y )
    {
        $conditionText  = strtolower( $this->current['condition']['text'] );
        $cloudy_pct     = $this->current['cloud'];

        //$conditionText = 'sun, lightning, fog';
        $scale_multiplyer   = 2;

        if( $this->day_time ) {

            $this->drawSun( $image,$scale_multiplyer,$x,$y );

        } else {

            $this->drawMoon( $image,$scale_multiplyer,$x,$y );

        }

        // Clouds
        if( str_contains( $conditionText, 'cloud' ) or str_contains($conditionText, 'overcast' ) ) {

            $this->drawLightClouds( $image,$scale_multiplyer,$x,$y,$conditionText,$cloudy_pct );
        }

        if( str_contains( $conditionText, 'sun' ) and $cloudy_pct >= 50 ) {

            $this->drawLightClouds( $image,$scale_multiplyer,$x,$y,$conditionText,$cloudy_pct );
            $this->drawDarkClouds( $image,$scale_multiplyer,$x,$y,$conditionText,$cloudy_pct );

        } elseif( $cloudy_pct >= 50 ) {

            $this->drawLightClouds( $image,$scale_multiplyer,$x,$y,$conditionText,$cloudy_pct );
            $this->drawDarkClouds( $image,$scale_multiplyer,$x,$y,$conditionText,$cloudy_pct );

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
        $scale_multiplyer   = 1;
        $conditionText      = strtolower( $this->current['condition']['text'] );
        $cloudy_pct         = $this->current['cloud'];
        //$conditionText = 'sun, lightning, fog';
        //var_dump( $conditionText,$cloudy_pct );

        // Sun
        imagefilledellipse( $image,$x + 25,$y + 15,32,32,$this->yellow );

        // Clouds
        if( str_contains( $conditionText, 'cloud' ) or str_contains($conditionText, 'overcast' ) ) {

            $this->drawLightClouds( $image,$scale_multiplyer,$x + 25,$y + 10,$conditionText,$cloudy_pct );

        }
        if( str_contains( $conditionText, 'sun' ) and $cloudy_pct >= 50 ) {

            $this->drawLightClouds( $image,$scale_multiplyer,$x + 25,$y + 10,$conditionText,$cloudy_pct );
            $this->drawDarkClouds( $image,$scale_multiplyer,$x + 25,$y + 10,$conditionText,$cloudy_pct );

        } elseif( $cloudy_pct >= 50 ) {

            $this->drawLightClouds( $image,$scale_multiplyer,$x + 25,$y + 10,$conditionText,$cloudy_pct );
            $this->drawDarkClouds( $image,$scale_multiplyer,$x + 25,$y + 10,$conditionText,$cloudy_pct );

        }

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

        // Fog
        if( str_contains( $conditionText,'fog' ) or str_contains( $conditionText,'mist' ) ) {

            $this->drawFog( $image,$x,$y );

        }

        // Thunder
        if ( str_contains($conditionText, 'lightning') ) {

            $this->drawLightning( $image,$x,$y );

        }

    }

    private function drawSun( $image,$scale_multiplyer,$x,$y ) {

        imagefilledellipse( $image,$x,$y,( 32 ) * $scale_multiplyer,( 32 ) * $scale_multiplyer,$this->yellow );

    }

    private function drawMoon( $image,$scale_multiplyer,$x,$y ) {

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

        // Draw a filled ellipse to represent the moon. Since the height and width are the same, it will be a perfect circle.
        imagefilledellipse( $image,$x,$y,( $radius ) * $scale_multiplyer,( $radius ) * $scale_multiplyer,$this->pale );

    }

    private function drawRain( $image,$x,$y ) {

        $y = $y + 15;
        $raindrop_count = 30;
        for( $i = 0;$i < $raindrop_count;$i++ ) {

            $new_x = mt_rand( 5,50 );
            $new_y = mt_rand( 30,40 );
            imageline( $image,$x + $new_x + 15,$y + $new_y,( $x + $new_x ),( $y + 10 ),$this->blue );

        }

    }

    private function drawSnow( $image,$x,$y ) {

        for( $i =0;$i < 20;$i++ ) {

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

        imageline( $image, $x + 42, $y + 30,( $x + 32 ),( $y + 45 ), $this->yellow );
        imageline( $image, $x + 43, $y + 30,( $x + 33 ),( $y + 45 ), $this->yellow );

    }

    private function drawLightClouds( $image,$scale_multiplyer,$x,$y,$conditionText,$cloudy_pct ) {

        imagefilledellipse( $image,$x,$y + 10,30 * $scale_multiplyer,20 * $scale_multiplyer,$this->grey );
        imagefilledellipse( $image,$x,$y + 10,30 * $scale_multiplyer,24 * $scale_multiplyer,$this->grey );
        imagefilledellipse( $image,$x,$y + 10,36 * $scale_multiplyer,26 * $scale_multiplyer,$this->grey );

    }

    private function drawDarkClouds( $image,$scale_multiplyer,$x,$y,$conditionText,$cloudy_pct ) {

        imagefilledellipse( $image,$x,$y + 18,30 * $scale_multiplyer,24 * $scale_multiplyer,$this->dark_grey );

    }

}