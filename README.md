# refonlinesms
Small mobile optimized portal to request a SMS voucher to access WLAN with pfSense Captive Portal

## Requirements
* PHP 5.x
* MySQL
* SMS-Gateway (e.g. from Hetzner)

## Installation
1. Copy evenything into your webroot
2. Place the sample .ini file outside your webroot and rename
3. Enter your MySQL and SMS-Gateway data into the .ini file
4. Change path to .ini file in php/Db.php (line 4)
5. Run
