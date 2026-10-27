#!/bin/bash
# Build ansible.zip for MGMT:/etc/ansible  (run inside Resources/)
set -e
T=$(mktemp -d); A=$T/ansible
cp -r ansible "$A"; rm -f "$A/files/README.txt"
cp -r web "$A/files/web"
cp configs/web/jjongmarket.conf configs/streaming/jjonglive.service "$A/files/"
(cd "$T" && zip -qr - ansible) > "${1:-ansible.zip}"
rm -rf "$T"; ls -l "${1:-ansible.zip}"
