#!/bin/sh

set -e
set -v

make distclean || :

aclocal
autoreconf -i -f

./configure --prefix=$AUTOBUILD_INSTALL_ROOT

#make
#make install

rm -f *.tar.gz
make dist

if [ -x /usr/bin/rpmbuild ]
then
  rpmbuild --nodeps \
     --define "_sourcedir `pwd`" \
     -ba --clean php-virt-control.spec
fi
