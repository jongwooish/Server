# 10. Chrony

### [ DNS ]

```bash
apt install -y chrony
vim /etc/chrony/conf.d/jjong.conf
```

```
local stratum 8
allow 10.10.0.0/16
allow 10.8.0.0/24
```

```bash
sed -i 's/^pool /#pool /' /etc/chrony/chrony.conf
systemctl restart chrony
```

### [ FW, WEB1, WEB2, DB, MGMT, CLIENT ]

```bash
apt install -y chrony
echo "server 10.10.10.10 iburst" >> /etc/chrony/conf.d/jjong.conf
sed -i 's/^pool /#pool /' /etc/chrony/chrony.conf
systemctl restart chrony
```
