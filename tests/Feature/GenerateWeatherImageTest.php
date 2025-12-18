<?php

namespace Tests\Feature;

use App\Console\Commands\GenerateWeatherImage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class GenerateWeatherImageTest extends TestCase
{
    /**
     * Test that the GenerateWeatherImage command runs successfully for the weather
     *
     * @return void
     */
    public function test_generate_weather_image_is_successful(): void
    {
        
        // Define the expected output message from the command
        $expectedOutput = 'Image generated successfully at public/images/weather.png';

        // Call the artisan command using the fluent API
        $this->artisan(GenerateWeatherImage::class)
             ->expectsOutput($expectedOutput)
             ->assertSuccessful();

    }

    /**
     * Test that the GenerateWeatherImage command runs successfully for the clock
     *
     * @return void
     */
    public function test_generate_clock_image_is_successful(): void
    {
        
        // Define the expected output message from the command
        $expectedOutput = 'Image generated successfully at public/images/clock.png';

        // Call the artisan command using the fluent API
        $this->artisan(GenerateWeatherImage::class)
             ->expectsOutput($expectedOutput)
             ->assertSuccessful();

    }

}