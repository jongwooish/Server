#!/bin/bash
# =====================================================================
#  prepare-template-linux.sh  (Debian / Ubuntu)
#  Run inside the Template VM right before taking the "Template" snapshot.
#  Cleans per-machine data so every clone starts fresh, then powers off.
#
#  usage: sudo ./prepare-template-linux.sh
# =====================================================================
set -euo pipefail

if [ "$(id -u)" -ne 0 ]; then
    echo "Run as root (sudo)." >&2
    exit 1
fi

echo "[1/5] Cleaning package cache"
apt-get clean

echo "[2/5] Emptying machine-id (a new one is generated on each clone's first boot)"
truncate -s 0 /etc/machine-id
if [ -d /var/lib/dbus ]; then ln -sf /etc/machine-id /var/lib/dbus/machine-id; fi

echo "[3/5] Removing DHCP leases"
rm -f /var/lib/dhcp/*.leases /var/lib/NetworkManager/*.lease 2>/dev/null || true

echo "[4/5] Clearing logs and shell history"
journalctl --rotate >/dev/null 2>&1 || true
journalctl --vacuum-time=1s >/dev/null 2>&1 || true
find /var/log -type f \( -name '*.gz' -o -name '*.[0-9]' \) -delete
find /var/log -type f -exec truncate -s 0 {} \;
rm -f /root/.bash_history

echo "[5/5] Done. Powering off - take the 'Template' snapshot after shutdown."
sleep 2
poweroff
