# 13. Keepalived

### [ WEB1 ]

```bash
apt install -y keepalived
cp /usr/share/doc/keepalived/samples/keepalived.conf.vrrp /etc/keepalived/keepalived.conf
vim /etc/keepalived/keepalived.conf
```

```
# /etc/keepalived/keepalived.conf  (WEB1)
vrrp_script chk_apache {
    script "/usr/bin/curl -fs -o /dev/null http://127.0.0.1/health.html"
    interval 2
    weight -20                 # 110 - 20 = 90 < 100  ->  WEB2 takes the VIP
}

vrrp_instance VI_1 {
    state MASTER
    interface ens33
    garp_master_delay 10
    virtual_router_id 51
    priority 110
    advert_int 1
    authentication {
        auth_type PASS
        auth_pass <VRRP_PASS>  # 8 characters max, same on WEB1 and WEB2
    }
    virtual_ipaddress {
        10.10.10.100/32
    }
    track_script {
        chk_apache
    }
}
```

```bash
systemctl restart keepalived
```

### [ WEB2 ]

```bash
apt install -y keepalived
scp 10.10.10.21:/etc/keepalived/keepalived.conf /etc/keepalived
vim /etc/keepalived/keepalived.conf
```

```
# /etc/keepalived/keepalived.conf  (WEB2)
vrrp_script chk_apache {
    script "/usr/bin/curl -fs -o /dev/null http://127.0.0.1/health.html"
    interval 2
    weight -20                 # 110 - 20 = 90 < 100  ->  WEB2 takes the VIP
}

vrrp_instance VI_1 {
    state BACKUP
    interface ens33
    garp_master_delay 10
    virtual_router_id 51
    priority 100
    advert_int 1
    authentication {
        auth_type PASS
        auth_pass <VRRP_PASS>  # 8 characters max, same on WEB1 and WEB2
    }
    virtual_ipaddress {
        10.10.10.100/32
    }
    track_script {
        chk_apache
    }
}
```

```bash
systemctl restart keepalived
```
