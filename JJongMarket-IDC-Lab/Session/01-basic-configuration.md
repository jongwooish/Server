# 01. Basic Configuration

### [ All Devices ]

```bash
apt install -y vim ssh curl nmap rsyslog
echo -e 'set nu\nset ic\nset si' >> /etc/vim/vimrc
echo 'permitrootlogin yes' >> /etc/ssh/sshd_config && systemctl restart ssh
read hostname && echo $hostname > /etc/hostname && hostname $hostname && exit
```

`read hostname` 에서 FW, DNS, WEB1, WEB2, DB, MGMT, CLIENT, REMOTE 중 하나를 입력합니다.

> `/etc/hosts` 의 `127.0.1.1` 줄도 새 호스트네임으로 바꿉니다. 그대로 두면 apache2 등을 재시작할 때 이름을 찾지 못한다는 경고가 뜹니다.
