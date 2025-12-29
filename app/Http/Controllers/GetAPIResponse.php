<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class GetAPIResponse extends Controller
{
    public function getWeatherForecast( $latitude,$longitude,$fetchDays = 7 )
    {
        // Step 1: Get metadata for the location
        $pointsUrl = "https://api.weather.gov/points/{$latitude},{$longitude}";
        /*
        $pointsResponse = Http::withHeaders([
            'User-Agent' => 'Jadissa/jWeather)'
        ])->get($pointsUrl)->json();
        */
        $pointsUrl = "https://api.weather.gov/points/{$latitude},{$longitude}";
        // The NWS API documentation suggests including a User-Agent header for identification
        $pointsResponse = Http::withHeaders([
            'User-Agent' => 'Jadissa/jWeather (your_email@example.com)' // Replace with your app name and email
        ])->get($pointsUrl);

        if ($pointsResponse->failed()) {
            // Handle error, e.g., log or return an error message
            return ['error' => 'Failed to retrieve location metadata.'];
        }

        function fahrenheitToCelsius($fahrenheit) {
            return round(($fahrenheit - 32) * 5/9, 1);
        }

        $pointsData = $pointsResponse->json();
        $forecastUrl = $pointsData['properties']['forecast']; // URL for the detailed forecast

        // Step 2: Get the detailed forecast
        $forecastResponse = Http::withHeaders([
            'User-Agent' => 'Jadissa/jWeather (your_email@example.com)'
        ])->get($forecastUrl);

        if ($forecastResponse->failed()) {
            // Handle error
            return ['error' => 'Failed to retrieve forecast data.'];
        }

        $forecastData = $forecastResponse->json();

        // Extract current weather (first period of the forecast)
        $currentWeather = $forecastData['properties']['periods'][0];
        $currentWeather['temperatureCelsius'] = fahrenheitToCelsius($currentWeather['temperature']);

        // Extract expected weather for a duration of days
        // The weather.gov API typically provides a 7-day forecast (14 periods for day/night)
        // Adjust the loop to fetch the desired number of days
        $expectedWeather = [];
        for ($i = 0; $i < min(count($forecastData['properties']['periods']), $fetchDays * 2); $i++) {
            $period = $forecastData['properties']['periods'][$i];
            // Add Celsius temperature to each period object
            $period['temperatureCelsius'] = fahrenheitToCelsius($period['temperature']);
            $expectedWeather[] = $period;
        }

        // Step 3: Get active alerts for the point location
        // The NWS API provides an endpoint to query active alerts by specific lat/lon points
        $alertsUrl = "https://api.weather.gov/alerts/active?point={$latitude},{$longitude}";
        $alertsResponse = Http::withHeaders([
            'User-Agent' => 'Jadissa/jWeather (your_email@example.com)'
        ])->get($alertsUrl);

        $activeAlerts = [];
        if ($alertsResponse->successful()) {
            $alertsData = $alertsResponse->json();
            // The alerts are in the 'features' array of the GeoJSON response
            $activeAlerts = $alertsData['features'];
        }

        return [
            'current' => $currentWeather,
            'expected' => $expectedWeather,
            'alerts' => $activeAlerts,
        ];
    }
}