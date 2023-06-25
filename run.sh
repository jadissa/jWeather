#!/bin/bash
## Notes
## - Requires PHP
## - See weather.php for more info
if [[ ! -x "$(command -v php)" ]]; then
	exit
else
	php weather.php
fi;