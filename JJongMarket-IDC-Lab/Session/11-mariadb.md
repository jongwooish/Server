# 11. MariaDB

### [ DB ]

```bash
apt install -y mariadb-server
systemctl enable --now mariadb
vim /etc/mysql/mariadb.conf.d/60-jjong.cnf
```

```ini
[mysqld]
bind-address         = 10.10.20.10
character-set-server = utf8mb4
collation-server     = utf8mb4_unicode_ci
```

```bash
systemctl restart mariadb
```

### [ Host PC ] → [ DB ]

DB에 NAT 어댑터를 잠깐 붙여 IP를 받은 뒤, 호스트 PC PowerShell에서 보냅니다.

```powershell
scp .\configs\mariadb\shop.sql root@<DB_NAT_IP>:/root/
```

NAT 어댑터는 다시 제거합니다.

### [ DB ]

```bash
mariadb < /root/shop.sql
```

> 콘솔에 한글을 붙여 넣으면 깨지므로 SQL은 반드시 파일로 옮깁니다.
