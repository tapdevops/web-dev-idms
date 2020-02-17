# -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-==-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-
# TAP Laravel Docker
# Created At: 2019-12-19
#
# Untuk membungkus PHP Laravel Project. Berikut ini extension-extension yang akan di
# pasang:
# -> PHP Ext: BC Math
#    For arbitrary precision mathematics PHP offers the Binary Calculator which supports 
#    numbers of any size and precision up to 2147483647 (or 0x7FFFFFFF) decimals, if 
#    there is sufficient memory, represented as strings. 
# -> PHP Ext: CLI
#    PHP CLI is a short for PHP Command Line Interface. As the name implies, this is a 
#    way of using PHP in the system command line. Or by other words it is a way of 
#    running PHP Scripts that aren't on a web server (such as Apache web server or 
#    Microsoft IIS). People usually treat PHP as web development, server side tool.
# -> PHP Ext: Common
#    Includes common files for PHP packages, this package contains common utilities 
#    shared among all packaged PHP versions. The php-common package contains files 
#    used by both the php package and the php-cli package.
#    This extension included: php-api, php-bz2, php-calendar, php-ctype, php-curl, 
#    php-date, php-exif, php-fileinfo, php-filter, php-ftp, php-gettext, php-gmp, 
#    php-hash, php-iconv, php-json, php-libxml, php-openssl, php-pcre, php-pecl-Fileinfo, 
#    php-pecl-phar, php-pecl-zip, php-reflection, php-session, php-shmop, php-simplexml, 
#    php-sockets, php-spl, php-tokenizer, php-zend-abi, php-zip, php-zlib
# -> PHP Ext: GD
#    PHP is not limited to creating just HTML output. It can also be used to create 
#    and manipulate image files in a variety of different image formats, including GIF, 
#    PNG, JPEG, WBMP, and XPM. Even more conveniently, PHP can output image streams 
#    directly to a browser. You will need to compile PHP with the GD library of image 
#    functions for this to work. GD and PHP may also require other libraries, depending 
#    on which image formats you want to work with. 
# Cara Penggunaan:
# -> Untuk membuat Docker Images, jalankan command berikut:
#    $ docker build -t AUTHOR/NAMA_PROJECT_YYYYMMDD_HHii .
#    $ docker build -t FERDINAND/SAMPLEPROJECT_20191212_1402 .
# -> Setelah Docker Images terbentuk (tanpa error), lalu buat sebuah Docker Container:
#    $ docker run -p 4016:80 -d kucing/001
# -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-==-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-


# 1. Set Server
#    Server menentukan bagaiman Shellscript berjalan. Untuk Oracle Linux, command yang 
#    digunakan sama seperti CentosOS7.
# -------------------------------------------------------------------------------------
FROM oraclelinux:7-slim
LABEL Vendor="Oracle CentOS7"

# 2. Instalasi Plugin/Program yang dibutuhkan.
#    Berikut ini beberapa perintah yang dijalankan, diantaranya:
#    -> Update Sistem
#    -> Install Oracle Linux Yum Server
#    -> Install Oracle Instant Client x64
#    -> Install Apache HTTP Server
#    -> Install PHP Extensions ( https://davescripts.com/docker-container-with-centos-7-apache-php-72 )
#    -> Install Composer
#    -> Install Nano Editor
#    -> Hapus cache Repository Yum
RUN yum update -y
RUN yum install -y make
RUN yum install -y oracle-release-el7
RUN yum install -y oracle-instantclient19.5-basic.x86_64
RUN yum install -y oracle-instantclient19.5-devel.x86_64
RUN yum install -y httpd-tools
RUN yum install -y gcc
RUN yum -y --setopt=tsflags=nodocs install httpd
RUN rpm -Uvh https://dl.fedoraproject.org/pub/epel/epel-release-latest-7.noarch.rpm
RUN rpm -Uvh https://mirror.webtatic.com/yum/el7/webtatic-release.rpm
RUN yum -y install \
	php72w \
	php72w-bcmath \
	php72w-cli \
	php72w-common \
	php72w-gd \
	php72w-intl \
	php72w-ldap \
	php72w-mbstring \
	php72w-mysql \
	php72w-pgsql \
	php72w-pdo \
	php72w-pear \
	php72w-soap \
	php72w-xml \
	php72w-xmlrpc \
	php-devel
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN yum -y install nano
RUN yum clean all
RUN echo $PATH

# 4. Setup Oracle Environment
#    Untuk setting Oracle pada Centos7 yang akan berjalan di Docker Container.
#    -> ORACLE_HOME 
# -------------------------------------------------------------------------------------
ENV ORACLE_HOME=/usr/lib/oracle/19.5/client64
ENV PATH=$ORACLE_HOME/bin:$ORACLE_HOME/OPatch/:/usr/sbin:$PATH
ENV LD_LIBRARY_PATH=$ORACLE_HOME/lib:/usr/lib
RUN echo 'instantclient,$ORACLE_HOME/lib' | pecl install oci8

RUN rm -rf /etc/httpd/conf.d/welcome.conf
COPY ./docker-utility/vhost.conf /etc/httpd/conf.d/000-default.conf

# 5. Update Apache Environment
# -------------------------------------------------------------------------------------
RUN sed -E -i -e '/<Directory "\/var\/www\/html">/,/<\/Directory>/s/AllowOverride None/AllowOverride All/' /etc/httpd/conf/httpd.conf
RUN sed -E -i -e 's/DirectoryIndex (.*)$/DirectoryIndex index.php \1/g' /etc/httpd/conf/httpd.conf

# 6. PHP Initialize
#    Untuk mengatur file php.ini, file yang di custom di file php.ini saat ini adalah
#    sebagai berikut:
#    -> max_execution_time = 600 (line 362, satuan detik)
#    -> max_input_time = 600 (line 372, satuan detik)
#    -> memory_limit = 512M (line 383, satuan megabytes)
#
#    Jika ingin merubah config php.ini, bisa di edit di folder "docker-utility.php.ini".
# -------------------------------------------------------------------------------------
COPY ./docker-utility/php.ini /etc/

# 7. Copy Projects to Document Root
#    Untuk menjalankan beberapa perintah seperti change mode, dan pindahkan file-file 
#    project.
# -------------------------------------------------------------------------------------
COPY . /var/www/html/Web-IDMS
#RUN chmod -R 777 /var/www/html/Web-IDMS/storage /var/www/html/Web-IDMS/bootstrap /var/www/html/Web-IDMS/resources /var/www/html/Web-IDMS/vendor /var/www/html/Web-IDMS/public

# 8. Laravel Configuration
#    Untuk menjalankan perintah-perintah di Laravel. Script yang ingin dijalankan, di
#    sesuaikan dengan kebutuhan Project. (https://laravel.com/docs/6.x/artisan)
# -------------------------------------------------------------------------------------
RUN ( cd /var/www/html/Web-IDMS; composer install )
RUN ( cd /var/www/html/Web-IDMS; composer dump-autoload )
RUN ( cd /var/www/html/Web-IDMS; php artisan optimize )
RUN chmod -R 777 /var/www/html/Web-IDMS
RUN chown -R apache:apache /var/www/html/Web-IDMS
COPY ./docker-utility/.env /var/www/html/Web-IDMS
RUN ( cd /var/www/html/Web-IDMS; php artisan key:generate )
RUN ( cd /var/www/html/Web-IDMS; php artisan optimize )
RUN ( cd /var/www/html/Web-IDMS; php artisan view:recreate )
RUN ( cd /var/www/html/Web-IDMS; php artisan optimize )

# 9. Starting Apache Server
# -------------------------------------------------------------------------------------
EXPOSE 80
ADD ./docker-utility/run-httpd.sh /run-httpd.sh
RUN chmod -v +x /run-httpd.sh
CMD [ "/run-httpd.sh" ]
CMD echo "ServerName localhost" >> /etc/apache2/apache2.conf
CMD ["/usr/sbin/apache2ctl", "-D", "FOREGROUND"]
RUN rm -rf /run/httpd/* /tmp/httpd*
RUN /usr/sbin/apachectl -DFOREGROUNDRUN 

