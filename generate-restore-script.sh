mkdir tmp
cp -rf vendor tmp/
cp restore/xcloner_restore.php.txt tmp/xcloner_restore.php
/Applications/MAMP/bin/php/php7.0.12/bin/php phar-generate.php
tar -czpf restore/xcloner-restore.tgz tmp/
rm -rf tmp/*
