# Linux GUI Install

[한국어](README/README.ko.md) | **English** | [日本語](README/README.ja.md) | [简体中文](README/README.zh-CN.md) | [Español](README/README.es.md) | [Français](README/README.fr.md)

![Debian](https://img.shields.io/badge/Debian-12-A81D33?logo=debian&logoColor=white) ![VMware Workstation Pro](https://img.shields.io/badge/VMware_Workstation_Pro-17-607078?logo=vmware&logoColor=white)

How to create a **GUI VM** by cloning the Linux CLI template VM.

> For the CLI VM, read [01. Debian VM Installation Guide](../01-Debian-VM-Install/) first.

## Contents

- [1. Full clone the Debian CLI VM](#1-full-clone-the-debian-cli-vm)
- [2. Boot Linux](#2-boot-linux)
- [3. Install the GUI](#3-install-the-gui)
- [4. Check](#4-check)
- [Summary](#summary)

---

## 1. Full clone the Debian CLI VM

On the Debian CLI VM, select **Manage → Clone**.

![Clone menu](images/01-clone-menu.png)

Press Enter → Enter, then select **Create a full clone**.

![Create a full clone](images/02-full-clone.png)

Putting **GUI** in the name to tell it apart from the CLI VM makes life easier later.

![Virtual machine name](images/03-vm-name.png)

When the clone is created, press Enter (Close).

![Clone done](images/04-clone-done.png)

---

## 2. Boot Linux

Power on the VM.

![Power on](images/05-power-on.png)

Log in as the **root** user.

![root login](images/06-root-login.png)

---

## 3. Install the GUI

Type the following command and press Enter.

```bash
tasksel
```

![tasksel](images/07-tasksel.png)

Check **Debian desktop environment** with the Space bar and press Enter.

![Debian desktop environment](images/08-desktop-environment.png)

Wait for the download and installation to finish.

![Installing packages](images/09-installing.png)

Reboot with `reboot`, and the system boots into the GUI.

```bash
reboot
```

![reboot](images/10-reboot.png)

---

## 4. Check

When you see a screen like this, you can use it as the **original Linux GUI VM**.

![GUI check](images/11-gui-check.png)

---

## Summary

- The GUI VM is created by **full cloning the Debian CLI VM**. Put GUI in the name to tell them apart.
- Log in as root and select **Debian desktop environment** in **`tasksel`**.
- When the installation finishes, **`reboot`**; once the GUI appears, use it as the original GUI VM.
