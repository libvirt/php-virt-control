#!/bin/bash

if php -m | grep qrencode > /dev/null; then
	# Library already exists. No action required.
	exit 0
fi

if [ `id -u` -ne 0 ]; then
	echo "Error: You have to run this script as root!"
	exit 1
fi

num=$(locate libqrencode | wc -l)
if [ $num -eq 0 ]; then
	echo "libqrencode not found. Downloading ..."
	wget -O test.tgz http://fukuchi.org/works/qrencode/qrencode-3.3.1.tar.gz
	tar -xzf test.tgz
	rm -f test.tgz
	cd qrencode-3.3.1
	./configure --prefix=/usr
	make
	make install
	cd ..
	rm -rf qrencode-3.3.1
fi

git clone https://github.com/cviebrock/php-qrencode.git
cd php-qrencode
phpize
./configure
make
make install
cd ..
rm -rf php-qrencode
echo extension=qrencode.so > /etc/php.d/qrencode.ini
php -m | grep qr > /dev/null
if [ "$?" -ne 0 ]; then
	rm -f /etc/php.d/qrencode.ini
	echo "ERROR: Cannot find phpqrcode extension in PHP modules. Reverting..."
	exit 1
fi

echo "Restarting apache..."
service httpd restart
echo "All done"
exit 0
