# weather-proxy
Proxy Luftdaten from your Feinstaubsensor to OpenWeatherMap

The main part of data.php is taken from the original repo https://github.com/opendata-stuttgart/madavi-api, but before handling the data the connection is closed to free resources on your Airrohr.


## Steps
* Register at OpenWeatherMap and receive an api key.
* Find out geo data of your Feinstaubsensor.
* Save these params to config.inc.php.
* Call `php command.php install` to get your station id and store it as well.
* Make data.php accessible from your Feinstaubsensor and configure the sensor to send data to it.