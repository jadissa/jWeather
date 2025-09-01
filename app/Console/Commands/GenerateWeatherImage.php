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
            $this->createWeatherImage($weatherData);
            $this->info('Image generated successfully at public/images/out.png');
        } catch (Exception $e) {
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
        $width = 1092;
        $height = 448;
        $image = imagecreatetruecolor($width, $height);

        // Define weather Colors
        $this->grey       = imagecolorallocate( $image,150,150,150 );
        $this->dark_grey  = imagecolorallocate( $image,100,100,100 );
        $this->blue       = imagecolorallocate( $image,0,128,255 );
        $this->white      = imagecolorallocate( $image,255,255,255 );
        $this->yellow     = imagecolorallocate( $image,255,223,0 );
        $this->pale       = imagecolorallocate($image, 230, 230, 230);

        // Define fonts
        $font_color = $this->hexToRgb( config('services.weatherapi.font_color') ) ?? [255, 255, 255];
        $font_color = imagecolorallocate($image, $font_color['r'], $font_color['g'], $font_color['b']);
        $font_family = public_path( config('services.weatherapi.font_family') );
        $font_size = config('services.weatherapi.font_size');

        // Define data constraints
        $precision = config('services.weatherapi.precision');
        $heat_unit = config('services.weatherapi.heat_unit');
        $speed_unit = config('services.weatherapi.speed_unit');

        // Make transparent
        imagesavealpha($image, true);
        $transparent = imagecolorallocatealpha($image, 0, 0, 0, 127);
        imagefill($image, 0, 0, $transparent);

        // Draw heading
        $locationName = $data['location']['name'];
        $headingText = "Weather for $locationName";
        imagettftext($image, $font_size+5, 0, 20, 40, $font_color, $font_family, $headingText);

        // Draw horizontal line
        imageline($image, 0, 60, $width, 60, $font_color);

        // --- Left side: Current conditions ---
        $current = $data['current'];
        $currentY = 150;
        $leftMargin = 20;

        // Draw current image
        $this->drawCurrentIcon( $image,$current['condition']['text'], $leftMargin + 32,$currentY-32 );

        // Current temperature
        $tempText = $current["temp_{$heat_unit}"]. '°';
        imagettftext($image, $font_size+8, 0, $leftMargin + 70, $currentY-22, $font_color, $font_family, $tempText);

        $currentY += 40;

        // Alert data
        if (isset($data['alerts']['alert'][0])) {
            $alertText = "Alert: " . $data['alerts']['alert'][0]['event'];
            imagettftext($image, $font_size, 0, $leftMargin, $currentY, $font_color, $font_family, $alertText);
        }

        $currentY += 40;

        // Wind
        $windText = "Wind: " . $current["wind_{$speed_unit}"] . " $speed_unit " . $current['wind_dir'];

        if( $current["gust_{$speed_unit}"] > 0 ) {

            $windText .= ", Gusts: " . $current["gust_{$speed_unit}"] . " {$speed_unit}";

        }

        imagettftext($image, $font_size-5, 0, $leftMargin, $currentY, $font_color, $font_family, $windText);

        $currentY += 40;

        // Humidity
        $humidityText = "Humidity: " . $current['humidity'] . "%";
        imagettftext($image, $font_size-5, 0, $leftMargin, $currentY, $font_color, $font_family, $humidityText);

        // --- Right side: 3-day forecast ---
        $forecastDays = $data['forecast']['forecastday'];
        $rightMargin = 550;
        $columnWidth = 200;

        foreach( $forecastDays as $index => $day ) {

            $x = $rightMargin + ($index * $columnWidth) + 50;
            $y = 100;

            // Day name (e.g., Mon, Tues)
            $dayName = date('D', strtotime($day['date']));
            imagettftext($image, $font_size, 0, $x, $y, $font_color, $font_family, $dayName);

            $y += 20;

            // Weather icon (dynamic 64x64 transparent image)
            $conditionText = strtolower($day['day']['condition']['text']);
            $this->drawForecastIcon($image, $x, $y, $conditionText,$index );

            $y += 80;

            // High temperature
            $highTempText = $day['day']["maxtemp_{$heat_unit}"] . "°";
            imagettftext($image, $font_size-5, 0, $x, $y, $font_color, $font_family, $highTempText);

            $y += 30;

            // Vertical temperature line (20x100 pixel, filled dynamically)
            $tempLineHeight = 100;
            $tempLineWidth = 20;
            $lowTemp = $day['day']["mintemp_{$heat_unit}"];
            $highTemp = $day['day']["maxtemp_{$heat_unit}"];
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
                $font_color,
                IMG_ARC_PIE
            );

            // Middle
            imagefilledrectangle(
                $image,
                $x+15 + ($tempLineWidth / 2) - 10,
                $y,
                $x+15 + ($tempLineWidth / 2) + 10,
                $y + $fillHeight,
                $font_color
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
                $font_color,
                IMG_ARC_PIE
            );

            $y += $tempLineHeight + 55;

            // Low temperature
            $lowTempText = $day['day']["mintemp_{$heat_unit}"] . "°";
            imagettftext($image, $font_size-5, 0, $x, $y, $font_color, $font_family, $lowTempText);
        }

        // Save the image
        $imagePath = public_path('images/out.png');
        imagepng($image, $imagePath);
        imagedestroy($image);
    }

    private function drawCurrentIcon( $image,$conditionText,$x,$y )
    {
        //$conditionText = 'sun, rain, lightning, cloud,';
        //var_dump( $conditionText );
        $day_time = true;

        $currentHour = date('H'); // Get current hour in 24-hour format (00-23)

        // Define "nighttime" hours (example: from 7 PM to before 6 AM)
        $afterDarkHour = 19; // 7 PM
        $beforeMorningHour = 6; // 6 AM

        if ($currentHour >= $afterDarkHour or $currentHour < $beforeMorningHour) {

            $day_time = false;

        }

        if( $day_time ) {

            imagefilledellipse( $image,$x,$y,64,64,$this->yellow );

        } else {

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
            imagefilledellipse($image, $x,$y, $radius * 2, $radius * 2, $this->pale);

        }

        // Clouds
        if( str_contains( $conditionText, 'cloud' ) ) {

            imagefilledellipse( $image,$x + 10,$y - 10,64-6,20,$this->grey );
            imagefilledellipse( $image,$x + 10,$y - 10,64-6,24,$this->grey );
            imagefilledellipse( $image,$x + 10,$y - 10,64,26,$this->grey );

        } elseif( str_contains($conditionText, 'overcast' ) ) {

            imagefilledellipse( $image,$x + 10,$y - 10,64-6,20,$this->grey );
            imagefilledellipse( $image,$x + 10,$y - 10,64-6,24,$this->grey );
            imagefilledellipse( $image,$x + 10,$y - 10,64,26,$this->grey );

            imagefilledellipse( $image,$x-10,$y,64,20,$this->dark_grey );
            imagefilledellipse( $image,$x + 10,$y,64-9,28,$this->dark_grey );

        }

        // Rain
        $x = $x - 30;
        if( str_contains( $conditionText,'rain' ) ) {

            $raindrop_count = 10;
            for( $i = 0;$i < $raindrop_count;$i++ ) {

                $new_x = mt_rand( 5,50 );
                $new_y = mt_rand( 30,40 );
                imageline( $image,$x + $new_x + 15,$y + $new_y,$x + $new_x,$y + 10,$this->blue );

            }

        }

        // Snow
        if( str_contains( $conditionText,'snow' ) or str_contains( $conditionText,'blizzard' ) ) {

            for( $i =0;$i < 5;$i++ ) {

                imagefilledellipse( $image, $x + rand(0, 32),$y + rand(0, 32),5,5,$this->white );

            }

        }

        // Sleet
        if( str_contains( $conditionText,'sleet' ) ) {

            for( $i = 0;$i < 3;$i++ ) {

                imageline( $image, $x + rand(0, 32),$y + rand(0, 32),$x + rand(0, 32),$y + rand(0, 32),$this->blue );

                imagefilledellipse($image, $x + rand(0, 32), $y + rand(0, 32), 5, 5, $this->white);

            }

        }

        // Fog
        if( str_contains( $conditionText,'fog' ) or str_contains( $conditionText,'mist' ) ) {

            for( $i = 0;$i < 5;$i++ ) {

                imageline($image, $x, $y + $i * 10, $x + 64, $y + $i * 10, $this->grey);

            }

        }

        // Thunder
        if ( str_contains($conditionText, 'lightning') ) {

            imageline($image, $x + 32, $y + 15, $x + 22, $y + 30, $this->blue);
            imageline($image, $x + 33, $y + 15, $x + 23, $y + 30, $this->blue);

            imageline($image, $x + 22, $y + 30, $x + 42, $y + 30, $this->blue);
            imageline($image, $x + 23, $y + 30, $x + 43, $y + 30, $this->blue);

            imageline($image, $x + 42, $y + 30, $x + 32, $y + 45, $this->yellow);
            imageline($image, $x + 43, $y + 30, $x + 33, $y + 45, $this->yellow);

        }

    }

    private function drawForecastIcon( $image,$x,$y,$conditionText,$iterator )
    {

        //$conditionText = 'sun, rain, lightning, cloud';
        //var_dump( $iterator, $conditionText );
        // Sun
        imagefilledellipse( $image,$x + 25,$y + 15,32,32,$this->yellow );

        // Clouds
        if( str_contains( $conditionText, 'cloud' ) ) {

            imagefilledellipse( $image,$x + 40,$y + 10,30,20,$this->grey );
            imagefilledellipse( $image,$x + 40,$y + 10,30,24,$this->grey );
            imagefilledellipse( $image,$x + 40,$y + 10,36,26,$this->grey );

        } elseif( str_contains($conditionText, 'overcast' ) ) {

            imagefilledellipse( $image,$x + 40,$y + 10,30,20,$this->grey );
            imagefilledellipse( $image,$x + 40,$y + 10,30,24,$this->grey );
            imagefilledellipse( $image,$x + 40,$y + 10,36,26,$this->grey );

            imagefilledellipse( $image,$x + 20,$y + 25,25,15,$this->dark_grey );
            imagefilledellipse( $image,$x + 40,$y + 18,30,24,$this->dark_grey );

        }

        // Rain
        if( str_contains( $conditionText,'rain' ) ) {

            $raindrop_count = 10;
            for( $i = 0;$i < $raindrop_count;$i++ ) {

                $new_x = mt_rand( 5,50 );
                $new_y = mt_rand( 30,40 );
                imageline( $image,$x + $new_x + 15,$y + $new_y,$x + $new_x,$y + 10,$this->blue );

            }

        }

        // Snow
        if( str_contains( $conditionText,'snow' ) or str_contains( $conditionText,'blizzard' ) ) {

            for( $i =0;$i < 5;$i++ ) {

                imagefilledellipse( $image, $x + rand(0, 32),$y + rand(0, 32),5,5,$this->white );

            }

        }

        // Sleet
        if( str_contains( $conditionText,'sleet' ) ) {

            for( $i = 0;$i < 3;$i++ ) {

                imageline( $image, $x + rand(0, 32),$y + rand(0, 32),$x + rand(0, 32),$y + rand(0, 32),$this->blue );

                imagefilledellipse($image, $x + rand(0, 32), $y + rand(0, 32), 5, 5, $this->white);

            }

        }

        // Fog
        if( str_contains( $conditionText,'fog' ) or str_contains( $conditionText,'mist' ) ) {

            for( $i = 0;$i < 5;$i++ ) {

                imageline($image, $x, $y + $i * 10, $x + 64, $y + $i * 10, $this->grey);

            }

        }

        // Thunder
        if ( str_contains($conditionText, 'lightning') ) {

            imageline($image, $x + 32, $y + 15, $x + 22, $y + 30, $this->white);
            imageline($image, $x + 33, $y + 15, $x + 23, $y + 30, $this->white);

            imageline($image, $x + 22, $y + 30, $x + 42, $y + 30, $this->white);
            imageline($image, $x + 23, $y + 30, $x + 43, $y + 30, $this->white);

            imageline($image, $x + 42, $y + 30, $x + 32, $y + 45, $this->yellow);
            imageline($image, $x + 43, $y + 30, $x + 33, $y + 45, $this->yellow);

        }

    }

}