# 14. WEB

### [ MGMT ] 웹 인증서 발급

```bash
cd /etc/ssl
vim openssl.cnf
```

```
[ v3_req ]
subjectAltName = DNS:www.jjongmarket.kr, DNS:jjongmarket.kr
```

```bash
openssl req -out www.req -newkey rsa:2048 -nodes -keyout www.key     # CN: www.jjongmarket.kr
openssl ca -in www.req -out www.crt
scp /etc/ssl/www.* /etc/ssl/RootCA.crt 10.10.10.21:/etc/ssl
scp /etc/ssl/www.* /etc/ssl/RootCA.crt 10.10.10.22:/etc/ssl
```

### [ WEB1, WEB2 ]

`/etc/hosts` 의 `127.0.1.1` 을 각자 WEB1, WEB2로 고친 뒤 설치합니다.

```bash
apt install -y apache2 libapache2-mod-php php-mysql php-mbstring
a2enmod ssl rewrite
```

### [ WEB1 ]

```bash
mkdir -p /var/www/jjongmarket/css /etc/jjongmarket
vim /etc/jjongmarket/db.ini
```

```
; /etc/jjongmarket/db.ini  (WEB1, WEB2) - outside the web root
; chown root:www-data, chmod 640
host     = 10.10.20.10
dbname   = shop
user     = shop
password = "ChangeMe-Shop1"
```

WEB1에 NAT 어댑터를 잠깐 붙이고 호스트 PC에서 쇼핑몰 파일을 보냅니다.

```powershell
scp -r .\web\* root@<WEB1_NAT_IP>:/var/www/jjongmarket/
```

```bash
rm -f /var/www/jjongmarket/index.html
cp /etc/ssl/RootCA.crt /var/www/jjongmarket/ca.crt
vim /etc/apache2/sites-available/jjongmarket.conf
```

```
# =====================================================================
#  /etc/apache2/sites-available/jjongmarket.conf  (WEB1, WEB2 동일)
#  필요한 모듈: ssl, rewrite, headers  (php는 libapache2-mod-php 설치 시 자동 활성화)
# =====================================================================

# ---- HTTP(80): HTTPS로 넘기되, 아래 두 경로는 HTTP로도 응답 ----
#   /health.html   : keepalived 상태 검사용
#   /ca.crt        : 고객 PC가 사설 Root CA를 내려받는 주소
<VirtualHost *:80>
    ServerName  www.jjongmarket.kr
    ServerAlias jjongmarket.kr
    DocumentRoot /var/www/jjongmarket

    RewriteEngine On
    RewriteCond %{REQUEST_URI} !^/health\.html$
    RewriteCond %{REQUEST_URI} !^/ca\.crt$
    RewriteRule ^ https://www.jjongmarket.kr%{REQUEST_URI} [R=301,L]

    ErrorLog  ${APACHE_LOG_DIR}/jjongmarket_error.log
    CustomLog ${APACHE_LOG_DIR}/jjongmarket_access.log combined
</VirtualHost>

# ---- HTTPS(443): 쇼핑몰 본 서비스 ----
<VirtualHost *:443>
    ServerName  www.jjongmarket.kr
    ServerAlias jjongmarket.kr
    DocumentRoot /var/www/jjongmarket

    SSLEngine on
    SSLCertificateFile    /etc/ssl/www.crt
    SSLCertificateKeyFile /etc/ssl/www.key

    DirectoryIndex index.php

    <Directory /var/www/jjongmarket>
        Options -Indexes
        AllowOverride None
        Require all granted
    </Directory>
    # 쫑라이브 — 이 서버의 ffmpeg(jjonglive.service)가 만든 HLS 를 같은 HTTPS 주소(/hls/)로 (a2enmod headers)
    Alias /hls/ /var/www/hls/
    <Directory /var/www/hls>
        Require all granted
        AddType application/vnd.apple.mpegurl .m3u8
        AddType video/mp2t .ts
        Header set Cache-Control "no-cache"
    </Directory>

    # 공통 PHP(DB 접속 코드)는 브라우저로 직접 열 수 없게
    <Directory /var/www/jjongmarket/inc>
        Require all denied
    </Directory>

    Header always set X-Content-Type-Options "nosniff"
    Header always set X-Frame-Options "SAMEORIGIN"

    ErrorLog  ${APACHE_LOG_DIR}/jjongmarket_error.log
    CustomLog ${APACHE_LOG_DIR}/jjongmarket_access.log combined
</VirtualHost>
```

```bash
chmod 755 /etc/jjongmarket
chown root:www-data /etc/jjongmarket/db.ini
chmod 640 /etc/jjongmarket/db.ini
chmod -R a+rX /var/www/jjongmarket
```

### [ WEB2 ]

```bash
scp -r 10.10.10.21:/var/www/jjongmarket/* /var/www/jjongmarket/
scp 10.10.10.21:/etc/apache2/sites-available/jjongmarket.conf /etc/apache2/sites-available/
rm -f /var/www/jjongmarket/index.html
mkdir -p /etc/jjongmarket
scp 10.10.10.21:/etc/jjongmarket/db.ini /etc/jjongmarket/
chmod 755 /etc/jjongmarket
chown root:www-data /etc/jjongmarket/db.ini
chmod 640 /etc/jjongmarket/db.ini
chmod -R a+rX /var/www/jjongmarket
```

### [ WEB1, WEB2 ]

```bash
a2dissite 000-default
a2ensite jjongmarket
hostname > /var/www/jjongmarket/health.html
apache2ctl configtest
systemctl restart apache2
```

### [ CLIENT, REMOTE ]

```bash
apt install -y fonts-noto-cjk ibus-hangul
```

Firefox에서 RootCA를 신뢰하도록 가져온 뒤 `https://www.jjongmarket.kr` 접속. 데모 계정: `demo@jjongmarket.kr` / `Jjong1234!`

> Windows에서 scp로 보낸 폴더는 권한이 700이 될 수 있습니다. `chmod -R a+rX` 를 빼먹으면 500 에러가 납니다.
