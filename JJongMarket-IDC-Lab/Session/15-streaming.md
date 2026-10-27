# 15. Streaming

### [ WEB1 ]

```bash
apt install -y ffmpeg
```

NAT 어댑터를 붙이고 호스트 PC에서 영상, 웹 파일, hls.js를 보냅니다.
`hls.min.js` 는 저장소에 넣지 않았습니다. [jsDelivr](https://cdn.jsdelivr.net/npm/hls.js@1/dist/hls.min.js) 에서 받습니다.

```powershell
scp .\live\jjonglive.mp4 root@<WEB1_NAT_IP>:/root/
scp -r .\web root@<WEB1_NAT_IP>:/root/
scp .\hls.min.js root@<WEB1_NAT_IP>:/root/
```

```bash
mkdir -p /opt/jjonglive
mv /root/jjonglive.mp4 /opt/jjonglive/
vim /etc/systemd/system/jjonglive.service
```

```
# =====================================================================
#  /etc/systemd/system/jjonglive.service (WEB1, WEB2)
#  1분짜리 영상을 무한 반복하면서 2초짜리 HLS 조각으로 잘라 /var/www/hls 에 저장
#  → Apache 가 https://www.jjongmarket.kr/hls/ 로 내보낸다
# =====================================================================
[Unit]
Description=JJONG LIVE - loop broadcast (HLS)
After=network.target

[Service]
ExecStartPre=/bin/mkdir -p /var/www/hls
ExecStart=/usr/bin/ffmpeg -nostdin -loglevel warning -re -stream_loop -1 -i /opt/jjonglive/jjonglive.mp4 -c copy -f hls -hls_time 2 -hls_list_size 5 -hls_flags delete_segments+omit_endlist /var/www/hls/stream.m3u8
Restart=always
RestartSec=3

[Install]
WantedBy=multi-user.target
```

```bash
systemctl daemon-reload
systemctl enable --now jjonglive

cp -r /root/web/* /var/www/jjongmarket/
mkdir -p /var/www/jjongmarket/js
cp /root/hls.min.js /var/www/jjongmarket/js/
chmod -R a+rX /var/www/jjongmarket
a2enmod headers
vim /etc/apache2/sites-available/jjongmarket.conf
```

`<VirtualHost *:443>` 안에 추가:

```apache
    Alias /hls/ /var/www/hls/
    <Directory /var/www/hls>
        Require all granted
        AddType application/vnd.apple.mpegurl .m3u8
        AddType video/mp2t .ts
        Header set Cache-Control "no-cache"
    </Directory>
```

```bash
apache2ctl configtest
systemctl restart apache2
```

### [ WEB2 ]

```bash
apt install -y ffmpeg
mkdir -p /opt/jjonglive
scp 10.10.10.21:/opt/jjonglive/jjonglive.mp4 /opt/jjonglive/
scp 10.10.10.21:/etc/systemd/system/jjonglive.service /etc/systemd/system/
scp -r 10.10.10.21:/var/www/jjongmarket/* /var/www/jjongmarket/
scp 10.10.10.21:/etc/apache2/sites-available/jjongmarket.conf /etc/apache2/sites-available/
chmod -R a+rX /var/www/jjongmarket
hostname > /var/www/jjongmarket/health.html
systemctl daemon-reload
systemctl enable --now jjonglive
a2enmod headers
systemctl restart apache2
```
