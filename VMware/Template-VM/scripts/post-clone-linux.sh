#!/bin/bash
# =====================================================================
#  post-clone-linux.sh  (Debian / Ubuntu)
#  Run once inside each new clone to make it unique.
#    - hostname + /etc/hosts
#    - machine-id (DHCP uses it, duplicates can cause the same IP)
#    - SSH host keys
#
#  usage: sudo ./post-clone-linux.sh <new-hostname>
#  e.g.   sudo ./post-clone-linux.sh WEB1
# =====================================================================
set -euo pipefail

NEW_NAME="${1:-}"
if [ -z "$NEW_NAME" ]; then
    echo "usage: $0 <new-hostname>" >&2
    exit 1
fi
if [ "$(id -u)" -ne 0 ]; then
    echo "Run as root (sudo)." >&2
    exit 1
fi

echo "[1/4] Hostname -> $NEW_NAME"
hostnamectl set-hostname "$NEW_NAME"
if grep -q '^127\.0\.1\.1' /etc/hosts; then
    sed -i "s/^127\.0\.1\.1.*/127.0.1.1\t$NEW_NAME/" /etc/hosts
else
    printf '127.0.1.1\t%s\n' "$NEW_NAME" >> /etc/hosts
fi

echo "[2/4] New machine-id"
rm -f /etc/machine-id /var/lib/dbus/machine-id
systemd-machine-id-setup
if [ -d /var/lib/dbus ]; then ln -sf /etc/machine-id /var/lib/dbus/machine-id; fi

echo "[3/4] New SSH host keys"
rm -f /etc/ssh/ssh_host_*
ssh-keygen -A

echo "[4/4] Done."
echo "  - Set this VM's IP address now (Debian: /etc/network/interfaces, Ubuntu: /etc/netplan/)"
echo "  - Then reboot: systemctl reboot"
