# Simple PHP GIOS API client

**1. Setup and display options**

First, create a "data/" directory. You can rename the data directory in the file gios.php by setting the variable $cfg["storageDir"].
Then use a command: 
```
php gios.php
```
returns
```
# find station ID
php gios.php find "City name"

# get station sensors
php gios.php sensors station_id

# get data from selected sensor
php gios.php data sensor_id

# get all datas from selected station
php gios.php getall station_id

```
**2. Find city (eg. Jelenia Góra):**
```
php gios.php find Jelenia Góra
```
returns
```
List of stations in Jelenia Góra:

Station ID:    9153
Station name:  Jelenia Góra, ul. Ogińskiego
Address:       ul. Ogińskiego 6 (DOLNOŚLĄSKIE)
```

**3. Read a list of sensors from station**
```
php gios.php sensors 9153
```
returns
```
List of sensors in 9153 station:

Sensor ID:    14727
Sensor name:  dwutlenek azotu
Sensor code:  NO2

Sensor ID:    14729
Sensor name:  ozon
Sensor code:  O3

Sensor ID:    14730
Sensor name:  pył zawieszony PM10
Sensor code:  PM10

Sensor ID:    14731
Sensor name:  pył zawieszony PM2.5
Sensor code:  PM2.5

Sensor ID:    14733
Sensor name:  dwutlenek siarki
Sensor code:  SO2

Sensor ID:    14734
Sensor name:  benzen
Sensor code:  C6H6

Sensor ID:    14707
Sensor name:  tlenek węgla
Sensor code:  CO
```

**4. Get sensor data (by ID)**
```
php gios.php data 14730
```
return
```
OK: PM10 data from sensor ID 14730 has been stored correctly.
```

You can also download data from all sensors using the command:
```
php gios.php getall 9153
```
Where 9153 is Station ID.
