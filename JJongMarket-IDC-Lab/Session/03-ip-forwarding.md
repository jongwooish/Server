# 03. IP Forwarding

### [ FW ]

```bash
echo 'net.ipv4.ip_forward=1' >> /etc/sysctl.conf
sysctl -p
vim /etc/rc.local
```

```bash
#!/bin/bash
sysctl -p
```

```bash
chmod +x /etc/rc.local
```
