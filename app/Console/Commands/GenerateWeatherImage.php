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
            $weatherData = json_decode( '{"location":{"name":"Sunland Park","region":"New Mexico","country":"United States of America","lat":31.796,"lon":-106.579,"tz_id":"America\/Denver","localtime_epoch":1756917290,"localtime":"2025-09-03 10:34"},"current":{"last_updated_epoch":1756917000,"last_updated":"2025-09-03 10:30","temp_c":26.1,"temp_f":79,"is_day":1,"condition":{"text":"Partly cloudy","icon":"\/\/cdn.weatherapi.com\/weather\/64x64\/day\/116.png","code":1003},"wind_mph":8.7,"wind_kph":14,"wind_degree":118,"wind_dir":"ESE","pressure_mb":1023,"pressure_in":30.22,"precip_mm":0,"precip_in":0,"humidity":35,"cloud":75,"feelslike_c":26,"feelslike_f":78.7,"windchill_c":27,"windchill_f":80.6,"heatindex_c":26.6,"heatindex_f":79.9,"dewpoint_c":11,"dewpoint_f":51.7,"vis_km":16,"vis_miles":9,"uv":4.2,"gust_mph":10,"gust_kph":16.2},"forecast":{"forecastday":[{"date":"2025-09-03","date_epoch":1756857600,"day":{"maxtemp_c":35.9,"maxtemp_f":96.6,"mintemp_c":19,"mintemp_f":66.2,"avgtemp_c":27,"avgtemp_f":80.6,"maxwind_mph":9.2,"maxwind_kph":14.8,"totalprecip_mm":0,"totalprecip_in":0,"totalsnow_cm":0,"avgvis_km":10,"avgvis_miles":6,"avghumidity":35,"daily_will_it_rain":0,"daily_chance_of_rain":0,"daily_will_it_snow":0,"daily_chance_of_snow":0,"condition":{"text":"Cloudy ","icon":"\/\/cdn.weatherapi.com\/weather\/64x64\/day\/119.png","code":1006},"uv":2.3},"astro":{"sunrise":"06:43 AM","sunset":"07:28 PM","moonrise":"05:09 PM","moonset":"02:12 AM","moon_phase":"Waxing Gibbous","moon_illumination":75,"is_moon_up":1,"is_sun_up":0},"hour":[{"time_epoch":1756879200,"time":"2025-09-03 00:00","temp_c":25.1,"temp_f":77.2,"is_day":0,"condition":{"text":"Clear ","icon":"\/\/cdn.weatherapi.com\/weather\/64x64\/night\/113.png","code":1000},"wind_mph":8.1,"wind_kph":13,"wind_degree":98,"wind_dir":"E","pressure_mb":1016,"pressure_in":30.01,"precip_mm":0,"precip_in":0,"snow_cm":0,"humidity":37,"cloud":0,"feelslike_c":25.3,"feelslike_f":77.6,"windchill_c":25.1,"windchill_f":77.2,"heatindex_c":25.3,"heatindex_f":77.6,"dewpoint_c":9.5,"dewpoint_f":49.1,"will_it_rain":0,"chance_of_rain":0,"will_it_snow":0,"chance_of_snow":0,"vis_km":10,"vis_miles":6,"gust_mph":12.9,"gust_kph":20.8,"uv":0},{"time_epoch":1756882800,"time":"2025-09-03 01:00","temp_c":24.2,"temp_f":75.6,"is_day":0,"condition":{"text":"Clear ","icon":"\/\/cdn.weatherapi.com\/weather\/64x64\/night\/113.png","code":1000},"wind_mph":8.1,"wind_kph":13,"wind_degree":96,"wind_dir":"E","pressure_mb":1016,"pressure_in":30.02,"precip_mm":0,"precip_in":0,"snow_cm":0,"humidity":40,"cloud":0,"feelslike_c":25,"feelslike_f":77,"windchill_c":24.2,"windchill_f":75.6,"heatindex_c":25,"heatindex_f":77,"dewpoint_c":9.8,"dewpoint_f":49.6,"will_it_rain":0,"chance_of_rain":0,"will_it_snow":0,"chance_of_snow":0,"vis_km":10,"vis_miles":6,"gust_mph":12.8,"gust_kph":20.6,"uv":0},{"time_epoch":1756886400,"time":"2025-09-03 02:00","temp_c":23.3,"temp_f":74,"is_day":0,"condition":{"text":"Clear ","icon":"\/\/cdn.weatherapi.com\/weather\/64x64\/night\/113.png","code":1000},"wind_mph":8.1,"wind_kph":13,"wind_degree":89,"wind_dir":"E","pressure_mb":1017,"pressure_in":30.03,"precip_mm":0,"precip_in":0,"snow_cm":0,"humidity":43,"cloud":1,"feelslike_c":24.7,"feelslike_f":76.5,"windchill_c":23.3,"windchill_f":74,"heatindex_c":24.7,"heatindex_f":76.5,"dewpoint_c":10.1,"dewpoint_f":50.2,"will_it_rain":0,"chance_of_rain":0,"will_it_snow":0,"chance_of_snow":0,"vis_km":10,"vis_miles":6,"gust_mph":12.6,"gust_kph":20.3,"uv":0},{"time_epoch":1756890000,"time":"2025-09-03 03:00","temp_c":22.3,"temp_f":72.2,"is_day":0,"condition":{"text":"Clear ","icon":"\/\/cdn.weatherapi.com\/weather\/64x64\/night\/113.png","code":1000},"wind_mph":8.1,"wind_kph":13,"wind_degree":98,"wind_dir":"E","pressure_mb":1017,"pressure_in":30.03,"precip_mm":0,"precip_in":0,"snow_cm":0,"humidity":47,"cloud":0,"feelslike_c":24.5,"feelslike_f":76.1,"windchill_c":22.3,"windchill_f":72.2,"heatindex_c":24.5,"heatindex_f":76.1,"dewpoint_c":10.5,"dewpoint_f":50.9,"will_it_rain":0,"chance_of_rain":0,"will_it_snow":0,"chance_of_snow":0,"vis_km":10,"vis_miles":6,"gust_mph":12.5,"gust_kph":20.2,"uv":0},{"time_epoch":1756893600,"time":"2025-09-03 04:00","temp_c":21.3,"temp_f":70.4,"is_day":0,"condition":{"text":"Clear ","icon":"\/\/cdn.weatherapi.com\/weather\/64x64\/night\/113.png","code":1000},"wind_mph":8.3,"wind_kph":13.3,"wind_degree":107,"wind_dir":"ESE","pressure_mb":1017,"pressure_in":30.03,"precip_mm":0,"precip_in":0,"snow_cm":0,"humidity":51,"cloud":0,"feelslike_c":21.3,"feelslike_f":70.4,"windchill_c":21.3,"windchill_f":70.4,"heatindex_c":22.4,"heatindex_f":72.3,"dewpoint_c":10.8,"dewpoint_f":51.4,"will_it_rain":0,"chance_of_rain":0,"will_it_snow":0,"chance_of_snow":0,"vis_km":10,"vis_miles":6,"gust_mph":12.9,"gust_kph":20.8,"uv":0},{"time_epoch":1756897200,"time":"2025-09-03 05:00","temp_c":20.5,"temp_f":68.9,"is_day":0,"condition":{"text":"Clear ","icon":"\/\/cdn.weatherapi.com\/weather\/64x64\/night\/113.png","code":1000},"wind_mph":8.5,"wind_kph":13.7,"wind_degree":115,"wind_dir":"ESE","pressure_mb":1017,"pressure_in":30.03,"precip_mm":0,"precip_in":0,"snow_cm":0,"humidity":55,"cloud":2,"feelslike_c":20.5,"feelslike_f":68.9,"windchill_c":20.5,"windchill_f":68.9,"heatindex_c":21.1,"heatindex_f":69.9,"dewpoint_c":11.1,"dewpoint_f":51.9,"will_it_rain":0,"chance_of_rain":0,"will_it_snow":0,"chance_of_snow":0,"vis_km":10,"vis_miles":6,"gust_mph":13.5,"gust_kph":21.7,"uv":0},{"time_epoch":1756900800,"time":"2025-09-03 06:00","temp_c":19.8,"temp_f":67.6,"is_day":0,"condition":{"text":"Clear ","icon":"\/\/cdn.weatherapi.com\/weather\/64x64\/night\/113.png","code":1000},"wind_mph":8.3,"wind_kph":13.3,"wind_degree":117,"wind_dir":"ESE","pressure_mb":1017,"pressure_in":30.04,"precip_mm":0,"precip_in":0,"snow_cm":0,"humidity":59,"cloud":9,"feelslike_c":19.8,"feelslike_f":67.6,"windchill_c":19.8,"windchill_f":67.6,"heatindex_c":20,"heatindex_f":68,"dewpoint_c":11.5,"dewpoint_f":52.6,"will_it_rain":0,"chance_of_rain":0,"will_it_snow":0,"chance_of_snow":0,"vis_km":10,"vis_miles":6,"gust_mph":13.5,"gust_kph":21.7,"uv":0},{"time_epoch":1756904400,"time":"2025-09-03 07:00","temp_c":20,"temp_f":68,"is_day":1,"condition":{"text":"Cloudy ","icon":"\/\/cdn.weatherapi.com\/weather\/64x64\/day\/119.png","code":1006},"wind_mph":7.6,"wind_kph":12.2,"wind_degree":111,"wind_dir":"ESE","pressure_mb":1018,"pressure_in":30.06,"precip_mm":0,"precip_in":0,"snow_cm":0,"humidity":63,"cloud":74,"feelslike_c":20,"feelslike_f":68,"windchill_c":20,"windchill_f":68,"heatindex_c":20.2,"heatindex_f":68.3,"dewpoint_c":11.8,"dewpoint_f":53.2,"will_it_rain":0,"chance_of_rain":0,"will_it_snow":0,"chance_of_snow":0,"vis_km":10,"vis_miles":6,"gust_mph":12.3,"gust_kph":19.8,"uv":0},{"time_epoch":1756908000,"time":"2025-09-03 08:00","temp_c":22,"temp_f":71.5,"is_day":1,"condition":{"text":"Cloudy ","icon":"\/\/cdn.weatherapi.com\/weather\/64x64\/day\/119.png","code":1006},"wind_mph":7.2,"wind_kph":11.5,"wind_degree":109,"wind_dir":"ESE","pressure_mb":1018,"pressure_in":30.06,"precip_mm":0,"precip_in":0,"snow_cm":0,"humidity":57,"cloud":67,"feelslike_c":22,"feelslike_f":71.5,"windchill_c":22,"windchill_f":71.5,"heatindex_c":22.5,"heatindex_f":72.6,"dewpoint_c":11.5,"dewpoint_f":52.7,"will_it_rain":0,"chance_of_rain":0,"will_it_snow":0,"chance_of_snow":0,"vis_km":10,"vis_miles":6,"gust_mph":9.7,"gust_kph":15.6,"uv":0.6},{"time_epoch":1756911600,"time":"2025-09-03 09:00","temp_c":24.3,"temp_f":75.8,"is_day":1,"condition":{"text":"Cloudy ","icon":"\/\/cdn.weatherapi.com\/weather\/64x64\/day\/119.png","code":1006},"wind_mph":8.1,"wind_kph":13,"wind_degree":113,"wind_dir":"ESE","pressure_mb":1017,"pressure_in":30.04,"precip_mm":0,"precip_in":0,"snow_cm":0,"humidity":44,"cloud":77,"feelslike_c":24.5,"feelslike_f":76.1,"windchill_c":24.3,"windchill_f":75.8,"heatindex_c":24.5,"heatindex_f":76.1,"dewpoint_c":10.9,"dewpoint_f":51.7,"will_it_rain":0,"chance_of_rain":0,"will_it_snow":0,"chance_of_snow":0,"vis_km":10,"vis_miles":6,"gust_mph":9.3,"gust_kph":14.9,"uv":2},{"time_epoch":1756915200,"time":"2025-09-03 10:00","temp_c":26.1,"temp_f":79,"is_day":1,"condition":{"text":"Partly cloudy","icon":"\/\/cdn.weatherapi.com\/weather\/64x64\/day\/116.png","code":1003},"wind_mph":8.7,"wind_kph":14,"wind_degree":118,"wind_dir":"ESE","pressure_mb":1023,"pressure_in":30.22,"precip_mm":0,"precip_in":0,"snow_cm":0,"humidity":35,"cloud":75,"feelslike_c":26.6,"feelslike_f":79.9,"windchill_c":27,"windchill_f":80.6,"heatindex_c":26.6,"heatindex_f":79.9,"dewpoint_c":11,"dewpoint_f":51.7,"will_it_rain":0,"chance_of_rain":0,"will_it_snow":0,"chance_of_snow":0,"vis_km":16,"vis_miles":9,"gust_mph":10,"gust_kph":16.2,"uv":4.2},{"time_epoch":1756918800,"time":"2025-09-03 11:00","temp_c":30.5,"temp_f":86.9,"is_day":1,"condition":{"text":"Overcast ","icon":"\/\/cdn.weatherapi.com\/weather\/64x64\/day\/122.png","code":1009},"wind_mph":8.5,"wind_kph":13.7,"wind_degree":127,"wind_dir":"SE","pressure_mb":1016,"pressure_in":30.01,"precip_mm":0,"precip_in":0,"snow_cm":0,"humidity":31,"cloud":90,"feelslike_c":29.8,"feelslike_f":85.7,"windchill_c":30.5,"windchill_f":86.9,"heatindex_c":29.8,"heatindex_f":85.7,"dewpoint_c":10.8,"dewpoint_f":51.5,"will_it_rain":0,"chance_of_rain":0,"will_it_snow":0,"chance_of_snow":0,"vis_km":10,"vis_miles":6,"gust_mph":9.8,"gust_kph":15.7,"uv":7},{"time_epoch":1756922400,"time":"2025-09-03 12:00","temp_c":31.6,"temp_f":88.8,"is_day":1,"condition":{"text":"Sunny","icon":"\/\/cdn.weatherapi.com\/weather\/64x64\/day\/113.png","code":1000},"wind_mph":6.9,"wind_kph":11.2,"wind_degree":133,"wind_dir":"SE","pressure_mb":1016,"pressure_in":29.99,"precip_mm":0,"precip_in":0,"snow_cm":0,"humidity":26,"cloud":2,"feelslike_c":30.2,"feelslike_f":86.3,"windchill_c":31.6,"windchill_f":88.8,"heatindex_c":30.2,"heatindex_f":86.3,"dewpoint_c":11.7,"dewpoint_f":53,"will_it_rain":0,"chance_of_rain":0,"will_it_snow":0,"chance_of_snow":0,"vis_km":10,"vis_miles":6,"gust_mph":8,"gust_kph":12.9,"uv":9.2},{"time_epoch":1756926000,"time":"2025-09-03 13:00","temp_c":32.3,"temp_f":90.1,"is_day":1,"condition":{"text":"Partly Cloudy ","icon":"\/\/cdn.weatherapi.com\/weather\/64x64\/day\/116.png","code":1003},"wind_mph":6,"wind_kph":9.7,"wind_degree":113,"wind_dir":"ESE","pressure_mb":1015,"pressure_in":29.96,"precip_mm":0,"precip_in":0,"snow_cm":0,"humidity":20,"cloud":30,"feelslike_c":30.5,"feelslike_f":86.9,"windchill_c":32.3,"windchill_f":90.1,"heatindex_c":30.5,"heatindex_f":86.9,"dewpoint_c":7.1,"dewpoint_f":44.8,"will_it_rain":0,"chance_of_rain":0,"will_it_snow":0,"chance_of_snow":0,"vis_km":10,"vis_miles":6,"gust_mph":7,"gust_kph":11.2,"uv":10},{"time_epoch":1756929600,"time":"2025-09-03 14:00","temp_c":34.1,"temp_f":93.4,"is_day":1,"condition":{"text":"Cloudy ","icon":"\/\/cdn.weatherapi.com\/weather\/64x64\/day\/119.png","code":1006},"wind_mph":6.9,"wind_kph":11.2,"wind_degree":97,"wind_dir":"E","pressure_mb":1014,"pressure_in":29.94,"precip_mm":0,"precip_in":0,"snow_cm":0,"humidity":19,"cloud":69,"feelslike_c":32.4,"feelslike_f":90.3,"windchill_c":34.1,"windchill_f":93.4,"heatindex_c":32.4,"heatindex_f":90.3,"dewpoint_c":6.2,"dewpoint_f":43.2,"will_it_rain":0,"chance_of_rain":0,"will_it_snow":0,"chance_of_snow":0,"vis_km":10,"vis_miles":6,"gust_mph":8,"gust_kph":12.9,"uv":9.1},{"time_epoch":1756933200,"time":"2025-09-03 15:00","temp_c":33.5,"temp_f":92.4,"is_day":1,"condition":{"text":"Sunny","icon":"\/\/cdn.weatherapi.com\/weather\/64x64\/day\/113.png","code":1000},"wind_mph":7.8,"wind_kph":12.6,"wind_degree":102,"wind_dir":"ESE","pressure_mb":1013,"pressure_in":29.91,"precip_mm":0,"precip_in":0,"snow_cm":0,"humidity":20,"cloud":3,"feelslike_c":31.6,"feelslike_f":88.9,"windchill_c":33.5,"windchill_f":92.4,"heatindex_c":31.6,"heatindex_f":88.9,"dewpoint_c":9.4,"dewpoint_f":49,"will_it_rain":0,"chance_of_rain":0,"will_it_snow":0,"chance_of_snow":0,"vis_km":10,"vis_miles":6,"gust_mph":9,"gust_kph":14.5,"uv":6.7},{"time_epoch":1756936800,"time":"2025-09-03 16:00","temp_c":33,"temp_f":91.3,"is_day":1,"condition":{"text":"Cloudy ","icon":"\/\/cdn.weatherapi.com\/weather\/64x64\/day\/119.png","code":1006},"wind_mph":7.8,"wind_kph":12.6,"wind_degree":111,"wind_dir":"ESE","pressure_mb":1012,"pressure_in":29.88,"precip_mm":0,"precip_in":0,"snow_cm":0,"humidity":18,"cloud":84,"feelslike_c":30.9,"feelslike_f":87.6,"windchill_c":33,"windchill_f":91.3,"heatindex_c":30.9,"heatindex_f":87.6,"dewpoint_c":5.9,"dewpoint_f":42.5,"will_it_rain":0,"chance_of_rain":0,"will_it_snow":0,"chance_of_snow":0,"vis_km":10,"vis_miles":6,"gust_mph":9,"gust_kph":14.5,"uv":4.4},{"time_epoch":1756940400,"time":"2025-09-03 17:00","temp_c":32.6,"temp_f":90.7,"is_day":1,"condition":{"text":"Cloudy ","icon":"\/\/cdn.weatherapi.com\/weather\/64x64\/day\/119.png","code":1006},"wind_mph":7.6,"wind_kph":12.2,"wind_degree":126,"wind_dir":"SE","pressure_mb":1011,"pressure_in":29.86,"precip_mm":0,"precip_in":0,"snow_cm":0,"humidity":19,"cloud":73,"feelslike_c":30.5,"feelslike_f":86.8,"windchill_c":32.6,"windchill_f":90.7,"heatindex_c":30.5,"heatindex_f":86.8,"dewpoint_c":5.7,"dewpoint_f":42.3,"will_it_rain":0,"chance_of_rain":0,"will_it_snow":0,"chance_of_snow":0,"vis_km":10,"vis_miles":6,"gust_mph":8.8,"gust_kph":14.2,"uv":2.2},{"time_epoch":1756944000,"time":"2025-09-03 18:00","temp_c":31.4,"temp_f":88.6,"is_day":1,"condition":{"text":"Sunny","icon":"\/\/cdn.weatherapi.com\/weather\/64x64\/day\/113.png","code":1000},"wind_mph":7.6,"wind_kph":12.2,"wind_degree":145,"wind_dir":"SE","pressure_mb":1011,"pressure_in":29.87,"precip_mm":0,"precip_in":0,"snow_cm":0,"humidity":19,"cloud":20,"feelslike_c":29.4,"feelslike_f":84.9,"windchill_c":31.4,"windchill_f":88.6,"heatindex_c":29.4,"heatindex_f":84.9,"dewpoint_c":5.8,"dewpoint_f":42.4,"will_it_rain":0,"chance_of_rain":0,"will_it_snow":0,"chance_of_snow":0,"vis_km":10,"vis_miles":6,"gust_mph":9.3,"gust_kph":15,"uv":0.7},{"time_epoch":1756947600,"time":"2025-09-03 19:00","temp_c":29.9,"temp_f":85.8,"is_day":1,"condition":{"text":"Partly Cloudy ","icon":"\/\/cdn.weatherapi.com\/weather\/64x64\/day\/116.png","code":1003},"wind_mph":7.4,"wind_kph":11.9,"wind_degree":158,"wind_dir":"SSE","pressure_mb":1012,"pressure_in":29.87,"precip_mm":0,"precip_in":0,"snow_cm":0,"humidity":22,"cloud":45,"feelslike_c":28.1,"feelslike_f":82.6,"windchill_c":29.9,"windchill_f":85.8,"heatindex_c":28.1,"heatindex_f":82.6,"dewpoint_c":6.4,"dewpoint_f":43.5,"will_it_rain":0,"chance_of_rain":0,"will_it_snow":0,"chance_of_snow":0,"vis_km":10,"vis_miles":6,"gust_mph":9.8,"gust_kph":15.8,"uv":0},{"time_epoch":1756951200,"time":"2025-09-03 20:00","temp_c":28.9,"temp_f":84.1,"is_day":0,"condition":{"text":"Clear ","icon":"\/\/cdn.weatherapi.com\/weather\/64x64\/night\/113.png","code":1000},"wind_mph":7.6,"wind_kph":12.2,"wind_degree":164,"wind_dir":"SSE","pressure_mb":1012,"pressure_in":29.89,"precip_mm":0,"precip_in":0,"snow_cm":0,"humidity":26,"cloud":22,"feelslike_c":27.3,"feelslike_f":81.1,"windchill_c":28.9,"windchill_f":84.1,"heatindex_c":27.3,"heatindex_f":81.1,"dewpoint_c":7,"dewpoint_f":44.5,"will_it_rain":0,"chance_of_rain":0,"will_it_snow":0,"chance_of_snow":0,"vis_km":10,"vis_miles":6,"gust_mph":11.7,"gust_kph":18.8,"uv":0},{"time_epoch":1756954800,"time":"2025-09-03 21:00","temp_c":27.6,"temp_f":81.6,"is_day":0,"condition":{"text":"Clear ","icon":"\/\/cdn.weatherapi.com\/weather\/64x64\/night\/113.png","code":1000},"wind_mph":8.5,"wind_kph":13.7,"wind_degree":165,"wind_dir":"SSE","pressure_mb":1013,"pressure_in":29.9,"precip_mm":0,"precip_in":0,"snow_cm":0,"humidity":25,"cloud":0,"feelslike_c":26.4,"feelslike_f":79.6,"windchill_c":27.6,"windchill_f":81.6,"heatindex_c":26.4,"heatindex_f":79.6,"dewpoint_c":6.1,"dewpoint_f":43,"will_it_rain":0,"chance_of_rain":0,"will_it_snow":0,"chance_of_snow":0,"vis_km":10,"vis_miles":6,"gust_mph":13.7,"gust_kph":22.1,"uv":0},{"time_epoch":1756958400,"time":"2025-09-03 22:00","temp_c":26.6,"temp_f":79.9,"is_day":0,"condition":{"text":"Clear ","icon":"\/\/cdn.weatherapi.com\/weather\/64x64\/night\/113.png","code":1000},"wind_mph":8.7,"wind_kph":14,"wind_degree":170,"wind_dir":"S","pressure_mb":1013,"pressure_in":29.93,"precip_mm":0,"precip_in":0,"snow_cm":0,"humidity":31,"cloud":22,"feelslike_c":25.9,"feelslike_f":78.6,"windchill_c":26.6,"windchill_f":79.9,"heatindex_c":25.9,"heatindex_f":78.6,"dewpoint_c":7.8,"dewpoint_f":46,"will_it_rain":0,"chance_of_rain":0,"will_it_snow":0,"chance_of_snow":0,"vis_km":10,"vis_miles":6,"gust_mph":14.4,"gust_kph":23.1,"uv":0},{"time_epoch":1756962000,"time":"2025-09-03 23:00","temp_c":25.7,"temp_f":78.2,"is_day":0,"condition":{"text":"Clear ","icon":"\/\/cdn.weatherapi.com\/weather\/64x64\/night\/113.png","code":1000},"wind_mph":9.2,"wind_kph":14.8,"wind_degree":168,"wind_dir":"SSE","pressure_mb":1014,"pressure_in":29.94,"precip_mm":0,"precip_in":0,"snow_cm":0,"humidity":33,"cloud":3,"feelslike_c":25.2,"feelslike_f":77.4,"windchill_c":25.7,"windchill_f":78.2,"heatindex_c":25.2,"heatindex_f":77.4,"dewpoint_c":8.2,"dewpoint_f":46.8,"will_it_rain":0,"chance_of_rain":0,"will_it_snow":0,"chance_of_snow":0,"vis_km":10,"vis_miles":6,"gust_mph":14.5,"gust_kph":23.3,"uv":0}]},{"date":"2025-09-04","date_epoch":1756944000,"day":{"maxtemp_c":30.3,"maxtemp_f":86.5,"mintemp_c":20,"mintemp_f":68,"avgtemp_c":25.8,"avgtemp_f":78.4,"maxwind_mph":25.3,"maxwind_kph":40.7,"totalprecip_mm":0,"totalprecip_in":0,"totalsnow_cm":0,"avgvis_km":9.8,"avgvis_miles":6,"avghumidity":38,"daily_will_it_rain":0,"daily_chance_of_rain":0,"daily_will_it_snow":0,"daily_chance_of_snow":0,"condition":{"text":"Cloudy ","icon":"\/\/cdn.weatherapi.com\/weather\/64x64\/day\/119.png","code":1006},"uv":2.1},"astro":{"sunrise":"06:44 AM","sunset":"07:26 PM","moonrise":"05:50 PM","moonset":"03:15 AM","moon_phase":"Waxing Gibbous","moon_illumination":84,"is_moon_up":1,"is_sun_up":1},"hour":[{"time_epoch":1756965600,"time":"2025-09-04 00:00","temp_c":24.5,"temp_f":76.1,"is_day":0,"condition":{"text":"Partly Cloudy ","icon":"\/\/cdn.weatherapi.com\/weather\/64x64\/night\/116.png","code":1003},"wind_mph":9.6,"wind_kph":15.5,"wind_degree":169,"wind_dir":"S","pressure_mb":1014,"pressure_in":29.94,"precip_mm":0,"precip_in":0,"snow_cm":0,"humidity":30,"cloud":35,"feelslike_c":24.8,"feelslike_f":76.6,"windchill_c":24.5,"windchill_f":76.1,"heatindex_c":24.8,"heatindex_f":76.6,"dewpoint_c":6.1,"dewpoint_f":42.9,"will_it_rain":0,"chance_of_rain":0,"will_it_snow":0,"chance_of_snow":0,"vis_km":10,"vis_miles":6,"gust_mph":15.6,"gust_kph":25.1,"uv":0},{"time_epoch":1756969200,"time":"2025-09-04 01:00","temp_c":23.5,"temp_f":74.4,"is_day":0,"condition":{"text":"Partly Cloudy ","icon":"\/\/cdn.weatherapi.com\/weather\/64x64\/night\/116.png","code":1003},"wind_mph":7.8,"wind_kph":12.6,"wind_degree":183,"wind_dir":"S","pressure_mb":1014,"pressure_in":29.94,"precip_mm":0,"precip_in":0,"snow_cm":0,"humidity":36,"cloud":18,"feelslike_c":24.5,"feelslike_f":76.1,"windchill_c":23.5,"windchill_f":74.4,"heatindex_c":24.5,"heatindex_f":76.1,"dewpoint_c":7.1,"dewpoint_f":44.8,"will_it_rain":0,"chance_of_rain":0,"will_it_snow":0,"chance_of_snow":0,"vis_km":10,"vis_miles":6,"gust_mph":12.8,"gust_kph":20.6,"uv":0},{"time_epoch":1756972800,"time":"2025-09-04 02:00","temp_c":22.7,"temp_f":72.9,"is_day":0,"condition":{"text":"Partly Cloudy ","icon":"\/\/cdn.weatherapi.com\/weather\/64x64\/night\/116.png","code":1003},"wind_mph":6.5,"wind_kph":10.4,"wind_degree":206,"wind_dir":"SSW","pressure_mb":1014,"pressure_in":29.94,"precip_mm":0,"precip_in":0,"snow_cm":0,"humidity":39,"cloud":10,"feelslike_c":24.3,"feelslike_f":75.7,"windchill_c":22.7,"windchill_f":72.9,"heatindex_c":24.3,"heatindex_f":75.7,"dewpoint_c":7.7,"dewpoint_f":45.8,"will_it_rain":0,"chance_of_rain":0,"will_it_snow":0,"chance_of_snow":0,"vis_km":10,"vis_miles":6,"gust_mph":10.5,"gust_kph":17,"uv":0},{"time_epoch":1756976400,"time":"2025-09-04 03:00","temp_c":21.8,"temp_f":71.3,"is_day":0,"condition":{"text":"Clear ","icon":"\/\/cdn.weatherapi.com\/weather\/64x64\/night\/113.png","code":1000},"wind_mph":6.9,"wind_kph":11.2,"wind_degree":204,"wind_dir":"SSW","pressure_mb":1014,"pressure_in":29.94,"precip_mm":0,"precip_in":0,"snow_cm":0,"humidity":41,"cloud":1,"feelslike_c":21.8,"feelslike_f":71.3,"windchill_c":21.8,"windchill_f":71.3,"heatindex_c":23.2,"heatindex_f":73.7,"dewpoint_c":8.2,"dewpoint_f":46.8,"will_it_rain":0,"chance_of_rain":0,"will_it_snow":0,"chance_of_snow":0,"vis_km":10,"vis_miles":6,"gust_mph":11.2,"gust_kph":18,"uv":0},{"time_epoch":1756980000,"time":"2025-09-04 04:00","temp_c":21.2,"temp_f":70.1,"is_day":0,"condition":{"text":"Clear ","icon":"\/\/cdn.weatherapi.com\/weather\/64x64\/night\/113.png","code":1000},"wind_mph":4.7,"wind_kph":7.6,"wind_degree":206,"wind_dir":"SSW","pressure_mb":1014,"pressure_in":29.95,"precip_mm":0,"precip_in":0,"snow_cm":0,"humidity":48,"cloud":3,"feelslike_c":21.2,"feelslike_f":70.1,"windchill_c":21.2,"windchill_f":70.1,"heatindex_c":22.1,"heatindex_f":71.8,"dewpoint_c":9.4,"dewpoint_f":48.9,"will_it_rain":0,"chance_of_rain":0,"will_it_snow":0,"chance_of_snow":0,"vis_km":10,"vis_miles":6,"gust_mph":7.7,"gust_kph":12.3,"uv":0},{"time_epoch":1756983600,"time":"2025-09-04 05:00","temp_c":20.6,"temp_f":69,"is_day":0,"condition":{"text":"Clear ","icon":"\/\/cdn.weatherapi.com\/weather\/64x64\/night\/113.png","code":1000},"wind_mph":3.4,"wind_kph":5.4,"wind_degree":221,"wind_dir":"SW","pressure_mb":1014,"pressure_in":29.96,"precip_mm":0,"precip_in":0,"snow_cm":0,"humidity":51,"cloud":4,"feelslike_c":20.6,"feelslike_f":69,"windchill_c":20.6,"windchill_f":69,"heatindex_c":21,"heatindex_f":69.9,"dewpoint_c":9.9,"dewpoint_f":49.9,"will_it_rain":0,"chance_of_rain":0,"will_it_snow":0,"chance_of_snow":0,"vis_km":10,"vis_miles":6,"gust_mph":5.7,"gust_kph":9.1,"uv":0},{"time_epoch":1756987200,"time":"2025-09-04 06:00","temp_c":21.5,"temp_f":70.7,"is_day":0,"condition":{"text":"Clear ","icon":"\/\/cdn.weatherapi.com\/weather\/64x64\/night\/113.png","code":1000},"wind_mph":1.8,"wind_kph":2.9,"wind_degree":207,"wind_dir":"SSW","pressure_mb":1015,"pressure_in":29.96,"precip_mm":0,"precip_in":0,"snow_cm":0,"humidity":54,"cloud":5,"feelslike_c":21.5,"feelslike_f":70.7,"windchill_c":21.5,"windchill_f":70.7,"heatindex_c":21.9,"heatindex_f":71.4,"dewpoint_c":10.5,"dewpoint_f":50.9,"will_it_rain":0,"chance_of_rain":0,"will_it_snow":0,"chance_of_snow":0,"vis_km":10,"vis_miles":6,"gust_mph":3.1,"gust_kph":5,"uv":0},{"time_epoch":1756990800,"time":"2025-09-04 07:00","temp_c":22.5,"temp_f":72.6,"is_day":1,"condition":{"text":"Sunny","icon":"\/\/cdn.weatherapi.com\/weather\/64x64\/day\/113.png","code":1000},"wind_mph":1.3,"wind_kph":2.2,"wind_degree":140,"wind_dir":"SE","pressure_mb":1015,"pressure_in":29.97,"precip_mm":0,"precip_in":0,"snow_cm":0,"humidity":49,"cloud":11,"feelslike_c":23,"feelslike_f":73.3,"windchill_c":22.5,"windchill_f":72.6,"heatindex_c":23,"heatindex_f":73.3,"dewpoint_c":11,"dewpoint_f":51.8,"will_it_rain":0,"chance_of_rain":0,"will_it_snow":0,"chance_of_snow":0,"vis_km":10,"vis_miles":6,"gust_mph":2.4,"gust_kph":3.9,"uv":0},{"time_epoch":1756994400,"time":"2025-09-04 08:00","temp_c":23.7,"temp_f":74.6,"is_day":1,"condition":{"text":"Sunny","icon":"\/\/cdn.weatherapi.com\/weather\/64x64\/day\/113.png","code":1000},"wind_mph":3.6,"wind_kph":5.8,"wind_degree":141,"wind_dir":"SE","pressure_mb":1015,"pressure_in":29.97,"precip_mm":0,"precip_in":0,"snow_cm":0,"humidity":46,"cloud":14,"feelslike_c":24.2,"feelslike_f":75.5,"windchill_c":23.7,"windchill_f":74.6,"heatindex_c":24.2,"heatindex_f":75.5,"dewpoint_c":11.2,"dewpoint_f":52.2,"will_it_rain":0,"chance_of_rain":0,"will_it_snow":0,"chance_of_snow":0,"vis_km":10,"vis_miles":6,"gust_mph":5.1,"gust_kph":8.2,"uv":0.5},{"time_epoch":1756998000,"time":"2025-09-04 09:00","temp_c":25.6,"temp_f":78.1,"is_day":1,"condition":{"text":"Sunny","icon":"\/\/cdn.weatherapi.com\/weather\/64x64\/day\/113.png","code":1000},"wind_mph":6.7,"wind_kph":10.8,"wind_degree":159,"wind_dir":"SSE","pressure_mb":1015,"pressure_in":29.97,"precip_mm":0,"precip_in":0,"snow_cm":0,"humidity":43,"cloud":17,"feelslike_c":25.8,"feelslike_f":78.4,"windchill_c":25.6,"windchill_f":78.1,"heatindex_c":25.8,"heatindex_f":78.4,"dewpoint_c":11.5,"dewpoint_f":52.7,"will_it_rain":0,"chance_of_rain":0,"will_it_snow":0,"chance_of_snow":0,"vis_km":10,"vis_miles":6,"gust_mph":7.8,"gust_kph":12.5,"uv":1.7},{"time_epoch":1757001600,"time":"2025-09-04 10:00","temp_c":27.3,"temp_f":81.1,"is_day":1,"condition":{"text":"Partly Cloudy ","icon":"\/\/cdn.weatherapi.com\/weather\/64x64\/day\/116.png","code":1003},"wind_mph":7.4,"wind_kph":11.9,"wind_degree":160,"wind_dir":"SSE","pressure_mb":1014,"pressure_in":29.94,"precip_mm":0,"precip_in":0,"snow_cm":0,"humidity":37,"cloud":44,"feelslike_c":27,"feelslike_f":80.7,"windchill_c":27.3,"windchill_f":81.1,"heatindex_c":27,"heatindex_f":80.7,"dewpoint_c":11.3,"dewpoint_f":52.3,"will_it_rain":0,"chance_of_rain":0,"will_it_snow":0,"chance_of_snow":0,"vis_km":10,"vis_miles":6,"gust_mph":8.5,"gust_kph":13.7,"uv":3.7},{"time_epoch":1757005200,"time":"2025-09-04 11:00","temp_c":28.8,"temp_f":83.8,"is_day":1,"condition":{"text":"Cloudy ","icon":"\/\/cdn.weatherapi.com\/weather\/64x64\/day\/119.png","code":1006},"wind_mph":7.2,"wind_kph":11.5,"wind_degree":164,"wind_dir":"SSE","pressure_mb":1013,"pressure_in":29.93,"precip_mm":0,"precip_in":0,"snow_cm":0,"humidity":34,"cloud":58,"feelslike_c":28.2,"feelslike_f":82.7,"windchill_c":28.8,"windchill_f":83.8,"heatindex_c":28.2,"heatindex_f":82.7,"dewpoint_c":11.2,"dewpoint_f":52.2,"will_it_rain":0,"chance_of_rain":0,"will_it_snow":0,"chance_of_snow":0,"vis_km":10,"vis_miles":6,"gust_mph":8.2,"gust_kph":13.2,"uv":6.5},{"time_epoch":1757008800,"time":"2025-09-04 12:00","temp_c":29.3,"temp_f":84.7,"is_day":1,"condition":{"text":"Cloudy ","icon":"\/\/cdn.weatherapi.com\/weather\/64x64\/day\/119.png","code":1006},"wind_mph":7.2,"wind_kph":11.5,"wind_degree":177,"wind_dir":"S","pressure_mb":1013,"pressure_in":29.91,"precip_mm":0,"precip_in":0,"snow_cm":0,"humidity":31,"cloud":72,"feelslike_c":28.5,"feelslike_f":83.3,"windchill_c":29.3,"windchill_f":84.7,"heatindex_c":28.5,"heatindex_f":83.3,"dewpoint_c":11.1,"dewpoint_f":52,"will_it_rain":0,"chance_of_rain":0,"will_it_snow":0,"chance_of_snow":0,"vis_km":10,"vis_miles":6,"gust_mph":8.2,"gust_kph":13.2,"uv":8.3},{"time_epoch":1757012400,"time":"2025-09-04 13:00","temp_c":29.4,"temp_f":85,"is_day":1,"condition":{"text":"Cloudy ","icon":"\/\/cdn.weatherapi.com\/weather\/64x64\/day\/119.png","code":1006},"wind_mph":7.6,"wind_kph":12.2,"wind_degree":192,"wind_dir":"SSW","pressure_mb":1012,"pressure_in":29.89,"precip_mm":0,"precip_in":0,"snow_cm":0,"humidity":31,"cloud":86,"feelslike_c":28.5,"feelslike_f":83.3,"windchill_c":29.4,"windchill_f":85,"heatindex_c":28.5,"heatindex_f":83.3,"dewpoint_c":10.8,"dewpoint_f":51.4,"will_it_rain":0,"chance_of_rain":0,"will_it_snow":0,"chance_of_snow":0,"vis_km":10,"vis_miles":6,"gust_mph":8.7,"gust_kph":14.1,"uv":9.1},{"time_epoch":1757016000,"time":"2025-09-04 14:00","temp_c":29.4,"temp_f":84.8,"is_day":1,"condition":{"text":"Cloudy ","icon":"\/\/cdn.weatherapi.com\/weather\/64x64\/day\/119.png","code":1006},"wind_mph":12.3,"wind_kph":19.8,"wind_degree":215,"wind_dir":"SW","pressure_mb":1012,"pressure_in":29.88,"precip_mm":0,"precip_in":0,"snow_cm":0,"humidity":31,"cloud":93,"feelslike_c":28.4,"feelslike_f":83.1,"windchill_c":29.4,"windchill_f":84.8,"heatindex_c":28.4,"heatindex_f":83.1,"dewpoint_c":10.6,"dewpoint_f":51.1,"will_it_rain":0,"chance_of_rain":0,"will_it_snow":0,"chance_of_snow":0,"vis_km":10,"vis_miles":6,"gust_mph":14.2,"gust_kph":22.8,"uv":8.3},{"time_epoch":1757019600,"time":"2025-09-04 15:00","temp_c":29.4,"temp_f":84.8,"is_day":1,"condition":{"text":"Overcast ","icon":"\/\/cdn.weatherapi.com\/weather\/64x64\/day\/122.png","code":1009},"wind_mph":18.8,"wind_kph":30.2,"wind_degree":229,"wind_dir":"SW","pressure_mb":1012,"pressure_in":29.87,"precip_mm":0,"precip_in":0,"snow_cm":0,"humidity":31,"cloud":100,"feelslike_c":28.3,"feelslike_f":82.9,"windchill_c":29.4,"windchill_f":84.8,"heatindex_c":28.3,"heatindex_f":82.9,"dewpoint_c":10.4,"dewpoint_f":50.7,"will_it_rain":0,"chance_of_rain":0,"will_it_snow":0,"chance_of_snow":0,"vis_km":10,"vis_miles":6,"gust_mph":21.6,"gust_kph":34.8,"uv":5.8},{"time_epoch":1757023200,"time":"2025-09-04 16:00","temp_c":29.4,"temp_f":84.9,"is_day":1,"condition":{"text":"Overcast ","icon":"\/\/cdn.weatherapi.com\/weather\/64x64\/day\/122.png","code":1009},"wind_mph":21,"wind_kph":33.8,"wind_degree":247,"wind_dir":"WSW","pressure_mb":1011,"pressure_in":29.85,"precip_mm":0,"precip_in":0,"snow_cm":0,"humidity":29,"cloud":98,"feelslike_c":28.2,"feelslike_f":82.8,"windchill_c":29.4,"windchill_f":84.9,"heatindex_c":28.2,"heatindex_f":82.8,"dewpoint_c":9.7,"dewpoint_f":49.5,"will_it_rain":0,"chance_of_rain":0,"will_it_snow":0,"chance_of_snow":0,"vis_km":10,"vis_miles":6,"gust_mph":24.2,"gust_kph":38.9,"uv":3.7},{"time_epoch":1757026800,"time":"2025-09-04 17:00","temp_c":29.4,"temp_f":84.9,"is_day":1,"condition":{"text":"Overcast ","icon":"\/\/cdn.weatherapi.com\/weather\/64x64\/day\/122.png","code":1009},"wind_mph":18.3,"wind_kph":29.5,"wind_degree":246,"wind_dir":"WSW","pressure_mb":1010,"pressure_in":29.84,"precip_mm":0,"precip_in":0,"snow_cm":0,"humidity":29,"cloud":97,"feelslike_c":28.1,"feelslike_f":82.6,"windchill_c":29.4,"windchill_f":84.9,"heatindex_c":28.1,"heatindex_f":82.6,"dewpoint_c":9.3,"dewpoint_f":48.8,"will_it_rain":0,"chance_of_rain":0,"will_it_snow":0,"chance_of_snow":0,"vis_km":10,"vis_miles":6,"gust_mph":21.1,"gust_kph":33.9,"uv":1.6},{"time_epoch":1757030400,"time":"2025-09-04 18:00","temp_c":28.5,"temp_f":83.4,"is_day":1,"condition":{"text":"Overcast ","icon":"\/\/cdn.weatherapi.com\/weather\/64x64\/day\/122.png","code":1009},"wind_mph":19,"wind_kph":30.6,"wind_degree":230,"wind_dir":"SW","pressure_mb":1010,"pressure_in":29.83,"precip_mm":0,"precip_in":0,"snow_cm":0,"humidity":28,"cloud":96,"feelslike_c":27.6,"feelslike_f":81.6,"windchill_c":28.5,"windchill_f":83.4,"heatindex_c":27.6,"heatindex_f":81.6,"dewpoint_c":9,"dewpoint_f":48.2,"will_it_rain":0,"chance_of_rain":0,"will_it_snow":0,"chance_of_snow":0,"vis_km":10,"vis_miles":6,"gust_mph":22.3,"gust_kph":36,"uv":0.5},{"time_epoch":1757034000,"time":"2025-09-04 19:00","temp_c":27.7,"temp_f":81.9,"is_day":1,"condition":{"text":"Overcast ","icon":"\/\/cdn.weatherapi.com\/weather\/64x64\/day\/122.png","code":1009},"wind_mph":22.8,"wind_kph":36.7,"wind_degree":229,"wind_dir":"SW","pressure_mb":1011,"pressure_in":29.84,"precip_mm":0,"precip_in":0,"snow_cm":0,"humidity":33,"cloud":89,"feelslike_c":27,"feelslike_f":80.6,"windchill_c":27.7,"windchill_f":81.9,"heatindex_c":27,"heatindex_f":80.6,"dewpoint_c":9.7,"dewpoint_f":49.5,"will_it_rain":0,"chance_of_rain":0,"will_it_snow":0,"chance_of_snow":0,"vis_km":10,"vis_miles":6,"gust_mph":28.3,"gust_kph":45.5,"uv":0},{"time_epoch":1757037600,"time":"2025-09-04 20:00","temp_c":26.8,"temp_f":80.3,"is_day":0,"condition":{"text":"Overcast ","icon":"\/\/cdn.weatherapi.com\/weather\/64x64\/night\/122.png","code":1009},"wind_mph":23.3,"wind_kph":37.4,"wind_degree":234,"wind_dir":"SW","pressure_mb":1011,"pressure_in":29.85,"precip_mm":0,"precip_in":0,"snow_cm":0,"humidity":35,"cloud":85,"feelslike_c":26.5,"feelslike_f":79.6,"windchill_c":26.8,"windchill_f":80.3,"heatindex_c":26.5,"heatindex_f":79.6,"dewpoint_c":10.1,"dewpoint_f":50.2,"will_it_rain":0,"chance_of_rain":0,"will_it_snow":0,"chance_of_snow":0,"vis_km":10,"vis_miles":6,"gust_mph":29.6,"gust_kph":47.6,"uv":0},{"time_epoch":1757041200,"time":"2025-09-04 21:00","temp_c":26,"temp_f":78.8,"is_day":0,"condition":{"text":"Cloudy ","icon":"\/\/cdn.weatherapi.com\/weather\/64x64\/night\/119.png","code":1006},"wind_mph":25.3,"wind_kph":40.7,"wind_degree":210,"wind_dir":"SSW","pressure_mb":1011,"pressure_in":29.86,"precip_mm":0,"precip_in":0,"snow_cm":0,"humidity":38,"cloud":81,"feelslike_c":26.1,"feelslike_f":78.9,"windchill_c":26,"windchill_f":78.8,"heatindex_c":26.1,"heatindex_f":78.9,"dewpoint_c":10.5,"dewpoint_f":50.8,"will_it_rain":0,"chance_of_rain":0,"will_it_snow":0,"chance_of_snow":0,"vis_km":10,"vis_miles":6,"gust_mph":31.7,"gust_kph":51,"uv":0},{"time_epoch":1757044800,"time":"2025-09-04 22:00","temp_c":25.4,"temp_f":77.7,"is_day":0,"condition":{"text":"Cloudy ","icon":"\/\/cdn.weatherapi.com\/weather\/64x64\/night\/119.png","code":1006},"wind_mph":21.3,"wind_kph":34.2,"wind_degree":196,"wind_dir":"SSW","pressure_mb":1012,"pressure_in":29.88,"precip_mm":0,"precip_in":0,"snow_cm":0,"humidity":44,"cloud":91,"feelslike_c":25.8,"feelslike_f":78.4,"windchill_c":25.4,"windchill_f":77.7,"heatindex_c":25.8,"heatindex_f":78.4,"dewpoint_c":11.9,"dewpoint_f":53.5,"will_it_rain":0,"chance_of_rain":0,"will_it_snow":0,"chance_of_snow":0,"vis_km":10,"vis_miles":6,"gust_mph":26.3,"gust_kph":42.3,"uv":0},{"time_epoch":1757048400,"time":"2025-09-04 23:00","temp_c":24.8,"temp_f":76.7,"is_day":0,"condition":{"text":"Cloudy ","icon":"\/\/cdn.weatherapi.com\/weather\/64x64\/night\/119.png","code":1006},"wind_mph":10.1,"wind_kph":16.2,"wind_degree":209,"wind_dir":"SSW","pressure_mb":1012,"pressure_in":29.89,"precip_mm":0,"precip_in":0,"snow_cm":0,"humidity":47,"cloud":95,"feelslike_c":25.6,"feelslike_f":78.1,"windchill_c":24.8,"windchill_f":76.7,"heatindex_c":25.6,"heatindex_f":78.1,"dewpoint_c":12.7,"dewpoint_f":54.8,"will_it_rain":0,"chance_of_rain":0,"will_it_snow":0,"chance_of_snow":0,"vis_km":5,"vis_miles":3,"gust_mph":12,"gust_kph":19.3,"uv":0}]},{"date":"2025-09-05","date_epoch":1757030400,"day":{"maxtemp_c":26.4,"maxtemp_f":79.5,"mintemp_c":20,"mintemp_f":68,"avgtemp_c":23,"avgtemp_f":73.4,"maxwind_mph":14.1,"maxwind_kph":22.7,"totalprecip_mm":0,"totalprecip_in":0,"totalsnow_cm":0,"avgvis_km":9,"avgvis_miles":5,"avghumidity":57,"daily_will_it_rain":0,"daily_chance_of_rain":0,"daily_will_it_snow":0,"daily_chance_of_snow":0,"condition":{"text":"Overcast ","icon":"\/\/cdn.weatherapi.com\/weather\/64x64\/day\/122.png","code":1009},"uv":0.3},"astro":{"sunrise":"06:44 AM","sunset":"07:25 PM","moonrise":"06:26 PM","moonset":"04:21 AM","moon_phase":"Waxing Gibbous","moon_illumination":91,"is_moon_up":1,"is_sun_up":1},"hour":[{"time_epoch":1757052000,"time":"2025-09-05 00:00","temp_c":24.2,"temp_f":75.6,"is_day":0,"condition":{"text":"Overcast ","icon":"\/\/cdn.weatherapi.com\/weather\/64x64\/night\/122.png","code":1009},"wind_mph":6.3,"wind_kph":10.1,"wind_degree":270,"wind_dir":"W","pressure_mb":1013,"pressure_in":29.91,"precip_mm":0,"precip_in":0,"snow_cm":0,"humidity":51,"cloud":100,"feelslike_c":25.3,"feelslike_f":77.6,"windchill_c":24.2,"windchill_f":75.6,"heatindex_c":25.3,"heatindex_f":77.6,"dewpoint_c":13.4,"dewpoint_f":56.2,"will_it_rain":0,"chance_of_rain":0,"will_it_snow":0,"chance_of_snow":0,"vis_km":5,"vis_miles":3,"gust_mph":7.4,"gust_kph":12,"uv":0},{"time_epoch":1757055600,"time":"2025-09-05 01:00","temp_c":23.7,"temp_f":74.7,"is_day":0,"condition":{"text":"Overcast ","icon":"\/\/cdn.weatherapi.com\/weather\/64x64\/night\/122.png","code":1009},"wind_mph":8.9,"wind_kph":14.4,"wind_degree":291,"wind_dir":"WNW","pressure_mb":1013,"pressure_in":29.92,"precip_mm":0,"precip_in":0,"snow_cm":0,"humidity":52,"cloud":100,"feelslike_c":25.1,"feelslike_f":77.2,"windchill_c":23.7,"windchill_f":74.7,"heatindex_c":25.1,"heatindex_f":77.2,"dewpoint_c":13.2,"dewpoint_f":55.7,"will_it_rain":0,"chance_of_rain":0,"will_it_snow":0,"chance_of_snow":0,"vis_km":5,"vis_miles":3,"gust_mph":10.4,"gust_kph":16.8,"uv":0},{"time_epoch":1757059200,"time":"2025-09-05 02:00","temp_c":23.3,"temp_f":74,"is_day":0,"condition":{"text":"Overcast ","icon":"\/\/cdn.weatherapi.com\/weather\/64x64\/night\/122.png","code":1009},"wind_mph":9.6,"wind_kph":15.5,"wind_degree":311,"wind_dir":"NW","pressure_mb":1013,"pressure_in":29.93,"precip_mm":0,"precip_in":0,"snow_cm":0,"humidity":53,"cloud":100,"feelslike_c":24.9,"feelslike_f":76.9,"windchill_c":23.3,"windchill_f":74,"heatindex_c":24.9,"heatindex_f":76.9,"dewpoint_c":13,"dewpoint_f":55.5,"will_it_rain":0,"chance_of_rain":0,"will_it_snow":0,"chance_of_snow":0,"vis_km":5,"vis_miles":3,"gust_mph":11.4,"gust_kph":18.4,"uv":0},{"time_epoch":1757062800,"time":"2025-09-05 03:00","temp_c":22.8,"temp_f":73.1,"is_day":0,"condition":{"text":"Overcast ","icon":"\/\/cdn.weatherapi.com\/weather\/64x64\/night\/122.png","code":1009},"wind_mph":8.9,"wind_kph":14.4,"wind_degree":306,"wind_dir":"NW","pressure_mb":1014,"pressure_in":29.93,"precip_mm":0,"precip_in":0,"snow_cm":0,"humidity":53,"cloud":100,"feelslike_c":24.8,"feelslike_f":76.6,"windchill_c":22.8,"windchill_f":73.1,"heatindex_c":24.8,"heatindex_f":76.6,"dewpoint_c":12.9,"dewpoint_f":55.2,"will_it_rain":0,"chance_of_rain":0,"will_it_snow":0,"chance_of_snow":0,"vis_km":10,"vis_miles":6,"gust_mph":10.6,"gust_kph":17.1,"uv":0},{"time_epoch":1757066400,"time":"2025-09-05 04:00","temp_c":22.4,"temp_f":72.3,"is_day":0,"condition":{"text":"Overcast ","icon":"\/\/cdn.weatherapi.com\/weather\/64x64\/night\/122.png","code":1009},"wind_mph":9.2,"wind_kph":14.8,"wind_degree":316,"wind_dir":"NW","pressure_mb":1014,"pressure_in":29.93,"precip_mm":0,"precip_in":0,"snow_cm":0,"humidity":56,"cloud":87,"feelslike_c":24.7,"feelslike_f":76.4,"windchill_c":22.4,"windchill_f":72.3,"heatindex_c":24.7,"heatindex_f":76.4,"dewpoint_c":13.2,"dewpoint_f":55.8,"will_it_rain":0,"chance_of_rain":0,"will_it_snow":0,"chance_of_snow":0,"vis_km":10,"vis_miles":6,"gust_mph":11,"gust_kph":17.7,"uv":0},{"time_epoch":1757070000,"time":"2025-09-05 05:00","temp_c":22.1,"temp_f":71.7,"is_day":0,"condition":{"text":"Overcast ","icon":"\/\/cdn.weatherapi.com\/weather\/64x64\/night\/122.png","code":1009},"wind_mph":11,"wind_kph":17.6,"wind_degree":331,"wind_dir":"NNW","pressure_mb":1014,"pressure_in":29.93,"precip_mm":0,"precip_in":0,"snow_cm":0,"humidity":58,"cloud":80,"feelslike_c":24.6,"feelslike_f":76.3,"windchill_c":22.1,"windchill_f":71.7,"heatindex_c":24.6,"heatindex_f":76.3,"dewpoint_c":13.4,"dewpoint_f":56,"will_it_rain":0,"chance_of_rain":0,"will_it_snow":0,"chance_of_snow":0,"vis_km":10,"vis_miles":6,"gust_mph":12.9,"gust_kph":20.7,"uv":0},{"time_epoch":1757073600,"time":"2025-09-05 06:00","temp_c":21.6,"temp_f":70.9,"is_day":0,"condition":{"text":"Cloudy ","icon":"\/\/cdn.weatherapi.com\/weather\/64x64\/night\/119.png","code":1006},"wind_mph":11,"wind_kph":17.6,"wind_degree":1,"wind_dir":"N","pressure_mb":1014,"pressure_in":29.93,"precip_mm":0,"precip_in":0,"snow_cm":0,"humidity":60,"cloud":73,"feelslike_c":21.6,"feelslike_f":70.9,"windchill_c":21.6,"windchill_f":70.9,"heatindex_c":23.6,"heatindex_f":74.5,"dewpoint_c":13.5,"dewpoint_f":56.3,"will_it_rain":0,"chance_of_rain":0,"will_it_snow":0,"chance_of_snow":0,"vis_km":10,"vis_miles":6,"gust_mph":13,"gust_kph":21,"uv":0},{"time_epoch":1757077200,"time":"2025-09-05 07:00","temp_c":21.3,"temp_f":70.3,"is_day":1,"condition":{"text":"Cloudy ","icon":"\/\/cdn.weatherapi.com\/weather\/64x64\/day\/119.png","code":1006},"wind_mph":9.8,"wind_kph":15.8,"wind_degree":356,"wind_dir":"N","pressure_mb":1014,"pressure_in":29.95,"precip_mm":0,"precip_in":0,"snow_cm":0,"humidity":62,"cloud":87,"feelslike_c":21.3,"feelslike_f":70.3,"windchill_c":21.3,"windchill_f":70.3,"heatindex_c":22.6,"heatindex_f":72.7,"dewpoint_c":13.6,"dewpoint_f":56.5,"will_it_rain":0,"chance_of_rain":0,"will_it_snow":0,"chance_of_snow":0,"vis_km":5,"vis_miles":3,"gust_mph":11.7,"gust_kph":18.8,"uv":0},{"time_epoch":1757080800,"time":"2025-09-05 08:00","temp_c":21,"temp_f":69.8,"is_day":1,"condition":{"text":"Cloudy ","icon":"\/\/cdn.weatherapi.com\/weather\/64x64\/day\/119.png","code":1006},"wind_mph":10.5,"wind_kph":16.9,"wind_degree":10,"wind_dir":"N","pressure_mb":1015,"pressure_in":29.97,"precip_mm":0,"precip_in":0,"snow_cm":0,"humidity":63,"cloud":93,"feelslike_c":21,"feelslike_f":69.8,"windchill_c":21,"windchill_f":69.8,"heatindex_c":21.7,"heatindex_f":71,"dewpoint_c":13.6,"dewpoint_f":56.5,"will_it_rain":0,"chance_of_rain":0,"will_it_snow":0,"chance_of_snow":0,"vis_km":10,"vis_miles":6,"gust_mph":12.5,"gust_kph":20.2,"uv":0.1},{"time_epoch":1757084400,"time":"2025-09-05 09:00","temp_c":21.6,"temp_f":70.8,"is_day":1,"condition":{"text":"Overcast ","icon":"\/\/cdn.weatherapi.com\/weather\/64x64\/day\/122.png","code":1009},"wind_mph":9.2,"wind_kph":14.8,"wind_degree":25,"wind_dir":"NNE","pressure_mb":1015,"pressure_in":29.98,"precip_mm":0,"precip_in":0,"snow_cm":0,"humidity":64,"cloud":100,"feelslike_c":21.6,"feelslike_f":70.8,"windchill_c":21.6,"windchill_f":70.8,"heatindex_c":22.3,"heatindex_f":72.1,"dewpoint_c":13.7,"dewpoint_f":56.6,"will_it_rain":0,"chance_of_rain":0,"will_it_snow":0,"chance_of_snow":0,"vis_km":10,"vis_miles":6,"gust_mph":10.8,"gust_kph":17.4,"uv":0.3},{"time_epoch":1757088000,"time":"2025-09-05 10:00","temp_c":22.2,"temp_f":72,"is_day":1,"condition":{"text":"Overcast ","icon":"\/\/cdn.weatherapi.com\/weather\/64x64\/day\/122.png","code":1009},"wind_mph":9.2,"wind_kph":14.8,"wind_degree":38,"wind_dir":"NE","pressure_mb":1015,"pressure_in":29.98,"precip_mm":0,"precip_in":0,"snow_cm":0,"humidity":58,"cloud":98,"feelslike_c":23.1,"feelslike_f":73.6,"windchill_c":22.2,"windchill_f":72,"heatindex_c":23.1,"heatindex_f":73.6,"dewpoint_c":13.4,"dewpoint_f":56.2,"will_it_rain":0,"chance_of_rain":0,"will_it_snow":0,"chance_of_snow":0,"vis_km":10,"vis_miles":6,"gust_mph":11.5,"gust_kph":18.6,"uv":0.6},{"time_epoch":1757091600,"time":"2025-09-05 11:00","temp_c":22.9,"temp_f":73.2,"is_day":1,"condition":{"text":"Overcast ","icon":"\/\/cdn.weatherapi.com\/weather\/64x64\/day\/122.png","code":1009},"wind_mph":9.8,"wind_kph":15.8,"wind_degree":63,"wind_dir":"ENE","pressure_mb":1015,"pressure_in":29.98,"precip_mm":0,"precip_in":0,"snow_cm":0,"humidity":55,"cloud":97,"feelslike_c":24.1,"feelslike_f":75.3,"windchill_c":22.9,"windchill_f":73.2,"heatindex_c":24.1,"heatindex_f":75.3,"dewpoint_c":13.3,"dewpoint_f":56,"will_it_rain":0,"chance_of_rain":0,"will_it_snow":0,"chance_of_snow":0,"vis_km":10,"vis_miles":6,"gust_mph":12.4,"gust_kph":19.9,"uv":0.9},{"time_epoch":1757095200,"time":"2025-09-05 12:00","temp_c":24,"temp_f":75.1,"is_day":1,"condition":{"text":"Overcast ","icon":"\/\/cdn.weatherapi.com\/weather\/64x64\/day\/122.png","code":1009},"wind_mph":9.6,"wind_kph":15.5,"wind_degree":86,"wind_dir":"E","pressure_mb":1015,"pressure_in":29.98,"precip_mm":0,"precip_in":0,"snow_cm":0,"humidity":52,"cloud":96,"feelslike_c":25,"feelslike_f":77,"windchill_c":24,"windchill_f":75.1,"heatindex_c":25,"heatindex_f":77,"dewpoint_c":13.2,"dewpoint_f":55.7,"will_it_rain":0,"chance_of_rain":0,"will_it_snow":0,"chance_of_snow":0,"vis_km":10,"vis_miles":6,"gust_mph":11.8,"gust_kph":19,"uv":0.7},{"time_epoch":1757098800,"time":"2025-09-05 13:00","temp_c":24.8,"temp_f":76.7,"is_day":1,"condition":{"text":"Overcast ","icon":"\/\/cdn.weatherapi.com\/weather\/64x64\/day\/122.png","code":1009},"wind_mph":12.5,"wind_kph":20.2,"wind_degree":115,"wind_dir":"ESE","pressure_mb":1014,"pressure_in":29.94,"precip_mm":0,"precip_in":0,"snow_cm":0,"humidity":49,"cloud":61,"feelslike_c":25.6,"feelslike_f":78.2,"windchill_c":24.8,"windchill_f":76.7,"heatindex_c":25.6,"heatindex_f":78.2,"dewpoint_c":13.5,"dewpoint_f":56.2,"will_it_rain":0,"chance_of_rain":0,"will_it_snow":0,"chance_of_snow":0,"vis_km":10,"vis_miles":6,"gust_mph":16.3,"gust_kph":26.2,"uv":1.7},{"time_epoch":1757102400,"time":"2025-09-05 14:00","temp_c":25.6,"temp_f":78.1,"is_day":1,"condition":{"text":"Overcast ","icon":"\/\/cdn.weatherapi.com\/weather\/64x64\/day\/122.png","code":1009},"wind_mph":14.1,"wind_kph":22.7,"wind_degree":134,"wind_dir":"SE","pressure_mb":1013,"pressure_in":29.92,"precip_mm":0,"precip_in":0,"snow_cm":0,"humidity":47,"cloud":44,"feelslike_c":26.2,"feelslike_f":79.1,"windchill_c":25.6,"windchill_f":78.1,"heatindex_c":26.2,"heatindex_f":79.1,"dewpoint_c":13.6,"dewpoint_f":56.4,"will_it_rain":0,"chance_of_rain":0,"will_it_snow":0,"chance_of_snow":0,"vis_km":10,"vis_miles":6,"gust_mph":17,"gust_kph":27.3,"uv":1.3},{"time_epoch":1757106000,"time":"2025-09-05 15:00","temp_c":25.6,"temp_f":78.1,"is_day":1,"condition":{"text":"Partly Cloudy ","icon":"\/\/cdn.weatherapi.com\/weather\/64x64\/day\/116.png","code":1003},"wind_mph":13.2,"wind_kph":21.2,"wind_degree":127,"wind_dir":"SE","pressure_mb":1013,"pressure_in":29.9,"precip_mm":0,"precip_in":0,"snow_cm":0,"humidity":46,"cloud":26,"feelslike_c":26.2,"feelslike_f":79.2,"windchill_c":25.6,"windchill_f":78.1,"heatindex_c":26.2,"heatindex_f":79.2,"dewpoint_c":13.7,"dewpoint_f":56.7,"will_it_rain":0,"chance_of_rain":0,"will_it_snow":0,"chance_of_snow":0,"vis_km":5,"vis_miles":3,"gust_mph":16.5,"gust_kph":26.6,"uv":0.7},{"time_epoch":1757109600,"time":"2025-09-05 16:00","temp_c":25.4,"temp_f":77.7,"is_day":1,"condition":{"text":"Partly Cloudy ","icon":"\/\/cdn.weatherapi.com\/weather\/64x64\/day\/116.png","code":1003},"wind_mph":12.1,"wind_kph":19.4,"wind_degree":121,"wind_dir":"ESE","pressure_mb":1012,"pressure_in":29.89,"precip_mm":0,"precip_in":0,"snow_cm":0,"humidity":49,"cloud":62,"feelslike_c":26.1,"feelslike_f":79,"windchill_c":25.4,"windchill_f":77.7,"heatindex_c":26.1,"heatindex_f":79,"dewpoint_c":14.2,"dewpoint_f":57.5,"will_it_rain":0,"chance_of_rain":0,"will_it_snow":0,"chance_of_snow":0,"vis_km":10,"vis_miles":6,"gust_mph":15.4,"gust_kph":24.8,"uv":0.7},{"time_epoch":1757113200,"time":"2025-09-05 17:00","temp_c":25.1,"temp_f":77.2,"is_day":1,"condition":{"text":"Partly Cloudy ","icon":"\/\/cdn.weatherapi.com\/weather\/64x64\/day\/116.png","code":1003},"wind_mph":10.7,"wind_kph":17.3,"wind_degree":116,"wind_dir":"ESE","pressure_mb":1012,"pressure_in":29.89,"precip_mm":0,"precip_in":0,"snow_cm":0,"humidity":51,"cloud":81,"feelslike_c":26,"feelslike_f":78.7,"windchill_c":25.1,"windchill_f":77.2,"heatindex_c":26,"heatindex_f":78.7,"dewpoint_c":14.4,"dewpoint_f":57.9,"will_it_rain":0,"chance_of_rain":0,"will_it_snow":0,"chance_of_snow":0,"vis_km":10,"vis_miles":6,"gust_mph":13.6,"gust_kph":21.9,"uv":0.6},{"time_epoch":1757116800,"time":"2025-09-05 18:00","temp_c":24.1,"temp_f":75.4,"is_day":1,"condition":{"text":"Overcast ","icon":"\/\/cdn.weatherapi.com\/weather\/64x64\/day\/122.png","code":1009},"wind_mph":10.3,"wind_kph":16.6,"wind_degree":112,"wind_dir":"ESE","pressure_mb":1012,"pressure_in":29.88,"precip_mm":0,"precip_in":0,"snow_cm":0,"humidity":53,"cloud":99,"feelslike_c":25.6,"feelslike_f":78,"windchill_c":24.1,"windchill_f":75.4,"heatindex_c":25.6,"heatindex_f":78,"dewpoint_c":14.6,"dewpoint_f":58.3,"will_it_rain":0,"chance_of_rain":0,"will_it_snow":0,"chance_of_snow":0,"vis_km":10,"vis_miles":6,"gust_mph":13,"gust_kph":20.9,"uv":0.2},{"time_epoch":1757120400,"time":"2025-09-05 19:00","temp_c":23.2,"temp_f":73.7,"is_day":1,"condition":{"text":"Overcast ","icon":"\/\/cdn.weatherapi.com\/weather\/64x64\/day\/122.png","code":1009},"wind_mph":11.9,"wind_kph":19.1,"wind_degree":124,"wind_dir":"SE","pressure_mb":1013,"pressure_in":29.92,"precip_mm":0,"precip_in":0,"snow_cm":0,"humidity":60,"cloud":73,"feelslike_c":25.2,"feelslike_f":77.3,"windchill_c":23.2,"windchill_f":73.7,"heatindex_c":25.2,"heatindex_f":77.3,"dewpoint_c":14.8,"dewpoint_f":58.6,"will_it_rain":0,"chance_of_rain":0,"will_it_snow":0,"chance_of_snow":0,"vis_km":10,"vis_miles":6,"gust_mph":15.5,"gust_kph":25,"uv":0},{"time_epoch":1757124000,"time":"2025-09-05 20:00","temp_c":22.3,"temp_f":72.1,"is_day":0,"condition":{"text":"Overcast ","icon":"\/\/cdn.weatherapi.com\/weather\/64x64\/night\/122.png","code":1009},"wind_mph":12.3,"wind_kph":19.8,"wind_degree":125,"wind_dir":"SE","pressure_mb":1014,"pressure_in":29.94,"precip_mm":0,"precip_in":0,"snow_cm":0,"humidity":63,"cloud":61,"feelslike_c":24.8,"feelslike_f":76.7,"windchill_c":22.3,"windchill_f":72.1,"heatindex_c":24.8,"heatindex_f":76.7,"dewpoint_c":14.8,"dewpoint_f":58.7,"will_it_rain":0,"chance_of_rain":0,"will_it_snow":0,"chance_of_snow":0,"vis_km":10,"vis_miles":6,"gust_mph":16.2,"gust_kph":26,"uv":0},{"time_epoch":1757127600,"time":"2025-09-05 21:00","temp_c":21.4,"temp_f":70.5,"is_day":0,"condition":{"text":"Partly Cloudy ","icon":"\/\/cdn.weatherapi.com\/weather\/64x64\/night\/116.png","code":1003},"wind_mph":12.1,"wind_kph":19.4,"wind_degree":117,"wind_dir":"ESE","pressure_mb":1014,"pressure_in":29.95,"precip_mm":0,"precip_in":0,"snow_cm":0,"humidity":66,"cloud":48,"feelslike_c":21.4,"feelslike_f":70.5,"windchill_c":21.4,"windchill_f":70.5,"heatindex_c":23.4,"heatindex_f":74.1,"dewpoint_c":14.9,"dewpoint_f":58.8,"will_it_rain":0,"chance_of_rain":0,"will_it_snow":0,"chance_of_snow":0,"vis_km":10,"vis_miles":6,"gust_mph":16.2,"gust_kph":26,"uv":0},{"time_epoch":1757131200,"time":"2025-09-05 22:00","temp_c":20.7,"temp_f":69.2,"is_day":0,"condition":{"text":"Partly Cloudy ","icon":"\/\/cdn.weatherapi.com\/weather\/64x64\/night\/116.png","code":1003},"wind_mph":9.2,"wind_kph":14.8,"wind_degree":116,"wind_dir":"ESE","pressure_mb":1015,"pressure_in":29.97,"precip_mm":0,"precip_in":0,"snow_cm":0,"humidity":71,"cloud":38,"feelslike_c":20.7,"feelslike_f":69.2,"windchill_c":20.7,"windchill_f":69.2,"heatindex_c":22.1,"heatindex_f":71.7,"dewpoint_c":15,"dewpoint_f":58.9,"will_it_rain":0,"chance_of_rain":0,"will_it_snow":0,"chance_of_snow":0,"vis_km":10,"vis_miles":6,"gust_mph":12.5,"gust_kph":20.2,"uv":0},{"time_epoch":1757134800,"time":"2025-09-05 23:00","temp_c":20.1,"temp_f":68.2,"is_day":0,"condition":{"text":"Partly Cloudy ","icon":"\/\/cdn.weatherapi.com\/weather\/64x64\/night\/116.png","code":1003},"wind_mph":10.1,"wind_kph":16.2,"wind_degree":118,"wind_dir":"ESE","pressure_mb":1015,"pressure_in":29.98,"precip_mm":0,"precip_in":0,"snow_cm":0,"humidity":73,"cloud":33,"feelslike_c":20.1,"feelslike_f":68.2,"windchill_c":20.1,"windchill_f":68.2,"heatindex_c":20.8,"heatindex_f":69.4,"dewpoint_c":15,"dewpoint_f":59,"will_it_rain":0,"chance_of_rain":0,"will_it_snow":0,"chance_of_snow":0,"vis_km":10,"vis_miles":6,"gust_mph":12.5,"gust_kph":20.2,"uv":0}]}]},"alerts":{"alert":[]}}', true );
            //$weatherData = $this->fetchWeatherData();
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

            $this->shape_size = $this->drawSun( $image,$scale_multiplyer,$x,$y );

        } else {

            $this->shape_size = $this->drawMoon( $image,$scale_multiplyer,$x,$y );

        }

        // Clouds
        if( $cloudy_pct >= 50 or str_contains( $conditionText, 'overcast' ) ) {

            $this->drawDarkClouds( $image,$x,$y-5 );

        }
        $this->drawLightClouds( $image,$x,$y-5 );

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
        $this->shape_size = $this->drawSun( $image,$scale_multiplyer,$x + 25,$y + 15 );

        // Clouds
        if( $cloudy_pct >= 50 or str_contains($conditionText, 'overcast' ) ) {

            $this->drawDarkClouds( $image,$x + 25,$y + 12 );

        }
        $this->drawLightClouds( $image,$x + 25,$y + 10 );

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

        $shape_size = ( 32 ) * $scale_multiplyer;

        imagefilledellipse( $image,$x,$y,$shape_size,$shape_size,$this->yellow );

        return $shape_size;
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

        $shape_size = ( $radius ) * $scale_multiplyer;

        // Draw a filled ellipse to represent the moon. Since the height and width are the same, it will be a perfect circle.
        imagefilledellipse( $image,$x,$y,$shape_size,$shape_size,$this->pale );

        return $shape_size;

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

    private function drawLightClouds( $image,$x,$y ) {

        imagefilledellipse( $image,$x-20,$y + 10,15,10,$this->light_grey );
        imagefilledellipse( $image,$x,$y + 10,38,22,$this->grey );
        imagefilledellipse( $image,$x+20,$y + 14,30,10,$this->light_grey );

    }

    private function drawDarkClouds( $image,$x,$y ) {

        imagefilledellipse( $image,$x,$y + 18,42,30,$this->dark_grey );

    }

}