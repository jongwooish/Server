# Debian VM 安装指南

[한국어](README.ko.md) | [English](../README.md) | [日本語](README.ja.md) | **简体中文** | [Español](README.es.md) | [Français](README.fr.md)

![Debian](https://img.shields.io/badge/Debian-12-A81D33?logo=debian&logoColor=white) ![VMware Workstation Pro](https://img.shields.io/badge/VMware_Workstation_Pro-17-607078?logo=vmware&logoColor=white)

在 VMware Workstation Pro 中新建 VM,并用文本模式安装程序安装 Debian 的过程。这台 VM 之后会作为 Clone 的基础,即 **Debian CLI VM**。

## 目录

- [1. 创建 VM](#1-创建-vm)
- [2. 开始安装与地区设置](#2-开始安装与地区设置)
- [3. 网络与主机名](#3-网络与主机名)
- [4. 账户与密码](#4-账户与密码)
- [5. 磁盘分区](#5-磁盘分区)
- [6. 软件选择与 GRUB](#6-软件选择与-grub)
- [总结](#总结)

---

## 1. 创建 VM

启动 VMware,按 **Ctrl + N** 新建 VM。

![New Virtual Machine Wizard](../images/01-new-vm-wizard.png)

选择启动所需的 **ISO 文件**。

![Select the ISO](../images/02-select-iso.png)

![Browse for the ISO](../images/03-browse-iso.png)

点击 **Next**,到下一个画面为止都保持默认值。

![ISO selected](../images/04-iso-selected.png)

![Customize Hardware](../images/05-ready-customize.png)

在 **Customize Hardware** 中把 **Network Adapter** 设为 **Host-only**。

![Network Adapter: Host-only](../images/06-network-host-only.png)

这不是必须的,但如果愿意,可以删除 **USB Controller** 和 **Sound Card**。

![Remove USB and Sound Card](../images/07-remove-usb-sound.png)

点击 **Finish**,VM 会自动开机。

![Finish](../images/08-finish.png)

---

## 2. 开始安装与地区设置

按 **Ctrl + G** 把输入交给 VM,然后用**方向键、空格键和 Enter** 进行设置。

![Boot menu](../images/09-boot-menu.png)

> 快速设置方法:**English** 回车 → **other** 回车 → **aaa** 回车 → **kkk** 回车

![Boot menu](../images/10-boot-menu-language.png)

![Select other](../images/11-select-other.png)

找到自己国家所在的大洲并选择。在韩国就选 **Asia**。

![Select Asia](../images/12-select-asia.png)

稍微往下滚动,选择 **Korea, Republic of(South Korea)**,不要选 North Korea。

![Select Korea](../images/13-select-korea.png)

接下来的 locale 画面按图中所示选择。

![Locale](../images/14-locale.png)

![Locale default](../images/15-locale-default.png)

---

## 3. 网络与主机名

出现网络(DHCP)设置画面时,按 **取消(Cancel)**。

![Cancel DHCP](../images/16-dhcp-cancel.png)

![Network autoconfiguration failed](../images/17-network-failed.png)

选择最下面的选项,**现在不配置网络。**

![Do not configure the network now](../images/18-no-network.png)

主机名(管理员名)如果任务书上有指定就照填,没有的话随便填(例:`root`)。

![Hostname](../images/19-hostname.png)

---

## 4. 账户与密码

root 密码如果任务书上有指定就照填,没有的话填你想要的密码。勾选 **Show Password in Clear** 可以确认输入的密码是否正确。

![Root password](../images/20-root-password.png)

确认密码时也输入同样的密码。

![Root password verify](../images/21-root-password-verify.png)

用户名如果任务书上有指定就照填,没有的话随便填(例:`sysop`)。

![Full name](../images/22-full-name.png)

账户名也和上面的用户名填得一样。

![Username](../images/23-username.png)

用户密码和上面的 root 密码设成一样比较方便;任务书上有指定的话就用指定的。这里也勾选 **Show Password in Clear** 进行确认。

![User password](../images/24-user-password.png)

确认密码栏也输入同样的密码。

![User password verify](../images/25-user-password-verify.png)

> 截图中能看到的真实密码已用 `********` 遮住。

---

## 5. 磁盘分区

出现询问分区方式的画面时,选择最上面的 **Guided - use entire disk**。

![Guided - use entire disk](../images/26-guided-entire-disk.png)

![Select disk](../images/27-select-disk.png)

![Partitioning scheme](../images/28-partition-scheme.png)

出现这个画面时,选择最下面的 **Finish partitioning and write changes to disk**。

![Finish partitioning](../images/29-finish-partitioning.png)

询问是否写入分区更改并格式化时,**一定要选 Yes**。选 No 的话,可能要从头重新设置。

![Write changes: Yes](../images/30-write-changes-yes.png)

---

## 6. 软件选择与 GRUB

这个画面询问是否把常用软件包的信息匿名发送给开发者。选择 **No**。

![Popularity contest: No](../images/31-popularity-no.png)

**只保留最后一个复选框(standard system utilities)**,其余全部取消勾选。这样安装的是没有桌面的 CLI 系统。

![Software selection](../images/32-software-selection.png)

问题是“是否把 GRUB 引导程序安装到主驱动器?”。选择 **Yes**。

![Install GRUB: Yes](../images/33-grub-yes.png)

这个画面询问把 GRUB 引导程序安装到哪里。选择**下面的磁盘(/dev/sda)**。

![GRUB device](../images/34-grub-device.png)

安装完成,即将重启。点击 **Continue**,Linux 就安装好了。辛苦了!

![Installation complete](../images/35-install-complete.png)

---

## 总结

- 用 **Ctrl + N** 新建 VM,选择 ISO 后把 **Network Adapter 改为 Host-only**(删除 USB・Sound Card 可选)。
- 安装程序先按 **Ctrl + G**,再用方向键、空格键和 Enter 操作。地区选 **Asia → Korea**。
- **取消** DHCP 设置,**之后再配置网络**。
- 主机名、用户名和密码使用**任务书上的值**,用 Show Password in Clear 确认输入。
- 分区:**Guided - use entire disk → Finish → Yes**。
- 软件**只保留 standard system utilities**,GRUB 选 **Yes → /dev/sda** 安装。
