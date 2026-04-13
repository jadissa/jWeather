<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\GetAPIResponse;

class GetWeatherForecastCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:get-weather-forecast-command';

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
        try
        {
            // Define zone
            date_default_timezone_set( config( 'services.weatherapi.time_zone' ) );

            print $this->fetchWeatherData();
        }
        catch( Exception $e )
        {
            $this->error('An error occurred: ' . $e->getMessage());
        }
    }

    private function fetchWeatherData()
    {

        $controller = new GetAPIResponse();
        $lat        = config('services.weatherapi.latitude');
        $long       = config('services.weatherapi.longtitude');
        $num_days   = config('services.weatherapi.days_to_fetch');
        $forecastData = $controller->getWeatherForecast( $lat,$long,$num_days );
        
        if( $forecastData['current'] ) {

            return json_encode($forecastData, JSON_PRETTY_PRINT);

        }

        $this->info(json_encode($forecastData, JSON_PRETTY_PRINT));
        $this->info(json_encode([
            'notes' => 'You need to go through the process of updating the interpretation of the response within ' . __FILE__,
        ]));

        throw new Exception("Failed to fetch weather data: " . $forecastData->body());

    }
}