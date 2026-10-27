# 17. Ansible

### [ Host PC ] → [ MGMT ]

`scripts/make-ansible-zip.sh` 로 만든 `jjong-idc-lab_Ansible.zip` 을 MGMT에 보냅니다 (NAT 어댑터 사용).

```powershell
scp .\jjong-idc-lab_Ansible.zip root@<MGMT_NAT_IP>:/root/
```

### [ MGMT ]

```bash
apt install -y unzip ansible
unzip -o /root/jjong-idc-lab_Ansible.zip -d /etc/
ssh-keyscan 10.10.30.254 10.10.10.10 10.10.10.21 10.10.10.22 10.10.20.10 >> /root/.ssh/known_hosts
ssh-keygen -t ed25519 -N "" -f /root/.ssh/id_ed25519
for h in 10.10.30.254 10.10.10.10 10.10.10.21 10.10.10.22 10.10.20.10; do ssh-copy-id root@$h; done
cd /etc/ansible
ansible all -m ping
ansible-playbook site.yml --check
ansible-playbook site.yml
```
