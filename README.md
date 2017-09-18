# simpleWebsiteAlert
A simple website watch and alert script

See comments in the php script for parameters

Just let the script run as a cronjob and watch your favourite websites for changes.

Example: php website_alert.php to@example.com from@example.com Example https://www.example.com push positives '//div[contains(@class, "example")]'

**Dependencies**

- wget
- diff

_optional dependencies_

- xmllint
- token from https://pushme.jagcesar.se/ >> must be stored inside pushToken.txt