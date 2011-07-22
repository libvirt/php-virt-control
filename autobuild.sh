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

if [ -n "$AUTOBUILD_COUNTER" ]; then
  EXTRA_RELEASE=".auto$AUTOBUILD_COUNTER"
else
  NOW=`date +"%s"`
  EXTRA_RELEASE=".$USER$NOW"
fi

if [ -x /usr/bin/rpmbuild ]
then
  rpmbuild --nodeps \
     --define "extra_release $EXTRA_RELEASE" \
     --define "_sourcedir `pwd`" \
     -ba --clean php-virt-control.spec
fi
