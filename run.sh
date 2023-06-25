#!/bin/bash
## Notes
## - Requires PHP
## - See weather.php for more info
##
## Website: https://github.com/jadissa/jWeather
if [[ ! -x "$(command -v php)" ]]; then
	exit
else
	php weather.php
fi;