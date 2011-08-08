Name:		php-virt-control
Version:	0.0.2
Release:	1%{?dist}%{?extra_release}
Summary:	PHP-based virtual machine control tool
Group:		Applications/Internet
License:	GPLv3
URL:		http://www.php-virt-control.org
Source0:	http://www.php-virt-control.org/download/php-virt-control-%{version}.tar.gz
BuildRoot:	%{_tmppath}/%{name}-%{version}-%{release}-root
BuildArch:	noarch
Requires:	php-libvirt >= 0.4.3
Requires:	webserver

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
  Order Deny,Allow
  Deny from all
  Allow from 127.0.0.1
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
mkdir -p %{buildroot}/%{_sysconfdir}/httpd/conf.d/
mkdir -p %{buildroot}/%{_sysconfdir}/%{name}

install -d -m0755 %{buildroot}%{_datadir}/%{name}/
cp -af *.php %{buildroot}%{_datadir}/%{name}/
cp -af *.css %{buildroot}%{_datadir}/%{name}/
cp -af classes/ data/ graphics/ lang/ logs/ pages/ %{buildroot}%{_datadir}/%{name}/
install -Dp -m0644 php-virt-control.conf %{buildroot}%{_sysconfdir}/httpd/conf.d/php-virt-control.conf

%clean
rm -rf %{buildroot}

%files
%doc AUTHORS COPYING README INSTALL
%defattr(-,root,root,-)
%config(noreplace) %{_sysconfdir}/httpd/conf.d/php-virt-control.conf
%{_datadir}/%{name}/

%changelog
* Fri Jul 22 2011 Michal Novotny <minovotn@redhat.com> - 0.0.2
- Several bugfixes plus network management

* Sun Jul 10 2011 Michal Novotny <minovotn@redhat.com> - 0.0.1
- Initial version
