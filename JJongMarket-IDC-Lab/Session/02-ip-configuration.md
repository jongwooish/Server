# 02. IP Configuration

### [ CLI Devices ]

```bash
vim /etc/network/interfaces
```

### [ FW ]

```
auto ens33
iface ens33 inet static
        address 203.0.113.10/24

auto ens37
iface ens37 inet static
        address 10.10.10.254/24

auto ens38
iface ens38 inet static
        address 10.10.20.254/24

auto ens39
iface ens39 inet static
        address 10.10.30.254/24
```

### [ DNS ]

```
auto ens33
iface ens33 inet static
        address 10.10.10.10/24
        gateway 10.10.10.254
```

### [ WEB1 ]

```
auto ens33
iface ens33 inet static
        address 10.10.10.21/24
        gateway 10.10.10.254
```

### [ WEB2 ]

```
auto ens33
iface ens33 inet static
        address 10.10.10.22/24
        gateway 10.10.10.254
```

### [ DB ]

```
auto ens33
iface ens33 inet static
        address 10.10.20.10/24
        gateway 10.10.20.254
```

### [ MGMT ]

```
auto ens33
iface ens33 inet static
        address 10.10.30.10/24
        gateway 10.10.30.254
```

### [ CLI Devices ]

```bash
systemctl restart networking
systemctl status networking
```

### [ GUI Devices ]

CLIENT, REMOTE는 DHCP로 받습니다. ([04. DHCP](04-dhcp.md))
