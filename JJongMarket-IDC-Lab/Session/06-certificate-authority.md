# 06. Certificate Authority

### [ MGMT ]

```bash
vim /etc/ssl/openssl.cnf
```

```
dir             = /etc/ssl/RootCA
...
x509_extensions = v3_req
...
policy          = policy_anything

[ req_distinguished_name ]
countryName_default          = KR
stateOrProvinceName_default  =
0.organizationName_default   =
```

```bash
vim /usr/lib/ssl/misc/CA.pl
```

```perl
my $CATOP = "/etc/ssl/RootCA";
```

```bash
/usr/lib/ssl/misc/CA.pl -newca
```

- 처음 Enter, CA 키 비밀번호 입력, 나머지는 기본값
- `Common Name []: RootCA`

```bash
cp /etc/ssl/RootCA/cacert.pem /etc/ssl/RootCA.crt
```

### [ All Devices except MGMT ]

```bash
scp 10.10.30.10:/etc/ssl/RootCA.crt /usr/local/share/ca-certificates
```

> FW의 nftables 때문에 scp가 막히면 FW에서 `systemctl stop nftables` 후 작업하고 `systemctl start nftables` 로 되돌립니다.

### [ All Devices ]

```bash
update-ca-certificates
```

Firefox는 시스템 목록이 아닌 자기 저장소를 쓰므로 CLIENT, REMOTE의 Firefox에는 RootCA를 따로 가져옵니다.
