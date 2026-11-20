# Linux GUI Install

[한국어](README.ko.md) | [English](../README.md) | [日本語](README.ja.md) | **简体中文** | [Español](README.es.md) | [Français](README.fr.md)

![Debian](https://img.shields.io/badge/Debian-12-A81D33?logo=debian&logoColor=white) ![VMware Workstation Pro](https://img.shields.io/badge/VMware_Workstation_Pro-17-607078?logo=vmware&logoColor=white)

通过 Clone Linux CLI Template VM 来制作 **GUI VM** 的过程。

> CLI VM 请先阅读 [01. Debian VM 安装指南](../../01-Debian-VM-Install/README/README.zh-CN.md)。

## 目录

- [1. Full Clone Debian CLI VM](#1-full-clone-debian-cli-vm)
- [2. 启动 Linux](#2-启动-linux)
- [3. 安装 GUI](#3-安装-gui)
- [4. 确认](#4-确认)
- [总结](#总结)

---

## 1. Full Clone Debian CLI VM

在 Debian CLI VM 上选择 **Manage → Clone**。

![Clone menu](../images/01-clone-menu.png)

按 Enter → Enter 后,选择 **Create a full clone**。

![Create a full clone](../images/02-full-clone.png)

为了和 CLI VM 区分,在名称里加上 **GUI**,以后会方便很多。

![Virtual machine name](../images/03-vm-name.png)

创建完成后按 Enter(Close)。

![Clone done](../images/04-clone-done.png)

---

## 2. 启动 Linux

打开 VM 的电源。

![Power on](../images/05-power-on.png)

用 **root** 用户登录。

![root login](../images/06-root-login.png)

---

## 3. 安装 GUI

输入下面的命令并按 Enter。

```bash
tasksel
```

![tasksel](../images/07-tasksel.png)

用空格键勾选 **Debian desktop environment**,然后按 Enter。

![Debian desktop environment](../images/08-desktop-environment.png)

等待下载和安装完成。

![Installing packages](../images/09-installing.png)

用 `reboot` 重启后,会进入 GUI 画面。

```bash
reboot
```

![reboot](../images/10-reboot.png)

---

## 4. 确认

看到下面这样的画面,就可以把它当作 **Linux GUI 原始 VM** 使用了。

![GUI check](../images/11-gui-check.png)

---

## 总结

- GUI VM 通过 **Full Clone Debian CLI VM** 来制作,名称里加上 GUI 以便区分。
- 用 root 登录后,在 **`tasksel`** 中选择 **Debian desktop environment**。
- 安装完成后 **`reboot`**,出现 GUI 画面后就把它作为 GUI 原始 VM 使用。
