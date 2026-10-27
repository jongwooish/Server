# 04. DHCP

### [ MGMT ]

```bash
apt install -y isc-dhcp-server
vim /etc/default/isc-dhcp-server
```

```
INTERFACESv4="ens33"
```

```bash
vim /etc/dhcp/dhcpd.conf
```

```
subnet 10.10.30.0 netmask 255.255.255.0 {
        range 10.10.30.100 10.10.30.150;
        option domain-name-servers 10.10.10.10;
        option routers 10.10.30.254;
}
subnet 203.0.113.0 netmask 255.255.255.0 {
        range 203.0.113.100 203.0.113.150;
        option domain-name-servers 10.10.10.10;
}
```

```bash
systemctl restart isc-dhcp-server
systemctl status isc-dhcp-server
```

### [ CLIENT ]

```bash
systemctl restart NetworkManager
ip a
```

### [ FW ] DHCP Relay

```bash
apt install -y dnsmasq
vim /etc/dnsmasq.conf
```

```
port=0
dhcp-relay=203.0.113.10,10.10.30.10
```

```bash
systemctl restart dnsmasq
```

### [ REMOTE ]

```bash
systemctl restart NetworkManager
ip a
```
