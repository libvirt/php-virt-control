Name:		php-virt-control
Version:	0.1.1
Release:	1%{?dist}%{?extra_release}
Summary:	PHP-based virtual machine control tool
Group:		Applications/Internet
License:	GPLv3
URL:		http://www.php-virt-control.org
Source0:	http://www.php-virt-control.org/download/php-virt-control-%{version}.tar.gz
BuildRoot:	%{_tmppath}/%{name}-%{version}-%{release}-root
BuildRequires:	gcc
Requires:	php-libvirt >= 0.4.4
Requires:	httpd
Requires:	php
Requires:	php-gd
Requires:	php-mysql

%description
php-virt-control is a virtual machine control tool written in PHP language
to allow virtual machine management using libvirt-php extension.
For more details see: http://www.php-virt-control.org

%prep
%setup -q -n php-virt-control-%{version}

%{__cat} <<EOF >php-virt-control.conf
#
#  %{summary}
#

<Directory "%{_datadir}/php-virt-control">
  Allow from all
</Directory>

<Directory "%{_datadir}/php-virt-control/setup">
  Order Deny,Allow
  Deny from all
  Allow from 127.0.0.1
  Allow from ::1
</Directory>

<Directory "%{_datadir}/php-virt-control/data">
  Deny from all
</Directory>

<Directory "%{_datadir}/php-virt-control/logs">
  Deny from all
</Directory>

Alias /php-virt-control %{_datadir}/%{name}
EOF

%install
rm -rf %{buildroot}
mkdir -p %{buildroot}/%{_datadir}/%{name}
mkdir -p %{buildroot}/%{_datadir}/%{name}/logs
mkdir -p %{buildroot}/%{_sysconfdir}/httpd/conf.d/
mkdir -p %{buildroot}/%{_sysconfdir}/%{name}

mkdir -p %{buildroot}/%{_bindir}
gcc -o %{buildroot}/%{_bindir}/apache-key-copy tools/apache-key-copy.c

install -d -m0755 %{buildroot}%{_datadir}/%{name}/
cp -af *.php %{buildroot}%{_datadir}/%{name}/
cp -af *.css %{buildroot}%{_datadir}/%{name}/
cp -af classes/ data/ graphics/ lang/ models/ pages/ setup/ %{buildroot}%{_datadir}/%{name}/
cp -af logs/README %{buildroot}%{_datadir}/%{name}/logs
cp -af data/config_db.php %{buildroot}/%{_sysconfdir}/%{name}/config_db.php
install -Dp -m0644 auth/50-org.libvirt-remote-access.pkla %{buildroot}/etc/polkit-1/localauthority/50-local.d/50-org.libvirt-remote-access.pkla
install -Dp -m0644 php-virt-control.conf %{buildroot}%{_sysconfdir}/httpd/conf.d/php-virt-control.conf
chmod 777 %{buildroot}%{_datadir}/%{name}/logs

%clean
rm -rf %{buildroot}

%files
%doc AUTHORS COPYING README INSTALL
%defattr(-,root,root,-)
%config(noreplace) %{_sysconfdir}/%{name}/config_db.php
%config(noreplace) %{_sysconfdir}/httpd/conf.d/php-virt-control.conf
%config(noreplace) /etc/polkit-1/localauthority/50-local.d/50-org.libvirt-remote-access.pkla
%{_bindir}/apache-key-copy
%{_datadir}/%{name}/

%changelog
* Fri Nov 08 2013 Michal Novotny <minovotn@redhat.com> - 0.1.1
- Project rewritten completely as version 0.1
