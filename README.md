# jWeather
## Simple weather script built with weatherapi.com

## License
[![License](https://img.shields.io/badge/license-GPL-blue)](LICENSE)

## Why
Forecast.io died and I needed a weather monitor replacement

## How
Note:
- Requires GeekTool, which can be downloaded from https://www.tynsoe.org/geektool/
- Requires a weather API key from https://weatherapi.com

- Register App for use at weatherapi.com
- Generate your API key
- Paste your API key into the weather.php script $OPTIONS, near the top

- Open GeekTool

#### Enable Weather Generation Method 1
- Drag new 'Shell' Geeklet to your desktop
- Paste the following line into the Shell Command:
```/path/to/php /path/to/jWeather/weather.php```

Note:
The following command can show you the path to your php install:
```which php```

- Set the command to run every 3,600 seconds
- Set Timeout to something such as 20 seconds
- Check Display status feedback image
- In your terminal, run ```/path/to/php /path/to/jWeather/weather.php``` to generate the weather display, if necessary or to test

#### Enable Weather Generation Method 2 ( optional )
Note:
- This only applies if you chose to not do method 1

- Create a cron entry in your system for the weather script to run:
- In your terminal, run ```crontab -e```
- Paste the following into your crontab:
- ```0 * * * * /path/to/php /path/to/jWeather/weather.php```
- Ensure your terminal has full disk access if issues are encountered

Note:
Full disk access for your terminal should not be necessary, as it has not been during my testing

#### Enable Weather Display
- Drag new 'Image' Geeklet to your desktop
- Set local path to: ```/path/to/jWeather/out.png```

- Resize the 'Image' window to desired size by dragging the bottom right corner
- Set to run every every 2 seconds or no more than 3600 seconds
- At this point, you should be set!

## Platforms
Any Mac system that has php installed

## FAQ
- You can change size of font in weather.php, along with other settings
- You may need to run ```/path/to/php /path/to/jWeather/weather.php``` to see those changes immediately

## Issues
https://github.com/jadissa/jWeather/issues

## Screenshots
<p float="left">
  <img src="screenshots/1.png" width="400" />
  <img src="screenshots/2.png" width="400" />
  <img src="screenshots/3.png" width="400" />
  <img src="screenshots/4.png" width="400" />
</p>