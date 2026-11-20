# Debian VM Installation Guide

[한국어](README/README.ko.md) | **English** | [日本語](README/README.ja.md) | [简体中文](README/README.zh-CN.md) | [Español](README/README.es.md) | [Français](README/README.fr.md)

![Debian](https://img.shields.io/badge/Debian-12-A81D33?logo=debian&logoColor=white) ![VMware Workstation Pro](https://img.shields.io/badge/VMware_Workstation_Pro-17-607078?logo=vmware&logoColor=white)

How to create a new VM in VMware Workstation Pro and install Debian with the text-mode installer. This VM is then used as the **Debian CLI VM** that later clones are based on.

## Contents

- [1. Create the VM](#1-create-the-vm)
- [2. Start the installer and choose the region](#2-start-the-installer-and-choose-the-region)
- [3. Network and hostname](#3-network-and-hostname)
- [4. Accounts and passwords](#4-accounts-and-passwords)
- [5. Disk partitioning](#5-disk-partitioning)
- [6. Software selection and GRUB](#6-software-selection-and-grub)
- [Summary](#summary)

---

## 1. Create the VM

Start VMware and press **Ctrl + N** to create a new VM.

![New Virtual Machine Wizard](images/01-new-vm-wizard.png)

Select the **ISO file** needed to boot.

![Select the ISO](images/02-select-iso.png)

![Browse for the ISO](images/03-browse-iso.png)

Click **Next** and keep the defaults until the next screen.

![ISO selected](images/04-iso-selected.png)

![Customize Hardware](images/05-ready-customize.png)

In **Customize Hardware**, set the **Network Adapter** to **Host-only**.

![Network Adapter: Host-only](images/06-network-host-only.png)

It's optional, but you can remove the **USB Controller** and **Sound Card** if you like.

![Remove USB and Sound Card](images/07-remove-usb-sound.png)

Click **Finish** and the VM powers on automatically.

![Finish](images/08-finish.png)

---

## 2. Start the installer and choose the region

Press **Ctrl + G** to send input to the VM, then use the **arrow keys, Space and Enter** to make your choices.

![Boot menu](images/09-boot-menu.png)

> Quick way: **English** Enter → **other** Enter → **aaa** Enter → **kkk** Enter

![Boot menu](images/10-boot-menu-language.png)

![Select other](images/11-select-other.png)

Find the continent of your country. For Korea, select **Asia**.

![Select Asia](images/12-select-asia.png)

Scroll down a little and select **Korea, Republic of (South Korea)**, not North Korea.

![Select Korea](images/13-select-korea.png)

On the following locale screens, choose as shown.

![Locale](images/14-locale.png)

![Locale default](images/15-locale-default.png)

---

## 3. Network and hostname

When the network (DHCP) configuration screen appears, press **Cancel**.

![Cancel DHCP](images/16-dhcp-cancel.png)

![Network autoconfiguration failed](images/17-network-failed.png)

Select the last option so that **the network is not configured now**.

![Do not configure the network now](images/18-no-network.png)

For the hostname (admin name), use the value from the task sheet if there is one; otherwise, type anything (e.g. `root`).

![Hostname](images/19-hostname.png)

---

## 4. Accounts and passwords

For the root password, use the one from the task sheet if there is one; otherwise, choose your own. Check **Show Password in Clear** to make sure you typed it correctly.

![Root password](images/20-root-password.png)

Type the same password again to verify it.

![Root password verify](images/21-root-password-verify.png)

For the user's name, use the one from the task sheet if there is one; otherwise, type anything (e.g. `sysop`).

![Full name](images/22-full-name.png)

Type the account username the same as the name above.

![Username](images/23-username.png)

It's easiest to use the same password as root for the user, unless the task sheet gives one. Again, check **Show Password in Clear** to verify it.

![User password](images/24-user-password.png)

Type the same password in the confirmation field.

![User password verify](images/25-user-password-verify.png)

> The real password visible in the captures has been masked as `********`.

---

## 5. Disk partitioning

When asked how to partition the disk, select the first option, **Guided - use entire disk**.

![Guided - use entire disk](images/26-guided-entire-disk.png)

![Select disk](images/27-select-disk.png)

![Partitioning scheme](images/28-partition-scheme.png)

On this screen, select the last option, **Finish partitioning and write changes to disk**.

![Finish partitioning](images/29-finish-partitioning.png)

When asked whether to write the partition changes and format, **always select Yes**. If you select No, you may have to start the setup over.

![Write changes: Yes](images/30-write-changes-yes.png)

---

## 6. Software selection and GRUB

This screen asks whether to anonymously send information about frequently used packages to the developers. Select **No**.

![Popularity contest: No](images/31-popularity-no.png)

**Uncheck everything except the last checkbox (standard system utilities).** This installs a CLI system without a desktop.

![Software selection](images/32-software-selection.png)

The question is "Install the GRUB boot loader to your primary drive?". Select **Yes**.

![Install GRUB: Yes](images/33-grub-yes.png)

This screen asks where to install the GRUB boot loader. Select **the disk below (/dev/sda)**.

![GRUB device](images/34-grub-device.png)

The installation is finished and the system will reboot. Click **Continue** and the Linux installation is done. Good job!

![Installation complete](images/35-install-complete.png)

---

## Summary

- Create the VM with **Ctrl + N**, select the ISO, and change the **Network Adapter to Host-only** (removing USB and Sound Card is optional).
- Control the installer with **Ctrl + G**, then arrow keys, Space and Enter. The region is **Asia → Korea**.
- **Cancel** the DHCP configuration and **configure the network later**.
- Use the **values from the task sheet** for the hostname, username and passwords. Check them with Show Password in Clear.
- Partitioning: **Guided - use entire disk → Finish → Yes**.
- Keep **only standard system utilities**, and install GRUB with **Yes → /dev/sda**.
