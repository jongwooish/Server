# 09. SSH

### [ FW ]

```bash
systemctl stop nftables
```

### [ MGMT ]

```bash
mkdir /etc/ssl/ssh-CA
ssh-keygen -f /etc/ssl/ssh-CA/ca -t ecdsa
ssh-keygen -t ecdsa
ssh-keygen -s /etc/ssl/ssh-CA/ca -I "jjong root key" -n root -V +52w /root/.ssh/id_ecdsa.pub
vim /etc/ssh/sshd_config
```

```
TrustedUserCAKeys /etc/ssl/ssh-CA/ca.pub
```

```bash
systemctl restart sshd
```

### [ All Devices except MGMT ]

```bash
scp -r 10.10.30.10:/etc/ssl /etc
```

### [ FW ]

```bash
systemctl start nftables
systemctl restart nftables
```

> `/etc/ssl` 을 통째로 복사하면 CA **개인키**(`ssh-CA/ca`)도 함께 넘어갑니다. 정리 방법은 [Verification - 보안 점검](../docs/verification.md#보안-점검) 에 있습니다.
