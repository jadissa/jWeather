<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\App;

class clock extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'weather:generate-clock';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->Config();
        $this->GenerateClock();

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

    private function Config() {

        date_default_timezone_set( config( 'services.weatherapi.time_zone' ) );

        // Define image
        $width              = 1200;
        $height             = 700;
        $this->image        = imagecreatetruecolor( $width,$height );

        // Define fonts
        $this->font_color   = $this->hexToRgb( config( 'services.weatherapi.font_color' ) ) ?? [255, 255, 255];
        $this->font_color   = imagecolorallocate( $this->image,$this->font_color['r'],$this->font_color['g'],$this->font_color['b'] );
        $this->font_family  = public_path( config( 'services.weatherapi.font_family' ) );
        $this->font_size    = config( 'services.weatherapi.font_size' );

        // Make transparent
        imagesavealpha($this->image, true);
        $transparent = imagecolorallocatealpha( $this->image,0,0,0,127 );
        imagefill( $this->image,0,0, $transparent );

    }

    private function GenerateClock() {

        // Draw the current time 
        $currentTime = date( 'h:i a' ); 
        $timeFontSize = $this->font_size * 10;
        imagettftext( $this->image,$timeFontSize,0,0,$timeFontSize,$this->font_color,$this->font_family,$currentTime );

        // Save the image
        $imagePath = public_path('images/clock.png');
        imagepng( $this->image,$imagePath );
        imagedestroy( $this->image );

        $this->info( 'Image generated successfully at public/images/clock.png' );

    }

}